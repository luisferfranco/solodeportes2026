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
    Schema::table('participaciones', function (Blueprint $table) {
      // Cambiar el campo 'survivor' a booleano nullable
      $table->boolean('survivor')->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('participaciones', function (Blueprint $table) {
      // Revert the 'survivor' field to its previous state (assuming it was non-nullable boolean)
      $table->boolean('survivor')->nullable(false)->change();
    });
  }
};
