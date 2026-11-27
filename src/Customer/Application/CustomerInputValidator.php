<?php

declare(strict_types=1);

namespace App\Customer\Application;

final class CustomerInputValidator
{
    private const array ALLOWED_FIELDS = ['first_name', 'last_name', 'email', 'phone', 'status'];

    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input, bool $partial = false): array
    {
        $data = [];
        $errors = [];

        foreach ($input as $field => $value) {
            if (!in_array($field, self::ALLOWED_FIELDS, true)) {
                $errors[$field] = 'This field is not allowed.';
            }
        }

        foreach (['first_name', 'last_name'] as $field) {
            if (!$partial || array_key_exists($field, $input)) {
                $value = $input[$field] ?? null;
                if (!is_string($value) || trim($value) === '' || strlen(trim($value)) > 100) {
                    $errors[$field] = 'This field must be a non-empty string of at most 100 characters.';
                } else {
                    $data[$field] = trim($value);
                }
            }
        }

        if (!$partial || array_key_exists('email', $input)) {
            $email = $input['email'] ?? null;
            if (!is_string($email) || strlen(trim($email)) > 254 || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'A valid email address of at most 254 characters is required.';
            } else {
                $data['email'] = strtolower(trim($email));
            }
        }

        if (array_key_exists('phone', $input)) {
            $phone = $input['phone'];
            if ($phone !== null && (!is_string($phone) || strlen(trim($phone)) > 30)) {
                $errors['phone'] = 'This field must be a string of at most 30 characters or null.';
            } else {
                $data['phone'] = is_string($phone) ? trim($phone) : null;
            }
        } elseif (!$partial) {
            $data['phone'] = null;
        }

        if (array_key_exists('status', $input)) {
            $status = $input['status'];
            if (!is_string($status) || !in_array($status, ['active', 'inactive'], true)) {
                $errors['status'] = 'Status must be either "active" or "inactive".';
            } else {
                $data['status'] = $status;
            }
        } elseif (!$partial) {
            $data['status'] = 'active';
        }

        if ($partial && $input === []) {
            $errors['_payload'] = 'At least one field must be provided.';
        }

        return ['data' => $data, 'errors' => $errors];
    }
}
