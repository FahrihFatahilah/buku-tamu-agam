<?php

namespace App\Builder\Registry;

class AnimationRegistry extends AbstractRegistry
{
    public function category(string $type): ?string
    {
        return $this->get($type)['category'] ?? null;
    }

    /**
     * Animations grouped by trigger, for the inspector's animation picker.
     */
    public function groupedByCategory(): array
    {
        $grouped = [];

        foreach ($this->all() as $type => $definition) {
            $category = $definition['category'] ?? 'other';
            $grouped[$category][] = [
                'type' => $type,
                'name' => $definition['name'] ?? $type,
            ];
        }

        return $grouped;
    }

    public function isScrollAnimation(string $type): bool
    {
        return $this->category($type) === 'scroll';
    }

    public function isEntrance(string $type): bool
    {
        return $this->category($type) === 'entrance';
    }

    public function isContinuous(string $type): bool
    {
        return $this->category($type) === 'continuous';
    }
}
