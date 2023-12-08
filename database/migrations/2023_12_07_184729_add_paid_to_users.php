<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('right_id')->default(1);
            $table->foreign('right_id')->references('id')->on('rights');
        });

        DB::table('users')->insert([
            ['firstname' => 'Admin', 'lastname' => 'Admin', 'password' => 'Admin', 'email' => 'admin@admin.fr', 'right_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
