<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AvatarType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditLogIndexRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Activitylog\Models\Activity;

class AdminAuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request): JsonResponse
    {
        $perPage  = (int) ($request->validated()['per_page'] ?? 25);
        $logName  = $request->validated()['log_name'] ?? null;

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest('id');

        if ($logName) {
            $query->where('log_name', $logName);
        }

        $activities = $query->cursorPaginate($perPage);

        $items = collect($activities->items())->map(function (Activity $a) {
            return [
                'id'          => $a->id,
                'log_name'    => $a->log_name,
                'description' => $a->description,
                'event'       => $a->event,
                'causer'      => $a->causer ? [
                    'id'       => $a->causer->id ?? null,
                    'username' => $a->causer->username ?? null,
                    'name'     => $a->causer->name ?? null,
                    'avatar'   => $a->causer->avatar
                        ? $a->causer->getAvatarFile(AvatarType::Small())
                        : null,
                ] : null,
                'subject'     => $a->subject ? [
                    'type'  => class_basename($a->subject_type),
                    'id'    => $a->subject_id,
                    'label' => $this->subjectLabel($a->subject),
                ] : ($a->subject_type ? [
                    'type'  => class_basename($a->subject_type),
                    'id'    => $a->subject_id,
                    'label' => '#' . $a->subject_id,
                ] : null),
                'properties'  => $a->properties,
                'created_at'  => optional($a->created_at)->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'per_page'    => $perPage,
                'next_cursor' => $activities->nextCursor()?->encode(),
                'prev_cursor' => $activities->previousCursor()?->encode(),
            ],
        ]);
    }

    private function subjectLabel($subject): string
    {
        if (isset($subject->username)) return '@' . $subject->username;
        if (isset($subject->name))     return $subject->name;
        if (isset($subject->title))    return $subject->title;
        return '#' . ($subject->id ?? '?');
    }
}
