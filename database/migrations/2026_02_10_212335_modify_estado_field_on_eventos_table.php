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
        // 1) añadir columna temporal string y copiar los valores existentes
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('estado_tmp')->nullable()->after('descripcion');
        });

        \DB::statement('UPDATE eventos SET estado_tmp = estado');

        // 2) eliminar la columna enum original
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // 3) crear la columna final string y restaurar los valores desde la temporal
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('estado')->nullable()->after('descripcion');
        });

        \DB::statement('UPDATE eventos SET estado = estado_tmp');

        // 4) eliminar columna temporal
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('estado_tmp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) crear columna temporal enum y copiar valores desde la columna string actual
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('estado_tmp')->nullable()->after('descripcion');
        });

        \DB::statement('UPDATE eventos SET estado_tmp = estado');

        // 2) eliminar la columna string actual
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // 3) recrear la columna enum y restaurar los valores desde la temporal
        Schema::table('eventos', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'en_progreso', 'finalizado'])->nullable()->after('descripcion');
        });

        \DB::statement('UPDATE eventos SET estado = estado_tmp');

        // 4) eliminar columna temporal
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('estado_tmp');
        });
    }
};
