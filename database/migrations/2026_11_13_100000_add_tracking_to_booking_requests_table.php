<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_requests', function (Blueprint $table): void {
            $table->string('reference', 20)->nullable()->unique()->after('id');
            $table->foreignId('user_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
            // Shown to the customer when they track the request, e.g. the confirmed time.
            $table->text('customer_note')->nullable()->after('status');
        });

        // Give requests made before tracking existed a reference too.
        DB::table('booking_requests')->whereNull('reference')->orderBy('id')->each(function (object $booking): void {
            do {
                $reference = 'BK-'.Str::upper(Str::random(8));
            } while (DB::table('booking_requests')->where('reference', $reference)->exists());

            DB::table('booking_requests')->where('id', $booking->id)->update(['reference' => $reference]);
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropUnique(['reference']);
            $table->dropColumn(['reference', 'customer_note']);
        });
    }
};
