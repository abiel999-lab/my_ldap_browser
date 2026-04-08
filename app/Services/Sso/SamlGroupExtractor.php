<?php

namespace App\Services\Sso;

class SamlGroupExtractor
{
    public static function extract(array $attributes, string $groupAttribute = 'groups'): array
    {
        $raw = $attributes[$groupAttribute] ?? [];

        if (is_string($raw)) {
            $raw = [$raw];
        }

        if (! is_array($raw)) {
            return [];
        }

        $groups = [];

        array_walk_recursive($raw, function ($value) use (&$groups) {
            if (is_string($value) && trim($value) !== '') {
                $groups[] = trim($value);
            }
        });

        $groups = array_values(array_unique($groups));

        sort($groups);

        return $groups;
    }

    public static function hasAllowedGroup(array $groups, string $allowedGroup): bool
    {
        return in_array($allowedGroup, $groups, true);
    }
}
