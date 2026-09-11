<?php

namespace App\Builder\Registry;

/**
 * Base registry: a keyed collection of declarative definitions.
 *
 * Concrete registries exist so the builder core never inspects individual
 * widget/decoration/overlay/animation implementations. Adding a new visual is
 * a `register()` call (or a config entry), not a code change in the core.
 */
abstract class AbstractRegistry
{
    /** @var array<string, array> */
    protected array $items = [];

    /** @var array<string, array> */
    protected array $categories = [];

    public function register(string $type, array $definition): static
    {
        $this->items[$type] = $definition + ['type' => $type];

        return $this;
    }

    /**
     * Register many definitions at once, keyed by type.
     */
    public function registerAll(array $definitions): static
    {
        foreach ($definitions as $type => $definition) {
            $this->register((string) $type, $definition);
        }

        return $this;
    }

    public function has(string $type): bool
    {
        return isset($this->items[$type]);
    }

    public function get(string $type): ?array
    {
        return $this->items[$type] ?? null;
    }

    public function forget(string $type): static
    {
        unset($this->items[$type]);

        return $this;
    }

    /** @return array<string, array> */
    public function all(): array
    {
        return $this->items;
    }

    /** @return array<int, string> */
    public function types(): array
    {
        return array_keys($this->items);
    }

    public function categories(array $categories = []): static
    {
        $this->categories = $categories;

        return $this;
    }

    public function categoryLabels(): array
    {
        return $this->categories;
    }

    /**
     * Group definitions by category, ordered by the category metadata.
     * Used to build the editor's element palette.
     */
    public function grouped(): array
    {
        $groups = [];

        foreach ($this->items as $type => $definition) {
            $category = $definition['category'] ?? 'other';

            $groups[$category] ??= [
                'key' => $category,
                'label' => $this->categories[$category]['label'] ?? ucfirst($category),
                'order' => $this->categories[$category]['order'] ?? 99,
                'items' => [],
            ];

            $groups[$category]['items'][] = $this->summarize($type, $definition);
        }

        usort($groups, fn ($a, $b) => $a['order'] <=> $b['order']);

        return array_values($groups);
    }

    /**
     * The shape sent to the editor — no closures, no view paths.
     */
    protected function summarize(string $type, array $definition): array
    {
        return [
            'type' => $type,
            'name' => $definition['name'] ?? $type,
            'category' => $definition['category'] ?? 'other',
            'icon' => $definition['icon'] ?? null,
            'description' => $definition['description'] ?? null,
            'defaultProps' => $definition['defaultProps'] ?? [],
            'defaultStyles' => $definition['defaultStyles'] ?? [],
            'allowedChildren' => $definition['allowedChildren'] ?? false,
            'requiresGuest' => (bool) ($definition['requiresGuest'] ?? false),
            'inspector' => $definition['inspector'] ?? [],
            'animated' => $definition['animated'] ?? null,
            'asset' => $definition['asset'] ?? null,
        ];
    }

    /**
     * Full payload for the editor bootstrap.
     */
    public function toEditorPayload(): array
    {
        return [
            'categories' => array_values($this->categories),
            'groups' => $this->grouped(),
            'items' => array_map(
                fn ($type, $definition) => $this->summarize($type, $definition),
                array_keys($this->items),
                $this->items
            ),
        ];
    }
}
