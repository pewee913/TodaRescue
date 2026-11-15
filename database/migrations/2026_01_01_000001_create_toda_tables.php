<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('places', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->decimal('lat', 10, 7); $t->decimal('lng', 10, 7); $t->timestamps();
        });
        Schema::create('todas', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->decimal('lat', 10, 7); $t->decimal('lng', 10, 7);
            $t->string('serves'); $t->string('phone'); $t->unsignedInteger('units')->default(0);
            $t->boolean('is_active')->default(true); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('todas'); Schema::dropIfExists('places'); }
};
