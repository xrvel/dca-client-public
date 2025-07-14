<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Ensure this is the correct namespace for your User model

class MyUserResetPassword extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'my:user-reset-password {userId} {newPassword}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset a user\'s password by user ID and new password';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$userId = $this->argument('userId');
		$newPassword = $this->argument('newPassword');

		// Validate input
		if (empty($userId)) {
			$this->error('Error: userId cannot be empty.');
			return Command::FAILURE;
		}

		if (empty($newPassword)) {
			$this->error('Error: newPassword cannot be empty.');
			return Command::FAILURE;
		}

		$user = User::find($userId);

		if (!$user) {
			$this->error("User with ID $userId not found.");
			return Command::FAILURE;
		}

		$user->password = Hash::make($newPassword);
		$user->save();

		$this->info("Password for user ID $userId has been successfully reset to : " . $newPassword);

		return Command::SUCCESS;
	}
}
