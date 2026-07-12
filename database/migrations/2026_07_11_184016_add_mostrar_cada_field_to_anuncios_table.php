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
    Schema::table('anuncios', function (Blueprint $table) {
      // Mostrar el anuncio cada x minutos. Por defecto, 240 minutos (4 horas)
      $table->integer('mostrar_cada')->default(240);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('anuncios', function (Blueprint $table) {
      $table->dropColumn('mostrar_cada');
    });
  }
};
