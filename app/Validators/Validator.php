<?php

declare(strict_types=1);

namespace App\Validators;

use App\Services\Database;

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

    public function passes(): bool
    {
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

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                switch ($rule) {
                    case 'required':
                        if ($value === null || trim((string) $value) === '') {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                        }
                        break;

                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, 'Please enter a valid email address.');
                        }
                        break;

                    case 'min':
                        $min = (int) ($params[0] ?? 0);
                        if (!empty($value) && strlen((string) $value) < $min) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.");
                        }
                        break;

                    case 'max':
                        $max = (int) ($params[0] ?? 255);
                        if (!empty($value) && strlen((string) $value) > $max) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.");
                        }
                        break;

                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid number.');
                        }
                        break;

                    case 'unique':
                        // Format: unique:table,column,exceptId,idColumn
                        $table = $params[0] ?? '';
                        $column = $params[1] ?? $field;
                        $exceptId = $params[2] ?? null;
                        $idCol = $params[3] ?? 'id';

                        if (!empty($value) && !empty($table)) {
                            $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val";
                            $bindings = [':val' => $value];
                            if ($exceptId !== null) {
                                $sql .= " AND `{$idCol}` != :except";
                                $bindings[':except'] = $exceptId;
                            }
                            $count = (int) Database::query($sql, $bindings)->fetchColumn();
                            if ($count > 0) {
                                $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is already taken.');
                            }
                        }
                        break;

                    case 'confirmed':
                        $confirmField = $field . '_confirmation';
                        $confirmValue = $this->data[$confirmField] ?? null;
                        if ($value !== $confirmValue) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' confirmation does not match.');
                        }
                        break;
                }
            }
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
}
