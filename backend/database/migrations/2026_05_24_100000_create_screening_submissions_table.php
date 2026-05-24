<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('screening_submissions', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('education_item_id')->nullable()->constrained('education_items')->nullOnDelete();
			$table->string('calculator_slug', 64);
			$table->string('menu_slug', 64);
			$table->unsignedTinyInteger('yes_count');
			$table->unsignedTinyInteger('total_questions');
			$table->unsignedTinyInteger('risk_yes_threshold');
			$table->string('category', 32);
			$table->string('category_label');
			$table->json('answers');
			$table->json('questions_snapshot')->nullable();
			$table->timestamp('submitted_at');
			$table->timestamps();

			$table->index(['user_id', 'calculator_slug', 'submitted_at']);
			$table->index(['menu_slug', 'calculator_slug']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('screening_submissions');
	}
};
