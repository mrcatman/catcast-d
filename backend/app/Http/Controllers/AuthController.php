<?php

namespace App\Http\Controllers;


use App\Helpers\CommonResponses;
use App\Helpers\ConfigHelper;
use App\Helpers\CryptoHelper;
use App\Models\AccountConnection;
use App\Models\UserRegistrationRequest;
use App\Models\AccountRestore;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JWTAuth;

class AuthController extends Controller {


    const PasswordRules = 'min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';

    public function login(){
        $username = request()->input('username');
        $user = User::where(['username'=> $username])->first();
        $password = request()->input('password');
        if ($user && ($user->password !== null && Hash::check($password, $user->password))) {
            if (!$user->is_admin) {
                if (!$user->email_confirmed) {
                    return response()->json(['message' => 'auth.errors.login.not_confirmed_email'], 401);
                }
                if (ConfigHelper::registrationManual() && !$user->registration_request_approved) {
                    return response()->json(['message' => 'auth.errors.login.not_approved'], 401);
                }
            }
            return $this->loginAsUser($user);
        } else {
            return response()->json(['message' => 'auth.errors.login.wrong_credentials'], 401);
        }
    }

    public function register() {
        $validation_rules = [
            'email' => 'required',
            'username' => 'required|min:3|max:30|alphanumeric',
            'password' => 'required|confirmed|'. self::PasswordRules,
        ];
        if (ConfigHelper::registrationManual()) {
            $validation_rules['request_comment'] = 'required';
        }
        $data = request()->validate($validation_rules);

        $username_taken = User::where(['username' => $data['username'], 'domain' => null])->count() > 0;
        if ($username_taken) {
            return CommonResponses::validationError(['username' => ['auth.errors.register.username_taken']]);
        }
        $email_taken = AccountConnection::where([
            'account_type' => 'email',
            'account_name' => $data['email']
        ])->first();
        if ($email_taken) {
            return CommonResponses::validationError(['email' => ['auth.errors.register.email_taken']]);
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->last_seen = Carbon::now();
        CryptoHelper::generateKeys($user);
        $user->save();

        $email_confirm_code = Str::random();
        $user->accountConnections()->create([
            'user_id' => $user->id,
            'account_type' => 'email',
            'account_name' => $data['email'],
            'confirm_code' => $email_confirm_code
        ]);

        $this->sendConfirmationEMail($data['email'], $email_confirm_code);

        if (ConfigHelper::registrationManual()) {
            $request = new UserRegistrationRequest([
                'comment' => $data['request_comment']
            ]);
            $user->registrationRequest()->save($request);
        }
        return $user;
    }

    public function getMyData() {
        $user = auth()->user();
        if ($user) {
            $user->load('info');
            $user->append('additional_settings');
            $user->append('links');
            $user->append('unread_notifications_count');
        }
        return ['user' => $user];
    }


    public function refresh() {
        return $this->respondWithToken(auth()->refresh());
    }


    public function updateMyData() {
        $user = auth()->user();
        $validation_rules = [
            'status_text' => 'sometimes:max:256',
            'about' => 'sometimes|max:1024',
            'full_name' => 'sometimes|max:512',
            'pictures_data' => 'sometimes',
            'links' => 'sometimes|array|nullable',
            'links.*.title'=>'required',
            'links.*.url'=>'required',
        ];

        $data = request()->validate($validation_rules);
        foreach($data as $key => $value) {
            $user->{$key} = $value;
        }

        if (request()->filled('additional_settings')) {
            $user->additional_settings = request()->input('additional_settings');
        }
        $user->save();
        $user->append('additional_settings');
        return $user;
    }


    public function updatePassword(){
        $user = auth()->user();
        $old_password = request()->input('old_password');
        if (Hash::check($old_password, $user->password)) {
            $data = request()->validate([
                'new_password' => 'required|confirmed|'.self::PasswordRules
            ]);
            $user->password = Hash::make($data['new_password']);
            $user->save();
            return $user;
        } else {
            return CommonResponses::validationError(['old_password' => ['settings.password.errors.old_password_wrong']]);
        }
    }

    public function resendConfirmationEmail() {
        $email_connection = AccountConnection::where([
            'account_type' => 'email',
            'account_name' => request()->input('email'),
            'confirmed' => false
        ])->firstOrFail();
        if (!$email_connection->confirmation_can_be_resent) {
            return CommonResponses::validationError(['email' => ['auth.errors.recently_requested']]);
        }
        $this->sendConfirmationEmail($email_connection->account_name, $email_connection->confirm_code);
        $email_connection->updated_at = Carbon::now();
        $email_connection->save();
        return response()->json(['message' => 'auth.resend_confirmation.success']);
    }

    public function forgotPassword() {
        $email_connection = AccountConnection::where([
            'account_type' => 'email',
            'account_name' => request()->input('email'),
            'confirmed' => true
        ])->firstOrFail();
        if (!$email_connection->user->registration_request_approved) {
            return response()->json(['message' => 'auth.errors.login.not_approved'], 401);
        }

        $already_has_restore = AccountRestore::where(['connection_id' => $email_connection->id])->first();
        if ($already_has_restore && time() - $already_has_restore->created_at->timestamp < 300) {
            return CommonResponses::validationError(['email' => ['auth.errors.recently_requested']]);
        }
        $restore = new AccountRestore([
            'connection_id' => $email_connection->id,
            'confirm_code' => Str::random()
        ]);
        $restore->save();
        $this->sendRestoreEmail($email_connection->account_name, $restore->confirm_code);
        return response()->json(['message' => 'auth.forgot_password.success']);
    }


    public function restoreAccount() {
        $restore = AccountRestore::where(['confirm_code' => request()->input('code')])->first();
        if (!$restore) {
            return response()->json(['message' => 'auth._errors.wrong_code'], 422);
        }
        $validation_rules = [
            'password' => 'required|confirmed|'. self::PasswordRules,
        ];
        $data = request()->validate($validation_rules);

        $user = $restore->accountConnection->user;
        $restore->delete();

        $user->password = Hash::make($data['password']);
        $user->save();

        $this->loginAsUser($user);
        return $user;
    }

    public function confirmEmail($code) {
        $email_connection = AccountConnection::where([
            'account_type' => 'email',
            'confirm_code' => $code,
            'confirmed' => false
        ])->first();
        if (!$email_connection) {
            return response()->json(['message' => 'auth._errors.wrong_code'], 422);
        }
        $user = $email_connection->user;
        if (!ConfigHelper::registrationManual() ||$user->registration_request_approved) {
            $this->loginAsUser($user);
        }
        return $user;
    }


    private function sendConfirmationEmail($email, $email_confirm_code) {
        $email_confirm_link = ConfigHelper::siteURL()."/auth/confirm?code=$email_confirm_code";
        //Mail::to($email)->send(new ConfirmAccount($email_confirm_link)); // todo: emails
    }

    private function sendRestoreEmail($email, $restore_code) {
        $email_confirm_link = ConfigHelper::siteURL()."/auth/restore?code=$restore_code";
        //Mail::to($email)->send(new ConfirmAccount($email_confirm_link)); // todo: emails
    }

    private function loginAsUser($user) {
        $user->load('info');
        $user->append('additional_settings');
        $user->append('links');
        $user->append('unread_notifications_count');
        auth()->login($user);
        return response()->json($user)->withCookie(
            'token',
            auth()->getToken()->get(),
            config('jwt.ttl'),
            '/'
        );
    }


}
