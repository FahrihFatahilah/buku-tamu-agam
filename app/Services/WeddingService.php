<?php

namespace App\Services;

use App\Models\Wedding;
use App\Models\WeddingSection;
use Illuminate\Support\Str;

class WeddingService
{
    public function __construct(
        private AuditLogService $audit,
        private TemplateService $templateService,
    ) {}

    public function create(int $clientId, array $data): Wedding
    {
        $data['client_id'] = $clientId;
        $data['slug'] = $this->generateSlug($data);
        $data['title'] ??= trim(($data['groom_name'] ?? '').' & '.($data['bride_name'] ?? ''));

        $wedding = Wedding::create($data);

        // Bootstrap default sections from template or defaults
        $this->bootstrapSections($wedding);

        $this->audit->log('wedding.created', 'wedding', $wedding->id, [
            'title' => $wedding->title,
        ], $wedding->id);

        return $wedding;
    }

    public function update(Wedding $wedding, array $data): Wedding
    {
        if (isset($data['groom_name']) || isset($data['bride_name'])) {
            // Regenerate slug suggestion only if explicitly requested
            if (! empty($data['regenerate_slug'])) {
                $data['slug'] = $this->generateSlug(array_merge($wedding->toArray(), $data));
            }
        }

        $wedding->update($data);
        $this->audit->log('wedding.updated', 'wedding', $wedding->id, [], $wedding->id);

        return $wedding;
    }

    public function publish(Wedding $wedding): Wedding
    {
        $wedding->update(['status' => 'published', 'published_at' => now()]);
        $this->audit->log('wedding.published', 'wedding', $wedding->id, [], $wedding->id);

        return $wedding;
    }

    public function unpublish(Wedding $wedding): Wedding
    {
        $wedding->update(['status' => 'draft']);
        $this->audit->log('wedding.unpublished', 'wedding', $wedding->id, [], $wedding->id);

        return $wedding;
    }

    public function archive(Wedding $wedding): Wedding
    {
        $wedding->update(['status' => 'archived']);
        $this->audit->log('wedding.archived', 'wedding', $wedding->id, [], $wedding->id);

        return $wedding;
    }

    private function generateSlug(array $data): string
    {
        $base = Str::slug(
            trim(($data['groom_name'] ?? '').' '.($data['bride_name'] ?? ''))
        );

        if (empty($base)) {
            $base = Str::slug($data['title'] ?? 'wedding');
        }

        $slug = $base;
        $i = 1;

        while (Wedding::where('slug', $slug)->when(isset($data['id']), fn ($q) => $q->where('id', '!=', $data['id']))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function bootstrapSections(Wedding $wedding): void
    {
        $layout = $this->templateService->layoutForTemplate($wedding->template);

        foreach ($layout as $index => $row) {
            WeddingSection::create([
                'wedding_id' => $wedding->id,
                'section_key' => $row['key'],
                'title' => $row['title'] ?? null,
                'is_enabled' => $row['enabled'] ?? true,
                'sort_order' => $index,
                // Carry the template's text defaults so the wedding starts from
                // the template's copy, and can still override per wedding later.
                'settings' => is_array($row['settings'] ?? null) ? $row['settings'] : [],
            ]);
        }
    }
}
