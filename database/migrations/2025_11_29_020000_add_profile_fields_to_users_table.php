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
            $table->string('nama_usaha')->nullable()->after('name');
            $table->enum('user_type', ['umkm', 'investor'])->default('umkm')->after('nama_usaha');
            $table->string('kategori')->nullable()->after('user_type');
            $table->string('phone')->nullable()->after('email');
            $table->text('alamat')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama_usaha', 'user_type', 'kategori', 'phone', 'alamat']);
        });
    }
};

