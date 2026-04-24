<?php

namespace App\Console\Commands;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;

class SyncRolesAndPermissions extends Command
{
    protected $signature = 'roles:sync';

    protected $description = 'Sync roles and permissions defined in RolePermissionSeeder (idempotent).';

    public function handle(): int
    {
        $this->info('Syncing roles and permissions...');

        $this->call('db:seed', [
            '--class' => RolePermissionSeeder::class,
            '--force' => true,
        ]);

        $this->info('Done.');

        return self::SUCCESS;
    }
}
