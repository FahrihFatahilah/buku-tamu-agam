<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\AuditLogService;
use App\Services\TemplatePreviewService;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    public function __construct(
        private TemplateService $templateService,
        private AuditLogService $audit,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Template::class);

        $templates = Template::withCount('weddings')->orderBy('sort_order')->get();

        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        $this->authorize('create', Template::class);

        return view('admin.templates.edit', [
            'template' => new Template([
                'category' => 'general',
                'is_active' => true,
                'sort_order' => (int) Template::max('sort_order') + 1,
            ]),
            'layout' => $this->templateService->defaultLayout(),
            'palettes' => config('ngundang.palettes'),
            'fonts' => config('ngundang.fonts'),
            'isNew' => true,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Template::class);

        $validated = $this->validated($request);
        $validated['key'] = $this->uniqueKey(($validated['key'] ?? '') ?: $validated['name']);

        $template = Template::create($this->payload($request, $validated));

        $this->templateService->forgetCache();
        $this->audit->log('template.created', 'template', $template->id, ['key' => $template->key]);

        return redirect()->route('admin.templates.edit', $template)
            ->with('success', "Template \"{$template->name}\" berhasil dibuat.");
    }

    public function edit(Template $template)
    {
        $this->authorize('update', $template);

        return view('admin.templates.edit', [
            'template' => $template,
            'layout' => $this->templateService->layoutForTemplate($template),
            'palettes' => config('ngundang.palettes'),
            'fonts' => config('ngundang.fonts'),
            'isNew' => false,
        ]);
    }

    /**
     * Live preview for the builder canvas. Renders the template against sample
     * content using the editor's *unsaved* settings, so the canvas updates
     * without writing a draft to the database.
     */
    public function preview(Request $request, TemplatePreviewService $previewService)
    {
        $this->authorize('viewAny', Template::class);

        $palettes = array_keys(config('ngundang.palettes', []));
        $displayFonts = config('ngundang.fonts.display', []);
        $bodyFonts = config('ngundang.fonts.body', []);

        $palette = $request->string('palette')->toString();
        $palette = in_array($palette, $palettes, true) ? $palette : ($palettes[0] ?? 'minimal');

        $fontDisplay = $request->string('font_display')->toString();
        $fontDisplay = in_array($fontDisplay, $displayFonts, true) ? $fontDisplay : ($displayFonts[0] ?? 'Playfair Display');

        $fontBody = $request->string('font_body')->toString();
        $fontBody = in_array($fontBody, $bodyFonts, true) ? $fontBody : ($bodyFonts[0] ?? 'Lato');

        $order = array_values(array_filter(explode(',', $request->string('order')->toString())));
        $disabled = array_values(array_filter(explode(',', $request->string('off')->toString())));

        // Only preview an existing template's own design; a new template previews
        // the generic default layout.
        $template = $request->integer('template')
            ? Template::find($request->integer('template'))
            : null;

        $data = $previewService->build($palette, $fontDisplay, $fontBody, $order, $disabled, $template);

        return response()
            ->view($this->templateService->layoutView($data['templateKey']), $data)
            ->header('X-Robots-Tag', 'noindex');
    }

    public function update(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $validated = $this->validated($request, $template);

        if (filled($validated['key'] ?? null) && $validated['key'] !== $template->key) {
            $validated['key'] = $this->uniqueKey($validated['key'], $template->id);
        } else {
            unset($validated['key']);
        }

        $template->update($this->payload($request, $validated, $template));

        $this->templateService->forgetCache();
        $this->audit->log('template.updated', 'template', $template->id, ['key' => $template->key]);

        return back()->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(Template $template)
    {
        $this->authorize('delete', $template);

        $key = $template->key;
        $template->delete();

        $this->templateService->forgetCache();
        $this->audit->log('template.deleted', 'template', $template->id, ['key' => $key]);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private function validated(Request $request, ?Template $template = null): array
    {
        $palettes = array_keys(config('ngundang.palettes', []));
        $display = config('ngundang.fonts.display', []);
        $body = config('ngundang.fonts.body', []);

        return $request->validate([
            'name' => 'required|string|max:100',
            'key' => [
                'nullable', 'string', 'max:60', 'alpha_dash',
                Rule::unique('templates', 'key')->ignore($template?->id),
            ],
            'description' => 'nullable|string|max:500',
            'category' => 'required|in:general,cultural,modern,romantic,religious',
            'palette' => ['required', 'string', Rule::in($palettes)],
            'font_display' => ['required', 'string', Rule::in($display)],
            'font_body' => ['required', 'string', Rule::in($body)],
            'is_active' => 'nullable',
            'sort_order' => 'nullable|integer|min:0',
            'order' => 'nullable|string',
            'enabled' => 'nullable|array',
            'title' => 'nullable|array',
        ]);
    }

    private function payload(Request $request, array $validated, ?Template $template = null): array
    {
        $palettes = config('ngundang.palettes', []);
        $palette = $palettes[$validated['palette']] ?? [];
        unset($palette['label']);

        $settings = $template?->default_settings ?? [];
        $settings['palette'] = $palette;
        $settings['fonts'] = [
            'display' => $validated['font_display'],
            'body' => $validated['font_body'],
        ];

        $payload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
            'default_settings' => $settings,
            'default_sections' => $this->sectionsFromRequest($request),
            'animation_personality' => $template?->animation_personality ?? ['sections' => 'fade_up'],
        ];

        if (isset($validated['key'])) {
            $payload['key'] = $validated['key'];
        }

        return $payload;
    }

    /**
     * Turn the builder's drag order + per-section controls into a stored layout.
     * Unknown or duplicated keys are discarded and any canonical section the
     * payload omits is appended, so a template always covers every section.
     */
    private function sectionsFromRequest(Request $request): array
    {
        $known = array_keys(config('ngundang.sections', []));

        $order = $request->input('order');
        if (is_string($order)) {
            $order = json_decode($order, true);
        }
        if (! is_array($order)) {
            $order = [];
        }

        $ordered = [];
        foreach ($order as $key) {
            if (is_string($key) && in_array($key, $known, true) && ! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }
        foreach ($known as $key) {
            if (! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }

        $enabled = (array) $request->input('enabled', []);
        $titles = (array) $request->input('title', []);

        return array_map(fn ($key) => [
            'key' => $key,
            'enabled' => ! empty($enabled[$key]),
            'title' => ! empty($titles[$key]) ? trim((string) $titles[$key]) : null,
        ], $ordered);
    }

    private function uniqueKey(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'template';
        $key = $base;
        $i = 2;

        while (Template::where('key', $key)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $key = "{$base}-{$i}";
            $i++;
        }

        return $key;
    }
}
