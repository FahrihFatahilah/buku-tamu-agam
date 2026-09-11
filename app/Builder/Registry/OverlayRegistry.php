<?php

namespace App\Builder\Registry;

class OverlayRegistry extends AbstractRegistry
{
    public function view(string $type): ?string
    {
        return $this->get($type)['view'] ?? null;
    }

    public function isAnimated(string $type): bool
    {
        return (bool) ($this->get($type)['animated'] ?? false);
    }

    /**
     * Animated overlays are heavy, so they are skipped on small screens
     * unless the author explicitly opts in. Static ones always render.
     */
    public function animatedTypes(): array
    {
        return array_keys(array_filter($this->all(), fn ($d) => ! empty($d['animated'])));
    }
}
