<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Evento;
use App\Models\User;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('invitaciones', function (Blueprint $table) {
      $table->id();

      $table->foreignIdFor(Evento::class)->constrained()->onDelete('cascade');
      $table->foreignIdFor(User::class, 'invitado_id')->constrained('users')->onDelete('cascade');
      $table->foreignIdFor(User::class, 'invitante_id')->constrained('users')->onDelete('cascade');
      $table->string('codigo')->unique();
      $table->datetime('accepted_at')->nullable();
      $table->datetime('rejected_at')->nullable();
      $table->datetime('caduca')->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('invitaciones');
  }
};
