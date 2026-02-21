<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add column as nullable initially so existing rows can be filled
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nombre');
        });

        // populate the slug column for existing records
        $rows = DB::table('eventos')->select('id', 'nombre')->get();
        foreach ($rows as $row) {
            $slug = Str::slug($row->nombre);
            $original = $slug;
            $count = 1;

            // ensure uniqueness
            while (DB::table('eventos')->where('slug', $slug)->exists()) {
                $slug = $original.'-'.$count++;
            }

            DB::table('eventos')
                ->where('id', $row->id)
                ->update(['slug' => $slug]);
        }

        // now make the column not nullable and add unique index
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
