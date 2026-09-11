<?php

namespace Tests\Feature\Builder;

use App\Builder\StyleCompiler;
use Tests\TestCase;

class StyleCompilerTest extends TestCase
{
    private function compile(array $nodes, array $theme = []): string
    {
        return (new StyleCompiler)->compile([
            'version' => 2,
            'theme' => $theme,
            'nodes' => $nodes,
            'overlays' => [],
        ]);
    }

    private function node(string $id, array $styles): array
    {
        return ['id' => $id, 'type' => 'section', 'props' => [], 'styles' => $styles, 'children' => []];
    }

    public function test_theme_colours_become_css_variables(): void
    {
        $css = $this->compile([], ['colors' => ['primary' => '#7C3238', 'accent' => '#B8960C']]);

        $this->assertStringContainsString('--n-primary:#7C3238', $css);
        $this->assertStringContainsString('--n-accent:#B8960C', $css);
    }

    public function test_theme_tokens_resolve_in_style_values(): void
    {
        $css = $this->compile(
            [$this->node('a', ['desktop' => ['color' => 'accent']])],
            ['colors' => ['accent' => '#B8960C']]
        );

        $this->assertStringContainsString('color:var(--n-accent)', $css);
    }

    public function test_numeric_styles_get_units(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => [
            'fontSize' => 48, 'borderRadius' => 4, 'maxWidth' => 720,
        ]])]);

        $this->assertStringContainsString('font-size:48px', $css);
        $this->assertStringContainsString('border-radius:4px', $css);
        $this->assertStringContainsString('max-width:720px', $css);
    }

    public function test_padding_box_expands(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => [
            'padding' => ['top' => 80, 'right' => 24, 'bottom' => 80, 'left' => 24],
        ]])]);

        $this->assertStringContainsString('padding:80px 24px 80px 24px', $css);
    }

    public function test_responsive_styles_emit_media_queries(): void
    {
        $css = $this->compile([$this->node('a', [
            'desktop' => ['fontSize' => 48],
            'mobile' => ['fontSize' => 28],
        ])]);

        $this->assertStringContainsString('[data-node-id="a"]{font-size:48px}', $css);
        $this->assertStringContainsString('@media (max-width:640px){[data-node-id="a"]{font-size:28px}}', $css);
    }

    public function test_transform_keys_merge_into_one_declaration(): void
    {
        $css = $this->compile([[
            'id' => 'd', 'type' => 'decoration', 'props' => [], 'children' => [],
            'styles' => ['desktop' => ['rotation' => -12, 'scale' => 150, 'flipH' => true]],
        ]]);

        $this->assertStringContainsString('transform:rotate(-12deg) scale(1.5) scaleX(-1)', $css);
    }

    public function test_decorations_are_absolutely_positioned(): void
    {
        $css = $this->compile([[
            'id' => 'd', 'type' => 'decoration', 'props' => [], 'children' => [],
            'styles' => ['desktop' => ['x' => 20, 'y' => 40]],
        ]]);

        $this->assertStringContainsString('position:absolute', $css);
        $this->assertStringContainsString('left:20px', $css);
        $this->assertStringContainsString('top:40px', $css);
    }

    // ── Security ────────────────────────────────────────────────────────────

    public function test_javascript_colour_is_rejected(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['color' => 'javascript:alert(1)']])]);

        $this->assertStringNotContainsString('javascript', $css);
    }

    public function test_url_colour_is_rejected(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['backgroundColor' => 'url(//evil.test/x.png)']])]);

        $this->assertStringNotContainsString('evil.test', $css);
    }

    public function test_background_image_traversal_is_rejected(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['backgroundImage' => '../../etc/passwd']])]);

        $this->assertStringNotContainsString('passwd', $css);
    }

    public function test_background_image_requires_an_image_extension(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['backgroundImage' => 'weddings/1/evil.php']])]);

        $this->assertStringNotContainsString('evil.php', $css);
    }

    public function test_builtin_background_resolves_to_a_local_asset(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['backgroundImage' => 'builtin:flower']])]);

        $this->assertStringContainsString('/builder/decorations/flower.svg', $css);
    }

    public function test_unknown_style_keys_are_ignored(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => [
            'notARealProperty' => 'evil',
            'position' => 'fixed',
        ]])]);

        $this->assertStringNotContainsString('evil', $css);
        $this->assertStringContainsString('position:fixed', $css);
    }

    public function test_out_of_range_numbers_are_clamped_out(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['fontSize' => 99999]])]);

        $this->assertStringNotContainsString('99999', $css);
    }

    public function test_css_injection_in_an_id_cannot_escape_the_selector(): void
    {
        $css = $this->compile([[
            'id' => 'a"]{color:red}body{',
            'type' => 'section', 'props' => [], 'children' => [],
            'styles' => ['desktop' => ['fontSize' => 20]],
        ]]);

        // The id is stripped to safe characters before being interpolated.
        $this->assertStringNotContainsString('color:red', $css);
        $this->assertStringContainsString('font-size:20px', $css);
    }

    public function test_font_names_reject_quotes_and_semicolons(): void
    {
        $css = $this->compile([], ['typography' => ['headingFont' => "Evil'; } body { display:none }"]]);

        $this->assertStringNotContainsString('display:none', $css);
    }

    public function test_shadow_compiles(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => [
            'boxShadow' => ['x' => 0, 'y' => 8, 'blur' => 24, 'spread' => 0, 'color' => '#00000033'],
        ]])]);

        $this->assertStringContainsString('box-shadow:0px 8px 24px 0px #00000033', $css);
    }

    public function test_opacity_is_percentage_aware(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['opacity' => 70]])]);

        $this->assertStringContainsString('opacity:0.7', $css);
    }

    public function test_visible_false_hides_the_node(): void
    {
        $css = $this->compile([$this->node('a', ['desktop' => ['visible' => false]])]);

        $this->assertStringContainsString('display:none', $css);
    }
}
