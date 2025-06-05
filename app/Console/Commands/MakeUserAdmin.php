<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-user-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->ask('User email');
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found.");
            return;
        }

        $user->is_admin = true;
        $user->save();
        $this->info("User is now an admin.");
    }
}
