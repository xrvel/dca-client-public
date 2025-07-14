<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;  // Add this line
use Illuminate\Support\Facades\Hash;  // Add this line
use App\Models\User;  // Add this line

class MyUsersSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		if (DB::table('users')->doesntExist()) {
			$user = User::create([
				'name' => env('DCA_CLIENT_ADMIN_NAME', 'Admin'),
				'email' => env('DCA_CLIENT_ADMIN_ENAIL', 'admin@localhost.com'),
				'password' => Hash::make(env('DCA_CLIENT_ADMIN_PASSWORD', sha1(time() . rand(1, 1000)))),
			]);
		}
	}
}
