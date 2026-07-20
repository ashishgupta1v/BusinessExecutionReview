<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Google OAuth support on the users table.
 * Password is made nullable so social-only accounts don't need a local password.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('google_id')->nullable()->unique()->after('email');
            $t->string('avatar')->nullable()->after('google_id');
            $t->string('password')->nullable()->change(); // requires doctrine/dbal on older Laravel; native on 11+
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['google_id', 'avatar']);
            // leave password NOT-NULL change out of down() to avoid failing on null rows
        });
    }
};
