<?php

use App\Models\AppBranding;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_brandings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        $branding = AppBranding::query()->create([]);

        if (! Schema::hasTable('settings')) {
            return;
        }

        $row = DB::table('settings')
            ->where('group', 'app')
            ->where('name', 'splash_image_path')
            ->first();

        if ($row === null) {
            return;
        }

        $path = json_decode($row->payload, true);

        if (is_string($path) && $path !== '' && Storage::disk('public')->exists($path)) {
            $branding
                ->addMedia(Storage::disk('public')->path($path))
                ->preservingOriginal()
                ->toMediaCollection(AppBranding::MEDIA_COLLECTION_SPLASH);

            Storage::disk('public')->delete($path);
        }

        DB::table('settings')
            ->where('group', 'app')
            ->where('name', 'splash_image_path')
            ->delete();
    }

    public function down(): void
    {
        Schema::dropIfExists('app_brandings');
    }
};
