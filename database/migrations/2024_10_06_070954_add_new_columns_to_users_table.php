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
            $table->dropColumn('name');

            // Add the new columns where 'name' was
            $table->string('firstname')->after('email');
            $table->string('lastname')->after('firstname');
            $table->string('timezone')->default('UTC')->after('lastname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');

            $table->dropColumn(['firstname', 'lastname', 'timezone']);
        });
    }
};
