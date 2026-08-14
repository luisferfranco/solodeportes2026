<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Temporada;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('eventos', function (Blueprint $table) {
      $table->integer('jornada_inicio')->nullable();
      $table->integer('jornada_fin')->nullable()->after('jornada_inicio');
    });

    foreach (Temporada::all() as $temporada) {
      $temporada->eventos()->update([
        'jornada_inicio' => 1,
        'jornada_fin' => $temporada->rondafinal,
      ]);
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('eventos', function (Blueprint $table) {
      $table->dropColumn('jornada_inicio');
      $table->dropColumn('jornada_fin');
    });
  }
};
