<?php

namespace App\Builder\Registry;

class DecorationRegistry extends AbstractRegistry
{
    /**
     * Decorations are free-form objects, so they all share the positioning
     * style fields and the `decoration` widget type in the document.
     */
    public function toEditorPayload(): array
    {
        return parent::toEditorPayload() + [
            'propFields' => $this->propFields(),
            'styleFields' => $this->styleFields(),
        ];
    }

    /**
     * Content fields shared by every decoration.
     */
    public function propFields(): array
    {
        return [
            ['type' => 'asset', 'name' => 'asset', 'label' => 'Aset dekorasi'],
            ['type' => 'color', 'name' => 'color', 'label' => 'Warna'],
            ['type' => 'text', 'name' => 'alt', 'label' => 'Teks alternatif'],
        ];
    }

    /**
     * Shared inspector fields for positioning a decoration.
     */
    public function styleFields(): array
    {
        return [
            ['type' => 'number', 'name' => 'x', 'label' => 'Posisi X', 'min' => -500, 'max' => 2000, 'unit' => 'px'],
            ['type' => 'number', 'name' => 'y', 'label' => 'Posisi Y', 'min' => -500, 'max' => 4000, 'unit' => 'px'],
            ['type' => 'number', 'name' => 'width', 'label' => 'Lebar', 'min' => 8, 'max' => 2000, 'unit' => 'px'],
            ['type' => 'number', 'name' => 'height', 'label' => 'Tinggi', 'min' => 0, 'max' => 2000, 'unit' => 'px'],
            ['type' => 'number', 'name' => 'rotation', 'label' => 'Rotasi', 'min' => -180, 'max' => 180, 'unit' => 'deg'],
            ['type' => 'slider', 'name' => 'scale', 'label' => 'Skala', 'min' => 10, 'max' => 300, 'step' => 1],
            ['type' => 'slider', 'name' => 'opacity', 'label' => 'Opacity', 'min' => 0, 'max' => 100, 'step' => 1],
            ['type' => 'number', 'name' => 'zIndex', 'label' => 'Z-index', 'min' => -10, 'max' => 999, 'unit' => ''],
            ['type' => 'number', 'name' => 'blur', 'label' => 'Blur', 'min' => 0, 'max' => 40, 'unit' => 'px'],
            ['type' => 'switch', 'name' => 'flipH', 'label' => 'Balik horizontal'],
            ['type' => 'switch', 'name' => 'flipV', 'label' => 'Balik vertikal'],
            ['type' => 'select', 'name' => 'blendMode', 'label' => 'Blend mode', 'options' => [
                ['value' => 'normal', 'label' => 'Normal'],
                ['value' => 'multiply', 'label' => 'Multiply'],
                ['value' => 'screen', 'label' => 'Screen'],
                ['value' => 'overlay', 'label' => 'Overlay'],
                ['value' => 'soft-light', 'label' => 'Soft Light'],
                ['value' => 'luminosity', 'label' => 'Luminosity'],
            ]],
            ['type' => 'switch', 'name' => 'visible', 'label' => 'Tampilkan'],
        ];
    }
}
