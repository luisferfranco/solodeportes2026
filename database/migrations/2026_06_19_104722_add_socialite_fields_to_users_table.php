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
    Schema::table('users', function (Blueprint $table) {
      $table->string('password')->nullable()->change();
      $table->string('external_id')->nullable();
      $table->string('external_auth')->default('google');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('password')->nullable(false)->change();
      $table->dropColumn('external_id');
      $table->dropColumn('external_auth');
    });
  }
};
