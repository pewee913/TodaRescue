<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('todas', function (Blueprint $table) {
            $table->json('route_waypoints')->nullable()->after('landmarks');
        });
    }

    public function down(): void
    {
        Schema::table('todas', function (Blueprint $table) {
            $table->dropColumn(['route_waypoints']);
        });
    }
};
