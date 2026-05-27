<?php

declare(strict_types=1);

/**
 * LaunchStack — Input Validator
 * Secure, chainable input validation helper
 */

namespace LaunchStack\Helpers;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);
        $validator->validate($rules);
        return $validator;
    }

    private function validate(array $rules): void
    {
        foreach ($rules as $field => $fieldRules) {
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value    = $this->data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "The {$field} field is required.");
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$field} must be a valid email address.");
                }
                break;

            case 'min':
                if ($value !== null && strlen((string) $value) < (int) $param) {
                    $this->addError($field, "The {$field} must be at least {$param} characters.");
                }
                break;

            case 'max':
                if ($value !== null && strlen((string) $value) > (int) $param) {
                    $this->addError($field, "The {$field} may not be greater than {$param} characters.");
                }
                break;

            case 'string':
                if ($value !== null && !is_string($value)) {
                    $this->addError($field, "The {$field} must be a string.");
                }
                break;

            case 'numeric':
                if ($value !== null && !is_numeric($value)) {
                    $this->addError($field, "The {$field} must be a number.");
                }
                break;

            case 'in':
                $allowed = explode(',', $param ?? '');
                if ($value !== null && !in_array($value, $allowed, true)) {
                    $this->addError($field, "The {$field} must be one of: " . implode(', ', $allowed));
                }
                break;

            case 'regex':
                if ($value !== null && $value !== '' && !preg_match($param, (string) $value)) {
                    $this->addError($field, "The {$field} format is invalid.");
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, "The {$field} confirmation does not match.");
                }
                break;

            case 'boolean':
                if ($value !== null && !is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                    $this->addError($field, "The {$field} must be true or false.");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get only the validated, sanitized data
     */
    public function validated(): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $this->data);
    }

    /**
     * Sanitize a single string (prevents XSS)
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate and parse JSON body from request
     */
    public static function parseJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Invalid JSON body provided.', 400);
        }

        return $decoded ?? [];
    }
}
