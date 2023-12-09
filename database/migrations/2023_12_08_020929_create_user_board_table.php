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
        Schema::create('board_right', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

        });

        DB::table('board_right')->insert([
            ['name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Editor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Viewer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('user_board', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_right_id')->default(1)->constrained('board_right');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('board_id')->constrained('boards');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_board');
    }
};
