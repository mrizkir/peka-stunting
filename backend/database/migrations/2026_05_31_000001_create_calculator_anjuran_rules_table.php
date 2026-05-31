<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('calculator_anjuran_rules', function (Blueprint $table) {
			$table->id();
			$table->foreignId('education_content_id')
				->constrained('education_contents')
				->cascadeOnDelete();
			$table->unsignedSmallInteger('sort_order')->default(0);
			$table->string('metric', 32);
			$table->string('indicator', 64)->nullable();
			$table->decimal('threshold', 10, 2)->nullable();
			$table->string('operator', 8)->default('gt');
			$table->boolean('is_default')->default(false);
			$table->string('label');
			$table->string('slug', 64)->nullable();
			$table->text('anjuran');
			$table->timestamps();

			$table->index(
				['education_content_id', 'metric', 'sort_order'],
				'calc_anjuran_content_metric_sort_idx',
			);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('calculator_anjuran_rules');
	}
};
