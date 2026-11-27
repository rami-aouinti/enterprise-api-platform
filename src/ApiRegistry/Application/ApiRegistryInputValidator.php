<?php

declare(strict_types=1);

namespace App\ApiRegistry\Application;

use stdClass;

final class ApiRegistryInputValidator
{
    private const array ALLOWED_API_FIELDS = ['name', 'description', 'owner', 'tags', 'lifecycle_state'];
    /**
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validateApi(array $input, bool $partial = false): array
    {
        $data = [];
        $errors = [];
        foreach ($input as $field => $_value) {
            if (!in_array($field, self::ALLOWED_API_FIELDS, true)) {
                $errors[$field] = 'This field is not allowed.';
            }
        }
        foreach (['name' => 120, 'owner' => 120] as $field => $maxLength) {
            if (!$partial || array_key_exists($field, $input)) {
                $value = $input[$field] ?? null;
                if (!is_string($value) || trim($value) === '' || $this->length(trim($value)) > $maxLength) {
                    $errors[$field] = sprintf('This field must be a non-empty string of at most %d characters.', $maxLength);
                } else {
                    $data[$field] = trim($value);
                }
            }
        }
        if (!$partial || array_key_exists('description', $input)) {
            $description = $input['description'] ?? '';
            if (!is_string($description) || $this->length(trim($description)) > 2000) {
                $errors['description'] = 'Description must be a string of at most 2000 characters.';
            } else {
                $data['description'] = trim($description);
            }
        }
        if (array_key_exists('tags', $input)) {
            $tags = $input['tags'];
            if (!is_array($tags) || !array_is_list($tags) || count($tags) > 20) {
                $errors['tags'] = 'Tags must be a list containing at most 20 values.';
            } else {
                $normalized = [];
                foreach ($tags as $tag) {
                    if (!is_string($tag)) {
                        $errors['tags'] = 'Each tag must be a string.';
                        break;
                    }
                    $tag = strtolower(trim($tag));
                    if ($tag === '' || $this->length($tag) > 40 || preg_match('/^[a-z0-9][a-z0-9-]*$/', $tag) !== 1) {
                        $errors['tags'] = 'Each tag must use 1 to 40 lowercase letters, numbers or hyphens.';
                        break;
                    }
                    $normalized[] = $tag;
                }
                if (!isset($errors['tags'])) {
                    $data['tags'] = array_values(array_unique($normalized));
                }
            }
        } elseif (!$partial) {
            $data['tags'] = [];
        }
        if (array_key_exists('lifecycle_state', $input)) {
            $state = $input['lifecycle_state'];
            if (!is_string($state) || !in_array($state, ['draft', 'published', 'deprecated'], true)) {
                $errors['lifecycle_state'] = 'Lifecycle state must be draft, published or deprecated.';
            } else {
                $data['lifecycle_state'] = $state;
            }
        } elseif (!$partial) {
            $data['lifecycle_state'] = 'draft';
        }
        if ($partial && $input === []) {
            $errors['_payload'] = 'At least one field must be provided.';
        }
        return ['data' => $data, 'errors' => $errors];
    }
    /** @param array<string, mixed> $input @return array{data: array{version: string, document: array<string, mixed>}, errors: array<string, string>} */
    public function validateVersion(array $input): array
    {
        $errors = [];
        $version = $input['version'] ?? null;
        $document = $input['document'] ?? null;
        foreach ($input as $field => $_value) {
            if (!in_array($field, ['version', 'document'], true)) {
                $errors[$field] = 'This field is not allowed.';
            }
        }
        if (!is_string($version) || preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?$/', $version) !== 1) {
            $errors['version'] = 'Version must use semantic versioning (for example 1.2.0).';
        }
        $document = $this->normalizeObject($document);
        if ($document === null) {
            $errors['document'] = 'An OpenAPI document object is required.';
        } else {
            $openApiVersion = $document['openapi'] ?? null;
            if (!is_string($openApiVersion) || preg_match('/^3\.(0|1)\.\d+$/', $openApiVersion) !== 1) {
                $errors['document'] = 'Only OpenAPI 3.0.x and 3.1.x documents are supported.';
            }
            $info = $document['info'] ?? null;
            if (!is_array($info) || !is_string($info['title'] ?? null) || trim($info['title']) === '') {
                $errors['document'] = 'The OpenAPI document must define info.title.';
            } elseif (is_string($version) && ($info['version'] ?? null) !== $version) {
                $errors['document'] = 'OpenAPI info.version must match the registered version.';
            }
            if (!is_array($document['paths'] ?? null)) {
                $errors['document'] = 'The OpenAPI document must define a paths object.';
            }
        }
        return ['data' => ['version' => is_string($version) ? $version : '', 'document' => $document ?? []], 'errors' => $errors];
    }
    /** @return array<string, mixed>|null */
    private function normalizeObject(mixed $value): ?array
    {
        if ($value instanceof stdClass) {
            $value = get_object_vars($value);
        }
        if (!is_array($value) || array_is_list($value)) {
            return null;
        }
        return $this->normalizeNested($value);
    }
    /**
     * @param array<string, mixed> $value
     * @return array<string, mixed>
     */
    private function normalizeNested(array $value): array
    {
        foreach ($value as $key => $item) {
            if ($item instanceof stdClass) {
                $value[$key] = $this->normalizeNested(get_object_vars($item));
            } elseif (is_array($item) && !array_is_list($item)) {
                $value[$key] = $this->normalizeNested($item);
            } elseif (is_array($item)) {
                $value[$key] = array_map(fn(mixed $nested): mixed => $nested instanceof stdClass ? $this->normalizeNested(get_object_vars($nested)) : (is_array($nested) && !array_is_list($nested) ? $this->normalizeNested($nested) : $nested), $item);
            }
        }
        return $value;
    }
    private function length(string $value): int
    {
        $length = iconv_strlen($value, 'UTF-8');
        return $length === false ? PHP_INT_MAX : $length;
    }
}
