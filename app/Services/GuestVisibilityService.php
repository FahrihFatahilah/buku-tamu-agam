<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\VisibilityRule;
use App\Models\Wedding;
use Illuminate\Support\Collection;

class GuestVisibilityService
{
    /**
     * Determine which entities are hidden from a guest.
     * Priority: Individual Guest Override > Category Rule > Wedding Default
     *
     * Only entities carrying an explicit rule can ever be hidden — an entity
     * without any rule is visible by default and is never returned here.
     *
     * Data is NEVER sent to browser and hidden with CSS.
     * Server determines what to include in the response.
     */
    public function getHiddenEntityIds(string $entityType, Wedding $wedding, ?Guest $guest): Collection
    {
        $rules = VisibilityRule::where('wedding_id', $wedding->id)
            ->where('entity_type', $entityType)
            ->get();

        if ($rules->isEmpty()) {
            return collect();
        }

        return $rules->pluck('entity_id')->unique()
            ->reject(fn($entityId) => $this->isVisible($entityId, $rules, $guest))
            ->values();
    }

    public function isEntityVisible(string $entityType, int $entityId, Wedding $wedding, ?Guest $guest): bool
    {
        $rules = VisibilityRule::where('wedding_id', $wedding->id)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();

        // No rules = visible by default
        if ($rules->isEmpty()) {
            return true;
        }

        return $this->isVisible($entityId, $rules, $guest);
    }

    private function isVisible(int $entityId, Collection $rules, ?Guest $guest): bool
    {
        $entityRules = $rules->where('entity_id', $entityId);

        // 1. Individual guest override (highest priority)
        if ($guest) {
            $guestRule = $entityRules->where('scope', 'guest')->where('scope_id', $guest->id)->first();
            if ($guestRule) {
                return $guestRule->is_visible;
            }

            // 2. Category rule
            if ($guest->category_id) {
                $categoryRule = $entityRules->where('scope', 'category')->where('scope_id', $guest->category_id)->first();
                if ($categoryRule) {
                    return $categoryRule->is_visible;
                }
            }
        }

        // 3. Wedding default
        $defaultRule = $entityRules->where('scope', 'wedding_default')->where('scope_id', null)->first();
        if ($defaultRule) {
            return $defaultRule->is_visible;
        }

        // No rule = visible
        return true;
    }
}
