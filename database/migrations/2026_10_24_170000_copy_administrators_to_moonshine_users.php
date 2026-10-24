<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MoonShine\Laravel\Models\MoonshineUserRole;

return new class () extends Migration {
    /**
     * Переносим администраторов в moonshine_users вместе с готовыми хешами паролей,
     * чтобы войти в MoonShine можно было прежним логином и паролем.
     */
    public function up(): void
    {
        DB::table('administrators')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->each(function (object $admin): void {
                DB::table('moonshine_users')->updateOrInsert(
                    ['email' => $admin->email],
                    [
                        'moonshine_user_role_id' => MoonshineUserRole::DEFAULT_ROLE_ID,
                        'name' => $admin->name,
                        'password' => $admin->password,
                        'created_at' => $admin->created_at ?? now(),
                        'updated_at' => now(),
                    ]
                );
            });
    }

    public function down(): void
    {
        DB::table('moonshine_users')
            ->whereIn('email', DB::table('administrators')->select('email'))
            ->delete();
    }
};
