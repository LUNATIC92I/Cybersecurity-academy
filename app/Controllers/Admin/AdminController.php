<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;

abstract class AdminController extends Controller
{
    protected function render(string $view, array $data = []): Response
    {
        $data['meta'] = ($data['meta'] ?? []) + ['title' => __('admin.title'), 'robots' => 'noindex'];
        return view('admin/' . $view, $data, 'layouts/admin');
    }

    protected function paginate(string $sql, array $params, int $page, int $perPage = 25): array
    {
        $page = max(1, $page);
        $total = (int) db()->value('SELECT COUNT(*) FROM (' . $sql . ') counted', $params);
        $offset = ($page - 1) * $perPage;
        $items = db()->fetchAll($sql . " LIMIT $perPage OFFSET $offset", $params);
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => (int) max(1, ceil($total / $perPage))];
    }

    /**
     * Status changes to "published" require content.publish; instructors can
     * draft and submit for review only.
     */
    protected function guardStatus(string $requested, ?string $current = null): string
    {
        $allowed = ['draft', 'in_review', 'published', 'archived'];
        if (!in_array($requested, $allowed, true)) {
            return $current ?? 'draft';
        }
        if (in_array($requested, ['published', 'archived'], true) && $requested !== $current && !auth()->can('content.publish')) {
            flash('error', __('admin.publish_denied'));
            return $current ?? 'draft';
        }
        return $requested;
    }

    protected function translationStatus(string $value): string
    {
        return in_array($value, ['missing', 'draft', 'in_review', 'published'], true) ? $value : 'draft';
    }

    /**
     * Upsert one row per locale into a *_translations table.
     */
    protected function saveTranslations(string $table, string $foreignKey, int $id, array $input, array $fields): void
    {
        foreach (['en', 'fr'] as $locale) {
            $row = $input[$locale] ?? [];
            if (!is_array($row)) {
                continue;
            }
            $data = [];
            foreach ($fields as $field) {
                $value = $row[$field] ?? null;
                $data[$field] = is_string($value) ? trim($value) : $value;
            }
            if (($data['title'] ?? $data['stem'] ?? $data['prompt'] ?? $data['name'] ?? '') === '' && $locale !== 'en') {
                continue;
            }
            if (array_key_exists('translation_status', $data)) {
                $status = $this->translationStatus((string) ($row['translation_status'] ?? 'draft'));
                if ($status === 'published' && !auth()->can('content.publish') && !auth()->can('translations.manage')) {
                    $status = 'in_review';
                }
                $data['translation_status'] = $status;
            }
            $columns = array_merge([$foreignKey, 'locale'], array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $updates = implode(', ', array_map(fn ($c) => "`$c` = VALUES(`$c`)", array_keys($data)));
            db()->query(
                "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updates",
                array_merge([$id, $locale], array_values($data))
            );
        }
    }

    protected function translationsFor(string $table, string $foreignKey, int $id): array
    {
        $out = [];
        foreach (db()->fetchAll("SELECT * FROM `$table` WHERE `$foreignKey` = ?", [$id]) as $row) {
            $out[$row['locale']] = $row;
        }
        return $out;
    }
}
