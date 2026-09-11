<?php

namespace App\Providers;

use App\Builder\Registry\AnimationRegistry;
use App\Builder\Registry\DecorationRegistry;
use App\Builder\Registry\EffectRegistry;
use App\Builder\Registry\OverlayRegistry;
use App\Builder\Registry\WidgetRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Loads the built-in builder definitions from config and registers the five
 * registries as singletons.
 *
 * To add a widget/decoration/overlay/animation/effect later, add an entry to
 * config/builder.php (plus a Blade view for it) or call register() from any
 * other service provider — the builder core needs no change either way.
 */
class BuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WidgetRegistry::class, function () {
            return (new WidgetRegistry)
                ->categories(config('builder.categories', []))
                ->registerAll(config('builder.widgets', []));
        });

        $this->app->singleton(DecorationRegistry::class, function () {
            $registry = new DecorationRegistry;
            $registry->categories([config('builder.categories.decoration', ['label' => 'Dekorasi', 'order' => 4])]);

            foreach (config('builder.decorations', []) as $type => $definition) {
                $registry->register($type, [
                    'name' => $definition['name'] ?? $type,
                    'category' => 'decoration',
                    'icon' => 'decoration',
                    'description' => $definition['description'] ?? null,
                    'asset' => $definition['asset'] ?? $type,
                    'defaultProps' => $definition['defaultProps'] ?? [],
                    'defaultStyles' => $definition['defaultStyles'] ?? ['desktop' => []],
                    'animated' => $definition['animated'] ?? false,
                ]);
            }

            return $registry;
        });

        $this->app->singleton(OverlayRegistry::class, function () {
            $registry = new OverlayRegistry;
            $registry->categories([config('builder.categories.overlay', ['label' => 'Efek', 'order' => 5])]);

            foreach (config('builder.overlays', []) as $type => $definition) {
                $registry->register($type, $definition + ['category' => 'overlay']);
            }

            return $registry;
        });

        $this->app->singleton(AnimationRegistry::class, function () {
            return (new AnimationRegistry)
                ->categories(config('builder.animation_categories', []))
                ->registerAll(config('builder.animations', []));
        });

        $this->app->singleton(EffectRegistry::class, function () {
            return (new EffectRegistry)
                ->categories([['effect' => ['label' => 'Effect', 'order' => 6]]])
                ->registerAll(config('builder.effects', []));
        });
    }

    public function boot(): void
    {
        //
    }
}
