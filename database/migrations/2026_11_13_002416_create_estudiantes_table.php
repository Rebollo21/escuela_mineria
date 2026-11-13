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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 20)->unique();
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
            $table->integer('edad_ingreso');
            $table->boolean('tiene_beca')->default(false);
            $table->boolean('trabaja')->default(false);
            $table->enum('estado_final', ['activo', 'abandono'])->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
