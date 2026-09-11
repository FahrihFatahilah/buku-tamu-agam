<?php

namespace App\Builder\Registry;

class EffectRegistry extends AbstractRegistry
{
    /**
     * Effects attach to a node and run in the browser. Kept intentionally
     * thin so heavier effects (Lottie, particles, WebGL) can be registered
     * later without changing the builder core.
     */
    public function scriptableTypes(): array
    {
        return $this->types();
    }
}
