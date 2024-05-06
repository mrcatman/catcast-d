<?php

namespace App\Http\Controllers;


use App\Helpers\LocalizationHelper;
use App\Models\User;
use App\Models\AccountRestore;
use Illuminate\Http\Request;
use App\ExternalServiceConnections\ExternalServiceConnection;
use App\ExternalServiceConnections\EmailConnection;
use App\ExternalServiceConnections\TelegramExternalServiceConnection;
use App\ExternalServiceConnections\VKExternalServiceConnection;
use Illuminate\Support\Facades\Validator;

class ExternalConnectionsController extends Controller {

    protected $types = [
        TelegramExternalServiceConnection::class,
        VKExternalServiceConnection::class,
        EmailConnection::class
    ];

    public function getConnections() {
        if ($user = auth()->user()) {
            $connections = [];
            foreach ($this->types as $type) {
                $instance = new $type;
                $connection = $instance->findByUserId($user->id);
                if ($connection) {
                    $connections[] = $connection;
                }
            }
            return ['list'=>$connections];
        } else{
            return CommonResponses::unauthorized();
        }
    }
    public function confirm($code) {
        if ($user = auth()->user()) {
            $connection = (new ExternalServiceConnection())->setFromCode($code);
            if ($connection->hasInstance()) {
                if ($connection->isWaitingForConfirmation()) {
                    $connection->confirm($user);
                    return ['message' =>'connections.success_confirm'];
                } else {
                    return response()->json(['message' => 'connections.errors.code_expired'], 422);
                }
            } else {
                return response()->json(['message' => 'connections.errors.no_such_code'], 422);
            }
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function sendConfirmation() {
        if ($user = auth()->user()) {
            if (!request()->has('type')) {
                return [
                    'status'=>0,
                    'text'=>'connections.errors.enter_type',
                    'errors'=>[
                        'type'=>[
                            'connections.errors.enter_type',
                        ]
                    ]
                ];
            }
            if (!request()->has('account_id')) {
                return [
                    'status'=>0,
                    'text'=>'connections.errors.enter_account_id',
                    'errors'=>[
                        'account_id'=>[
                            'connections.errors.enter_account_id',
                        ]
                    ]
                ];
            }
            foreach ($this->types as $type) {
                $instance = new $type;
                if ($request->input('type') == $instance->getName()) {
                    if ($instance->shouldSendConfirmation()) {
                        $already_has_instance = $instance->findByExternalHandle($request->input('account_id'),true);
                        if ($already_has_instance) {
                            return [
                                'status'=>0,
                                'text'=>'connections.errors.connected_user_already_exists',
                                'errors'=>[
                                    'account_id'=>[
                                        'connections.errors.connected_user_already_exists',
                                    ]
                                ]
                            ];
                        }
                        $instance->createByExternalHandle($request->input('account_id'))->deleteUnconfirmed()->sendConfirmation();
                        return [
                            'status'=>1,
                            'text'=>'connections.confirmation_sent',
                        ];
                    }
                }
            }
            return [
                'status'=>0,
                'text'=>'connections.errors.wrong_params',
            ];
        } else {
            return CommonResponses::unauthorized();
        }
    }



}
