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
        Schema::create('education_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('education_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('education_menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('education_items')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['menu_id', 'slug']);
            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });

        Schema::create('education_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->unique()->constrained('education_items')->cascadeOnDelete();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_contents');
        Schema::dropIfExists('education_items');
        Schema::dropIfExists('education_menus');
    }
};
