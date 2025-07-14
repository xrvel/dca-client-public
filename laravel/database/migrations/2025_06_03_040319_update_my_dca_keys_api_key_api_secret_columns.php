<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (Schema::hasTable('my_dca_keys')) {
			$columns = DB::getSchemaBuilder()->getColumnListing('my_dca_keys');

			if (in_array('api_key', $columns) && in_array('api_secret', $columns)) {
				Schema::table('my_dca_keys', function (Blueprint $table) {
					$table->text('api_key')->nullable()->comment('API key')->change();
					$table->text('api_secret')->nullable()->comment('API secret')->change();
				});
			}
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (Schema::hasTable('my_dca_keys')) {
			$columns = DB::getSchemaBuilder()->getColumnListing('my_dca_keys');

			if (in_array('api_key', $columns) && in_array('api_secret', $columns)) {
				Schema::table('my_dca_keys', function (Blueprint $table) {
					$table->string('api_key', 200)->default('')->comment('API key')->change();
					$table->string('api_secret', 200)->default('')->comment('API secret')->change();
				});
			}
		}
	}
};
