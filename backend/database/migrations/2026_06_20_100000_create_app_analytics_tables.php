<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('app_events', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->uuid('session_id');
			$table->string('event_name', 64);
			$table->json('properties')->nullable();
			$table->string('platform', 16)->default('android');
			$table->string('app_version', 32)->nullable();
			$table->dateTime('occurred_at');
			$table->timestamps();

			$table->index(['event_name', 'occurred_at']);
			$table->index(['user_id', 'occurred_at']);
			$table->index(['session_id', 'occurred_at']);
		});

		Schema::create('app_usage_sessions', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->uuid('session_id');
			$table->dateTime('started_at');
			$table->dateTime('ended_at');
			$table->unsignedInteger('duration_seconds');
			$table->string('platform', 16)->default('android');
			$table->string('app_version', 32)->nullable();
			$table->timestamps();

			$table->index(['user_id', 'started_at']);
			$table->index(['started_at']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('app_usage_sessions');
		Schema::dropIfExists('app_events');
	}
};
