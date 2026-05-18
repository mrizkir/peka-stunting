<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('guardians', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('phone', 15)->nullable();
			$table->string('relationship')->nullable();
			$table->text('address')->nullable();
			$table->timestamps();
		});

		Schema::create('children', function (Blueprint $table) {
			$table->id();
			$table->foreignId('guardian_id')->nullable()->constrained('guardians')->nullOnDelete();
			$table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
			$table->string('name');
			$table->char('gender', 1);
			$table->date('birth_date');
			$table->string('nik', 16)->nullable()->unique();
			$table->string('village')->nullable();
			$table->string('posyandu')->nullable();
			$table->text('notes')->nullable();
			$table->timestamps();

			$table->index(['name', 'birth_date']);
			$table->index('village');
		});

		Schema::create('measurements', function (Blueprint $table) {
			$table->id();
			$table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
			$table->foreignId('measured_by')->constrained('users')->cascadeOnDelete();
			$table->date('measured_at');
			$table->decimal('weight_kg', 5, 2);
			$table->decimal('height_cm', 5, 1);
			$table->unsignedSmallInteger('age_months');
			$table->text('notes')->nullable();
			$table->timestamps();

			$table->index(['child_id', 'measured_at']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('measurements');
		Schema::dropIfExists('children');
		Schema::dropIfExists('guardians');
	}
};
