<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(string $action, string $auditableType, int $auditableId, ?array $before, ?array $after, ?int $outletId = null): void
    {
        AuditLog::create([
            'outlet_id' => $outletId ?? OutletContext::id(),
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'before' => $before,
            'after' => $after,
        ]);
    }
}
