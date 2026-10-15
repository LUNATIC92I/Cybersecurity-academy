<?php

declare(strict_types=1);

namespace App\Services;

/**
 * In-app notifications. The payload stores a translation key and parameters
 * so each notification renders in the reader's current language.
 */
final class NotificationService
{
    public static function notify(int $userId, string $type, array $params = [], ?string $link = null): void
    {
        db()->insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'data' => json_encode(['key' => 'notification.' . $type, 'params' => $params], JSON_UNESCAPED_UNICODE),
            'link' => $link,
        ]);
    }

    public static function unreadCount(int $userId): int
    {
        return (int) db()->value('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL', [$userId]);
    }

    public static function recent(int $userId, int $limit = 20): array
    {
        $rows = db()->fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ' . max(1, min(100, $limit)),
            [$userId]
        );
        foreach ($rows as &$row) {
            $data = json_decode((string) $row['data'], true) ?: [];
            $row['text'] = __($data['key'] ?? 'notification.generic', $data['params'] ?? []);
        }
        return $rows;
    }

    public static function markAllRead(int $userId): void
    {
        db()->query('UPDATE notifications SET read_at = UTC_TIMESTAMP() WHERE user_id = ? AND read_at IS NULL', [$userId]);
    }
}
