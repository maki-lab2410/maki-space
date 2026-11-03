<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('room_code', 50)->nullable()->after('id');
        });

        // Default codes for existing rooms
        $defaultCodes = [
            'Koding dan Reimbursement' => 'LAB-01',
            'Statistik dan Pelaporan'  => 'LAB-02',
            'Anatomi Fisiologi'        => 'LAB-03',
            'Ruang Serbaguna'          => 'LAB-04',
        ];

        foreach ($defaultCodes as $nama => $kode) {
            DB::table('rooms')->where('name', $nama)->update(['room_code' => $kode]);
        }

        $remaining = DB::table('rooms')->whereNull('room_code')->get();
        $counter = 5;
        foreach ($remaining as $r) {
            DB::table('rooms')->where('id', $r->id)->update([
                'room_code' => 'R-' . str_pad($counter++, 2, '0', STR_PAD_LEFT)
            ]);
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->unique('room_code');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique(['room_code']);
            $table->dropColumn('room_code');
        });
    }
};
