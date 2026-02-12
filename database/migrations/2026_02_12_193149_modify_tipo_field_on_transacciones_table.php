<?php

use App\Models\Transaccion;
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
      Schema::table('transacciones', function (Blueprint $table) {
        $table->dropColumn(['tipo', 'estado']);
        $table->string('tipo')->nullable()->after('monto');
        $table->string('estado')->nullable()->after('tipo');
      });

      Transaccion::where('monto', '>', 0)
        ->update(['tipo' => 'deposito', 'estado' => 'aprobada']);
      Transaccion::where('monto', '<', 0)
        ->update(['tipo' => 'retiro', 'estado' => 'aprobada']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('transacciones', function (Blueprint $table) {
        $table->dropColumn(['tipo', 'estado']);
        $table->enum('tipo', ['deposito', 'retiro'])->nullable()->after('monto');
        $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'cancelada'])->nullable()->after('tipo');
      });
    }
};
