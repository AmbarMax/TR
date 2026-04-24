<?php

namespace App\Listeners;

use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class LogRoleChange
{
    public function handleAttached(RoleAttached $event): void
    {
        $this->record($event->model, $event->rolesOrIds, 'assigned');
    }

    public function handleDetached(RoleDetached $event): void
    {
        $this->record($event->model, $event->rolesOrIds, 'revoked');
    }

    private function record($subject, $rolesOrIds, string $action): void
    {
        // Causer is the currently-authenticated user if any; null when the
        // event fires outside an HTTP request (seeders, migrations, queued
        // jobs, tinker).
        $causer = auth()->user();

        activity('role')
            ->performedOn($subject)
            ->causedBy($causer)
            ->withProperties([
                'role'   => $rolesOrIds,
                'action' => $action,
            ])
            ->log('role_' . $action);
    }
}
