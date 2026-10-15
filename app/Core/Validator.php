<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Server-side validation with translatable messages.
 *
 * Rules: required, email, min:n, max:n, between:a,b, in:a,b,c, confirmed,
 * integer, numeric, slug, url, date, accepted, unique:table,column[,ignoreId],
 * exists:table,column, regex:/pattern/, password (strength policy).
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data, private readonly array $rules)
    {
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function passes(): bool
    {
        $this->errors = [];
        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            $isEmpty = $value === null || $value === '' || $value === [];
            if ($isEmpty && !in_array('required', $rules, true) && !in_array('accepted', $rules, true)) {
                continue;
            }
            foreach ($rules as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $error = $this->check($field, $name, $arg, $value, $isEmpty);
                if ($error !== null) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Validate or throw; returns only validated keys.
     */
    public function validate(): array
    {
        if (!$this->passes()) {
            $old = $this->data;
            foreach (['password', 'password_confirmation', 'current_password', 'new_password'] as $secret) {
                unset($old[$secret]);
            }
            throw new ValidationException($this->errors, $old);
        }
        $out = [];
        foreach (array_keys($this->rules) as $field) {
            $value = $this->data[$field] ?? null;
            $out[$field] = is_string($value) ? trim($value) : $value;
        }
        return $out;
    }

    private function check(string $field, string $rule, ?string $arg, mixed $value, bool $isEmpty): ?string
    {
        $label = __('field.' . $field);
        $len = is_string($value) ? mb_strlen($value) : (is_array($value) ? count($value) : 0);

        return match ($rule) {
            'required' => $isEmpty ? __('validation.required', ['field' => $label]) : null,
            'accepted' => in_array($value, ['1', 1, 'on', 'yes', true, 'true'], true) ? null : __('validation.accepted', ['field' => $label]),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) && mb_strlen((string) $value) <= 190 ? null : __('validation.email', ['field' => $label]),
            'min' => $len < (int) $arg ? __('validation.min', ['field' => $label, 'min' => $arg]) : null,
            'max' => $len > (int) $arg ? __('validation.max', ['field' => $label, 'max' => $arg]) : null,
            'between' => (function () use ($value, $arg, $label) {
                [$a, $b] = array_map('floatval', explode(',', (string) $arg));
                return is_numeric($value) && $value >= $a && $value <= $b ? null : __('validation.between', ['field' => $label, 'min' => $a, 'max' => $b]);
            })(),
            'in' => in_array((string) $value, explode(',', (string) $arg), true) ? null : __('validation.in', ['field' => $label]),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false ? null : __('validation.integer', ['field' => $label]),
            'numeric' => is_numeric($value) ? null : __('validation.numeric', ['field' => $label]),
            'slug' => preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $value) ? null : __('validation.slug', ['field' => $label]),
            'url' => filter_var($value, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', (string) $value) ? null : __('validation.url', ['field' => $label]),
            'date' => strtotime((string) $value) !== false ? null : __('validation.date', ['field' => $label]),
            'confirmed' => ($value === ($this->data[$field . '_confirmation'] ?? null)) ? null : __('validation.confirmed', ['field' => $label]),
            'regex' => preg_match((string) $arg, (string) $value) ? null : __('validation.regex', ['field' => $label]),
            'password' => $this->passwordPolicy((string) $value),
            'unique' => $this->unique((string) $arg, $value) ? null : __('validation.unique', ['field' => $label]),
            'exists' => $this->exists((string) $arg, $value) ? null : __('validation.exists', ['field' => $label]),
            default => throw new \InvalidArgumentException("Unknown validation rule [$rule]."),
        };
    }

    private function passwordPolicy(string $value): ?string
    {
        if (mb_strlen($value) < 12
            || !preg_match('/[a-z]/', $value)
            || !preg_match('/[A-Z]/', $value)
            || !preg_match('/\d/', $value)) {
            return __('validation.password');
        }
        return null;
    }

    private function unique(string $arg, mixed $value): bool
    {
        $parts = explode(',', $arg);
        [$table, $column] = [$parts[0], $parts[1] ?? 'id'];
        $ignore = isset($parts[2]) ? (int) $parts[2] : null;
        $this->guardIdentifier($table, $column);
        $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = ?";
        $params = [$value];
        if ($ignore) {
            $sql .= ' AND id <> ?';
            $params[] = $ignore;
        }
        return (int) db()->value($sql, $params) === 0;
    }

    private function exists(string $arg, mixed $value): bool
    {
        [$table, $column] = array_pad(explode(',', $arg), 2, 'id');
        $this->guardIdentifier($table, $column);
        return (int) db()->value("SELECT COUNT(*) FROM `$table` WHERE `$column` = ?", [$value]) > 0;
    }

    private function guardIdentifier(string ...$names): void
    {
        foreach ($names as $name) {
            if (!preg_match('/^[a-z_]+$/', $name)) {
                throw new \InvalidArgumentException('Invalid identifier in validation rule.');
            }
        }
    }
}
