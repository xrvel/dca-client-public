<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('my_dca_keys', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->default(0)->index()->comment('User ID of the owner of this data.');
			$table->string('label', 100)->default('')->comment('Label for the key');
			$table->string('exchange_name', 100)->default('')->comment('Exchange name');
			$table->text('api_key')->nullable()->comment('API key');
			$table->text('api_secret')->nullable()->comment('API secret');
			$table->unsignedBigInteger('ok_last_check_timestamp')->default(0)->comment('Last time this key is ok.');
			$table->unsignedBigInteger('error_last_check_timestamp')->default(0)->comment('Last time this key is error.');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('my_dca_keys');
	}
};
