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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 32)->nullable()->unique()->after('name');
        });

        DB::table('users')->select(['id', 'name'])->orderBy('id')->each(function (object $user): void {
            $base = Str::lower(Str::slug((string) $user->name, '_'));
            $base = preg_replace('/[^a-z0-9_]+/', '', $base) ?: 'user';
            $base = Str::substr($base, 0, 32);
            $candidate = $base;
            $suffix = 1;

            while (DB::table('users')->where('username', $candidate)->exists()) {
                $tail = '_'.$suffix++;
                $candidate = Str::substr($base, 0, 32 - Str::length($tail)).$tail;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
