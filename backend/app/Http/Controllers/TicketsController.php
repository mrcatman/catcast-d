<?php

namespace App\Http\Controllers;
use App\Helpers\CommonResponses;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketsController extends Controller{

    protected $admin_id = 58187655;
    protected $categories = [
        [
            'id' => 4,
            'text' => 'tickets.topics.siteerrors'
        ],
        [
            'id' => 5,
            'text' => 'tickets.topics.complaints'
        ],
        [
            'id' => 6,
            'text' => 'tickets.topics.suggestions'
        ],
        [
            'id' => 7,
            'text' => 'tickets.topics.other'
        ],
    ];
    protected $complaint_categories = [
        [
            'id' => 0,
            'text' => 'tickets.complaints.topics.copypermissions'
        ],
        [
            'id' => 1,
            'text' => 'tickets.complaints.topics.illegal'
        ],
        [
            'id' => 2,
            'text' => 'tickets.complaints.topics.offensive'
        ],
        [
            'id' => 3,
            'text' => 'tickets.complaints.topics.other'
        ],
    ];

    public function getCategories() {
        return $this->categories;
    }

    public function getComplaintCategories() {
        return $this->complaint_categories;
    }

    public function store() {
        $ticket = new Ticket();
        $user = null;
        if ($user = auth()->user()) {
            $ticket->user_id = $user->id;
        } else {
            $ticket->ip = request()->ip();
             if (request()->filled('contacts')) {
                $ticket->contacts = request()->input('contacts');
            } else {
                 return CommonResponses::validationError([
                     'contacts' => ['tickets.errors.enter_your_contacts']
                 ]);
            }
        }
        $ticket->category = (int)request()->input('category', -1);
        $text = request()->input('text', '...');
        if (request()->has('page')) {
            $ticket->connected_page = (object)[
                'type' => request()->input('page.type', ''),
                'id' => request()->input('page.id', -1)
            ];
        } else {
            if (!request()->filled('text')) {
                return CommonResponses::validationError([
                    'text' => ['tickets.errors.enter_text']
                ]);
            }
        }
        if (!request()->filled('text')) {
            return CommonResponses::validationError([
                'text' => ['tickets.errors.enter_text']
            ]);
        }
        $is_admin = $user && $user->is_admin && request()->input('is_admin');
        if (request()->has('user_id') && $is_admin) {
            $ticket->user_id = request()->input('user_id');
        }
        if (request()->has('title')) {
            $ticket->title = request()->input('title');
        }
        if (request()->has('is_important') && $is_admin) {
            $ticket->is_important = request()->input('is_important');
        }
        $ticket->save();
        $message = new TicketMessage();
        if ($user) {
            if (request()->has('user_id') && $is_admin) {
                $message->user_id = request()->input('user_id');
            } else {
                $message->user_id = $user->id;
            }
        }

        $message->ticket_id = $ticket->id;
        $message->is_answer = $is_admin;
        $message->text = $text;
        $message->save();

        return [
            'message' => request()->input('is_complaint') ? 'tickets.messages.complaint_saved' : 'tickets.messages.saved',
            'ticket' => $ticket,
            'data' => $message
        ];
    }

    public function index() {
        $user = auth()->user();
        $tickets = Ticket::orderBy('updated_at', 'DESC');
        $is_admin = request()->input('admin') && $user->is_admin;
        if (!$is_admin) {
            $tickets = $tickets->where(['user_id' => $user->id]);
        }
        $tickets = $tickets->paginate(request()->input('count', 10));
        if ($is_admin) {
            $tickets->getCollection()->transform(function ($ticket)  {
                $ticket->unread_user_messages =  TicketMessage::where(['ticket_id' => $ticket->id, 'is_answer' => false, 'is_read' => false])->count();
                $ticket->load('user');
                if ($ticket->status === "closed") {
                    $ticket->admin_status = "closed";
                }
                if ($ticket->status === "waiting_for_answer") {
                    $ticket->admin_status = "waiting_for_response";
                } else {
                    $ticket->admin_status = "waiting_for_answer";
                }
                $ticket = $ticket->toArray();
                if (!$ticket['user']) {
                    $ticket['user'] = [
                        'username' => ''
                    ];
                }
                return $ticket;
            });
        }
        return $tickets;
    }

    public function show($id) {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);
        $is_admin = request()->input('admin') && $user->is_admin;
        if ($ticket->user_id === $user->id || $is_admin) {
            $messages = TicketMessage::where(['ticket_id' => $ticket->id])->orderBy('id', 'DESC')->paginate(10); //:where(['user_id' => $user->id])->
            TicketMessage::where(['ticket_id' => $ticket->id, 'is_answer' => $is_admin ? false : true])->update(['is_read' => true]);
            return $messages;
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function update($id) {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);
        $is_admin = request()->input('is_answer') && $user->is_admin;
        if ($ticket->user_id === $user->id || $is_admin) {
            if (!$ticket->is_closed) {
                if (request()->filled('text')) {
                    $message = new TicketMessage();
                    $message->ticket_id = $ticket->id;
                    $message->text = request()->input('text');
                    $message->is_read = false;
                    $message->user_id = $user->id;
                    $message->is_answer = $is_admin;
                    $message->save();
                    $ticket->touch();
                    return $message;
                } else {
                    return CommonResponses::validationError(['text' => ['tickets.errors.enter_text']]);
                }
            } else {
                   return response()->json(['message' => 'tickets.errors.ticket_is_closed'], 422);
            }
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function getUnread() {
        $user = auth()->user();
        $has_important_messages = false;
        $tickets = Ticket::where(['user_id' => $user->id])->get();
        $tickets_by_id = [];
        foreach ($tickets as $ticket) {
            $tickets_by_id[$ticket->id] = $ticket;
        }
        $ticket_ids = $tickets->pluck('id');
        $unread = TicketMessage::whereIn('ticket_id', $ticket_ids)->where(['is_answer'=> true, 'is_read' => false])->get();
        foreach ($unread as $message) {
            if ($message->is_answer && $tickets_by_id[$ticket->id]->is_important) {
                $has_important_messages = true;
            }
        }
        return [
            'unread_count' => count($unread),
            'has_important_messages' => $has_important_messages,
        ];
    }

}
