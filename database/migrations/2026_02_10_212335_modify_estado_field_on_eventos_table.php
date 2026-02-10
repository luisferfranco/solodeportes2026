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
      Schema::table('eventos', function (Blueprint $table) {
        // el enum de estado en eventos se va a manejar con un enum de php, por lo que se cambia el tipo de dato a string
        $table->dropColumn('estado');
        $table->string('estado')->nullable()->after('descripcion');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('eventos', function (Blueprint $table) {
        $table->dropColumn('estado');
        $table->enum('estado', ['pendiente', 'en_progreso', 'finalizado'])->nullable()->after('descripcion');
      });
    }
};
