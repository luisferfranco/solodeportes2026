<?php

use App\Models\Evento;
use App\Models\Participacion;
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
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Participacion::class)
              ->constrained()
              ->onDelete('cascade');
            $table->foreignIdFor(Evento::class)
              ->constrained()
              ->onDelete('cascade');
            $table->integer('ronda');
            $table->integer('aciertos');
            $table->integer('diferencias');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};
