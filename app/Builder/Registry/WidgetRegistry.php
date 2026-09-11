<?php

namespace App\Builder\Registry;

class WidgetRegistry extends AbstractRegistry
{
    /**
     * Widgets that require a resolved guest to render meaningfully.
     */
    public function requiresGuest(string $type): bool
    {
        return (bool) ($this->get($type)['requiresGuest'] ?? false);
    }

    public function view(string $type): ?string
    {
        return $this->get($type)['view'] ?? null;
    }

    /**
     * Whether $childType may be placed inside $parentType.
     */
    public function canContain(string $parentType, string $childType): bool
    {
        $allowed = $this->get($parentType)['allowedChildren'] ?? false;

        if ($allowed === true) {
            return true;
        }

        if (is_array($allowed)) {
            return in_array($childType, $allowed, true);
        }

        return false;
    }

    /**
     * Widgets that accept children at all — used for dropzone hints.
     */
    public function containers(): array
    {
        return array_keys(array_filter(
            $this->all(),
            fn ($definition) => ! empty($definition['allowedChildren'])
        ));
    }
}
