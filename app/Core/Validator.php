<?php

namespace App\Core;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data, $rules);
        $validator->validate();
        return $validator;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                $this->applyRule($field, $value, $ruleName, $params);
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $fieldLabel = $this->formatFieldLabel($field);

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "{$fieldLabel} wajib diisi.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Format {$fieldLabel} tidak valid.");
                }
                break;

            case 'phone':
            case 'whatsapp':
                if (!empty($value)) {
                    // Standard Indonesian phone format: 08xxx, +62xxx, or 62xxx (10 to 15 digits)
                    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$value);
                    if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
                        $this->addError($field, "Nomor WhatsApp harus terdiri dari 10-15 digit angka yang valid.");
                    }
                }
                break;

            case 'min':
                $min = (int) ($params[0] ?? 0);
                if (!empty($value) && strlen((string)$value) < $min) {
                    $this->addError($field, "{$fieldLabel} minimal harus {$min} karakter.");
                }
                break;

            case 'max':
                $max = (int) ($params[0] ?? 255);
                if (!empty($value) && strlen((string)$value) > $max) {
                    $this->addError($field, "{$fieldLabel} maksimal {$max} karakter.");
                }
                break;

            case 'matches':
            case 'confirmed':
                $targetField = $params[0] ?? ($field . '_confirm');
                $targetValue = $this->data[$targetField] ?? null;
                if ($value !== $targetValue) {
                    $targetLabel = $this->formatFieldLabel($targetField);
                    $this->addError($field, "{$fieldLabel} tidak cocok dengan {$targetLabel}.");
                }
                break;

            case 'unique':
                $table = $params[0] ?? '';
                $column = $params[1] ?? $field;
                $exceptId = $params[2] ?? null;

                if (!empty($value) && !empty($table)) {
                    $pdo = Database::getConnection();
                    $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val";
                    $queryParams = ['val' => $value];

                    if ($exceptId !== null) {
                        $sql .= " AND id != :except_id";
                        $queryParams['except_id'] = $exceptId;
                    }

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($queryParams);
                    if ((int)$stmt->fetchColumn() > 0) {
                        $this->addError($field, "{$fieldLabel} '{$value}' sudah terdaftar dalam sistem.");
                    }
                }
                break;

            case 'exists':
                $table = $params[0] ?? '';
                $column = $params[1] ?? 'id';
                if (!empty($value) && !empty($table)) {
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val");
                    $stmt->execute(['val' => $value]);
                    if ((int)$stmt->fetchColumn() === 0) {
                        $this->addError($field, "Pilihan {$fieldLabel} tidak valid.");
                    }
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "{$fieldLabel} harus berupa angka.");
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params, true)) {
                    $this->addError($field, "Nilai {$fieldLabel} tidak sesuai pilihan.");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function formatFieldLabel(string $field): string
    {
        $labels = [
            'name'             => 'Nama lengkap',
            'email'            => 'Email',
            'phone'            => 'Nomor WhatsApp',
            'village_id'       => 'Desa',
            'password'         => 'Kata sandi',
            'password_confirm' => 'Konfirmasi kata sandi',
            'nik'              => 'NIK',
            'otp'              => 'Kode OTP',
            'token'            => 'Token verifikasi',
        ];

        return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
    }
}
