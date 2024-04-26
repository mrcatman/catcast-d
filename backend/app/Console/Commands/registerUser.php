<?php

namespace App\Console\Commands;

use App\Helpers\CommonResponses;
use App\Helpers\CryptoHelper;
use App\Models\AccountConnection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class registerUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->ask('Enter a email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo 'Enter a correct email!';
            return;
        }
        $email_taken = AccountConnection::where([
            'account_type' => 'email',
            'account_name' => $email
        ])->first();

        if ($email_taken) {
            echo 'Email already taken!';
            return;
        }

        $username = $this->ask('Enter a username');
        if (!ctype_alnum($username)) {
            echo 'Enter a correct (alphanumeric) username!';
            return;
        }
        $username_taken = User::where(['username' => $username, 'domain' => null])->count() > 0;
        if ($username_taken) {
            echo 'Username already taken!';
            return;
        }

        $is_admin = $this->confirm('Should this user be an administrator?');
        $password = Str::password(12);

        $user = new User([
            'email' => $email,
            'username' => $username,
        ]);
        $user->role_id = $is_admin ? User::ROLE_ID_ADMIN : User::ROLE_ID_USER;
        $user->password = Hash::make($password);
        CryptoHelper::generateKeys($user);
        $user->save();
        $user->accountConnections()->create([
            'user_id' => $user->id,
            'account_type' => 'email',
            'account_name' => $email,
            'confirmed' => true
        ]);

        echo "User created!".PHP_EOL."Username: ".$username.PHP_EOL."Password: ".$password;

    }
}
