<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('education_contents', function (Blueprint $table) {
			$table->json('calculator_config')->nullable()->after('body');
		});
	}

	public function down(): void
	{
		Schema::table('education_contents', function (Blueprint $table) {
			$table->dropColumn('calculator_config');
		});
	}
};
