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
        // 1) añadir columnas temporales y copiar valores actuales
        Schema::table('transacciones', function (Blueprint $table) {
            $table->string('tipo_tmp')->nullable()->after('monto');
            $table->string('estado_tmp')->nullable()->after('tipo_tmp');
        });

        \DB::statement('UPDATE transacciones SET tipo_tmp = tipo, estado_tmp = estado');

        // 2) eliminar columnas enum originales
        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'estado']);
        });

        // 3) crear columnas string finales y restaurar valores
        Schema::table('transacciones', function (Blueprint $table) {
            $table->string('tipo')->nullable()->after('monto');
            $table->string('estado')->nullable()->after('tipo');
        });

        \DB::statement('UPDATE transacciones SET tipo = tipo_tmp, estado = estado_tmp');

        // 4) eliminar columnas temporales
        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropColumn(['tipo_tmp', 'estado_tmp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) crear columnas temporales y copiar los valores actuales
        Schema::table('transacciones', function (Blueprint $table) {
            $table->string('tipo_tmp')->nullable()->after('monto');
            $table->string('estado_tmp')->nullable()->after('tipo_tmp');
        });

        \DB::statement('UPDATE transacciones SET tipo_tmp = tipo, estado_tmp = estado');

        // 2) eliminar las columnas string actuales
        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'estado']);
        });

        // 3) recrear las columnas enum y restaurar valores desde la temporal
        Schema::table('transacciones', function (Blueprint $table) {
            $table->enum('tipo', ['deposito', 'retiro'])->nullable()->after('monto');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'cancelada'])->nullable()->after('tipo');
        });

        \DB::statement('UPDATE transacciones SET tipo = tipo_tmp, estado = estado_tmp');

        // 4) eliminar columnas temporales
        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropColumn(['tipo_tmp', 'estado_tmp']);
        });
    }
};
