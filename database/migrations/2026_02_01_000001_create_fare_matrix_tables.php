<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fare_matrix', function (Blueprint $t) {
            $t->id(); $t->string('exit_point'); $t->string('route')->nullable(); $t->string('destination');
            $t->unsignedSmallInteger('km'); $t->decimal('fare', 8, 2); $t->decimal('discounted_fare', 8, 2);
            $t->timestamps(); $t->index('exit_point');
        });
        Schema::create('city_proper_barangays', function (Blueprint $t) { $t->id(); $t->string('name'); $t->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('fare_matrix'); Schema::dropIfExists('city_proper_barangays'); }
};
