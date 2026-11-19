<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('todas', function (Blueprint $table) {
            $table->text('barangays_passed')->nullable()->after('serves');
            $table->text('landmarks')->nullable()->after('barangays_passed');
        });
    }

    public function down(): void
    {
        Schema::table('todas', function (Blueprint $table) {
            $table->dropColumn(['barangays_passed', 'landmarks']);
        });
    }
};
