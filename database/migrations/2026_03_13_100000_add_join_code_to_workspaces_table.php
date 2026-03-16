<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('join_code')->nullable()->after('icon_key');
        });

        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $existingCodes = [];

        DB::table('workspaces')
            ->select('id')
            ->orderBy('id')
            ->each(function (object $workspace) use ($alphabet, &$existingCodes): void {
                do {
                    $characters = '';

                    for ($index = 0; $index < 9; $index++) {
                        $characters .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                    }

                    $code = implode('-', str_split($characters, 3));
                } while (in_array($code, $existingCodes, true));

                DB::table('workspaces')
                    ->where('id', $workspace->id)
                    ->update(['join_code' => $code]);

                $existingCodes[] = $code;
            });

        Schema::table('workspaces', function (Blueprint $table) {
            $table->unique('join_code');
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropUnique(['join_code']);
            $table->dropColumn('join_code');
        });
    }
};
