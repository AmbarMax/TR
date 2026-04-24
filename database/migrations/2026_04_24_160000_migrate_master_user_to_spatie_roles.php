<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SUPERADMIN_EMAILS = [
        'max@ambar.gg',
        'max@tabi.gg',
        'analaglere@gmail.com',
    ];

    private const REVOKED_EMAILS = [
        'user@corpsoft.io',
        'nick-gassiy@corpsoft.io',
        'tamara.karpenko@corpsoft.io',
        'digitaltabi.studio@gmail.com',
    ];

    public function up(): void
    {
        // Safety-net: if for some reason the rename-legacy migration didn't
        // run first (e.g. partial re-run), do it here idempotently. Normal
        // flow has this as a no-op because 2026_04_24_144000 already ran.
        if (Schema::hasTable('user_role') && ! Schema::hasTable('model_has_roles')) {
            Schema::rename('roles', 'legacy_roles');
            Schema::rename('user_role', 'legacy_user_role');
            Log::info('[9N-B-Step6-Mapping] safety-net: renamed legacy tables inline');
        }

        // Ensure the 5 Spatie roles exist on guard `api` (default for JWT).
        Artisan::call('roles:sync');

        // ---------------------------------------------------------------
        // Mapping: legacy `Master user` role -> Spatie role
        // ---------------------------------------------------------------
        if (Schema::hasTable('legacy_roles') && Schema::hasTable('legacy_user_role')) {
            $legacyMasterUsers = DB::table('legacy_user_role')
                ->join('legacy_roles', 'legacy_roles.id', '=', 'legacy_user_role.role_id')
                ->join('users', 'users.id', '=', 'legacy_user_role.user_id')
                ->where('legacy_roles.name', 'Master user')
                ->select('users.id', 'users.email')
                ->get();

            Log::info('[9N-B-Step6-Mapping] found '.$legacyMasterUsers->count().' legacy Master users');

            foreach ($legacyMasterUsers as $row) {
                $email = $row->email;

                if (in_array($email, self::SUPERADMIN_EMAILS, true)) {
                    $user = User::find($row->id);
                    if ($user) {
                        $user->assignRole('tr_superadmin');
                        Log::info("[9N-B-Step6-Mapping] assigned tr_superadmin to {$email} (user_id={$user->id})");
                    } else {
                        Log::warning("[9N-B-Step6-Mapping] superadmin {$email} referenced by legacy pivot but User::find returned null (user_id={$row->id})");
                    }
                    continue;
                }

                if (in_array($email, self::REVOKED_EMAILS, true)) {
                    Log::info("[9N-B-Step6-Mapping] skipping {$email} (revoked) — will fall back to member (user_id={$row->id})");
                    continue;
                }

                Log::warning("[9N-B-Step6-Mapping] unexpected legacy Master user {$email} not in superadmin/revoked lists (user_id={$row->id}) — will fall back to member");
            }
        } else {
            Log::info('[9N-B-Step6-Mapping] legacy pivot tables not present, skipping legacy mapping (SQLite fresh install or already cleaned)');
        }

        // ---------------------------------------------------------------
        // Default: every user without any Spatie role gets `member`.
        // Chunked to keep memory flat across 11.5k+ rows in prod.
        // ---------------------------------------------------------------
        $count = 0;
        User::whereDoesntHave('roles')->chunkById(500, function ($users) use (&$count) {
            foreach ($users as $user) {
                $user->assignRole('member');
                $count++;
            }
        });
        Log::info("[9N-B-Step6-Mapping] assigned 'member' to {$count} previously role-less users");
    }

    public function down(): void
    {
        // Reverse only the explicit superadmin assignments. The bulk
        // `member` assignment is intentionally NOT reversed because doing
        // so on rollback would strip roles from every non-staff user.
        foreach (self::SUPERADMIN_EMAILS as $email) {
            $user = User::where('email', $email)->first();
            if ($user && $user->hasRole('tr_superadmin')) {
                $user->removeRole('tr_superadmin');
                Log::info("[9N-B-Step6-Mapping:down] removed tr_superadmin from {$email} (user_id={$user->id})");
            }
        }
    }
};
