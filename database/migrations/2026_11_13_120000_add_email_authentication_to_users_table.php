<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('has_email_authentication')->default(false)->after('app_authentication_recovery_codes');
        });

        // Existing administrators get the "email me a code" option straight away, so anyone
        // locked out of their authenticator app can still sign in.
        DB::table('users')->where('is_admin', true)->update(['has_email_authentication' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('has_email_authentication');
        });
    }
};
