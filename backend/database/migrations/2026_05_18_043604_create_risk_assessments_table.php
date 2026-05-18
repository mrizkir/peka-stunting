<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('risk_assessments', function (Blueprint $table) {
			$table->id();
			$table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
			$table->foreignId('measurement_id')->constrained('measurements')->cascadeOnDelete();
			$table->foreignId('assessed_by')->constrained('users')->cascadeOnDelete();
			$table->enum('status', ['normal', 'risk', 'need_follow_up']);
			$table->unsignedTinyInteger('score');
			$table->json('indicators');
			$table->text('summary')->nullable();
			$table->timestamp('assessed_at');
			$table->timestamps();

			$table->index(['child_id', 'assessed_at']);
			$table->index('status');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('risk_assessments');
	}
};
