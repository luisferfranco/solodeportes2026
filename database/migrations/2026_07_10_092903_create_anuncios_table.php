<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('anuncios', function (Blueprint $table) {
      $table->id();

      $table->string('titulo');
      $table->text('cuerpo');
      $table->date('desde')->nullable();
      $table->date('hasta')->nullable();
      $table->enum('estado', ['activo', 'inactivo'])->default('activo');
      $table->foreignIdFor(User::class, 'autor_id')
        ->constrained('users')
        ->onDelete('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('anuncios');
  }
};
