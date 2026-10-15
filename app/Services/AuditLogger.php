<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Append-only audit trail of administrative and security-relevant actions.
 */
final class AuditLogger
{
    public static function log(string $action, ?string $entityType = null, int|string|null $entityId = null, array $details = [], ?int $actorId = null): void
    {
        try {
            $request = null;
            try {
                $request = app('request');
            } catch (\Throwable) {
            }
            db()->insert('audit_logs', [
                'actor_id' => $actorId ?? auth()->id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId === null ? null : (string) $entityId,
                'details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'ip_address' => $request?->ip(),
            ]);
        } catch (\Throwable $e) {
            app('logger')->error('Audit log write failed', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }
}
