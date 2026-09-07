<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\GuestCategory;
use App\Models\GiftMethod;
use App\Models\GuestBookEntry;
use App\Models\Playlist;
use App\Models\Rsvp;
use App\Models\Template;
use App\Models\User;
use App\Models\VisibilityRule;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use App\Models\WeddingEvent;
use App\Models\WeddingSection;
use App\Services\TemplateService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Templates
        $this->seedTemplates();

        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@ngundang.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Demo Client
        $client = Client::create([
            'name' => 'Demo Client',
            'email' => 'client@demo.com',
            'status' => 'active',
        ]);

        $clientAdmin = User::create([
            'name' => 'Client Admin',
            'email' => 'clientadmin@demo.com',
            'password' => Hash::make('password'),
            'client_id' => $client->id,
            'role' => 'client_admin',
            'is_active' => true,
        ]);

        $operator = User::create([
            'name' => 'Check-in Operator',
            'email' => 'operator@demo.com',
            'password' => Hash::make('password'),
            'client_id' => $client->id,
            'role' => 'checkin_operator',
            'is_active' => true,
        ]);

        // Demo Wedding
        $template = Template::where('key', 'minang-elegance')->first();

        $wedding = Wedding::create([
            'client_id'    => $client->id,
            'template_id'  => $template->id,
            'public_id'    => 'INV-DEMO01',
            'short_id'     => 'demo01',
            'slug'         => 'andi-sari',
            'title' => 'The Wedding of Andi & Sari',
            'groom_name' => 'Andi Pratama',
            'bride_name' => 'Sari Dewi',
            'groom_nickname' => 'Andi',
            'bride_nickname' => 'Sari',
            'description' => 'Dengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara pernikahan kami.',
            'quote' => '"Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu istri-istri dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya." — QS. Ar-Rum: 21',
            'date' => '2025-06-15',
            'venue' => 'Gedung Serbaguna Minang Permai',
            'address' => 'Jl. Minang Permai No. 1, Padang, Sumatera Barat',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Sections
        $sections = app(TemplateService::class)->defaultSections();
        foreach ($sections as $i => $key) {
            WeddingSection::create([
                'wedding_id' => $wedding->id,
                'section_key' => $key,
                'is_enabled' => true,
                'sort_order' => $i,
            ]);
        }

        // Domain
        WeddingDomain::create([
            'wedding_id' => $wedding->id,
            'domain' => 'andi-sari.ngundang.com',
            'type' => 'subdomain',
            'is_primary' => true,
            'is_active' => true,
            'verification_status' => 'verified',
            'verified_at' => now(),
            'ssl_status' => 'active',
        ]);

        // Guest Categories
        $categories = [
            ['name' => 'Keluarga', 'color' => '#7C3238'],
            ['name' => 'Teman', 'color' => '#4A6741'],
            ['name' => 'VIP', 'color' => '#B8960C'],
            ['name' => 'Rekan Kerja', 'color' => '#2C4A6E'],
        ];

        foreach ($categories as $cat) {
            GuestCategory::create(array_merge($cat, ['wedding_id' => $wedding->id]));
        }

        // Demo Guests
        $keluarga = GuestCategory::where('wedding_id', $wedding->id)->where('name', 'Keluarga')->first();
        $teman = GuestCategory::where('wedding_id', $wedding->id)->where('name', 'Teman')->first();
        $vip = GuestCategory::where('wedding_id', $wedding->id)->where('name', 'VIP')->first();

        $budi = \App\Models\Guest::create([
            'wedding_id' => $wedding->id,
            'category_id' => $keluarga->id,
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'max_pax' => 2,
        ]);

        $rina = \App\Models\Guest::create([
            'wedding_id' => $wedding->id,
            'category_id' => $teman->id,
            'name' => 'Rina Wati',
            'phone' => '08987654321',
            'max_pax' => 1,
        ]);

        $vipGuest = \App\Models\Guest::create([
            'wedding_id' => $wedding->id,
            'category_id' => $vip->id,
            'name' => 'Pak Direktur',
            'phone' => '08111222333',
            'max_pax' => 4,
        ]);

        // Demo RSVP
        \App\Models\Rsvp::create([
            'guest_id' => $budi->id,
            'wedding_id' => $wedding->id,
            'attendance_status' => 'attending',
            'pax' => 2,
        ]);

        // Demo Guest Book
        \App\Models\GuestBookEntry::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $rina->id,
            'name' => 'Rina Wati',
            'message' => 'Selamat menempuh hidup baru! Semoga menjadi keluarga yang sakinah, mawaddah, warahmah.',
            'status' => 'approved',
        ]);

        \App\Models\GuestBookEntry::create([
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Anonim',
            'message' => 'Bahagia selalu ya!',
            'status' => 'pending',
        ]);

        // Demo Gift Methods
        \App\Models\GiftMethod::create([
            'wedding_id' => $wedding->id,
            'type' => 'bank_transfer',
            'label' => 'BCA',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Andi Pratama',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\GiftMethod::create([
            'wedding_id' => $wedding->id,
            'type' => 'qris',
            'label' => 'QRIS',
            'merchant_name' => 'Andi & Sari Wedding',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Demo Events
        \App\Models\WeddingEvent::create([
            'wedding_id' => $wedding->id,
            'name' => 'Akad Nikah',
            'type' => 'akad',
            'starts_at' => '2025-06-15 08:00:00',
            'ends_at' => '2025-06-15 10:00:00',
            'venue' => 'Masjid Al-Ikhlas',
            'address' => 'Jl. Minang Permai No. 1, Padang',
            'dress_code' => 'Formal - Merah Maroon',
            'is_public' => true,
            'sort_order' => 1,
        ]);

        \App\Models\WeddingEvent::create([
            'wedding_id' => $wedding->id,
            'name' => 'Resepsi',
            'type' => 'reception',
            'starts_at' => '2025-06-15 11:00:00',
            'ends_at' => '2025-06-15 15:00:00',
            'venue' => 'Gedung Serbaguna Minang Permai',
            'address' => 'Jl. Minang Permai No. 1, Padang, Sumatera Barat',
            'dress_code' => 'Formal - Merah Maroon',
            'is_public' => true,
            'sort_order' => 2,
        ]);

        // Demo Playlist
        $playlist = \App\Models\Playlist::create([
            'wedding_id' => $wedding->id,
            'name' => 'Default',
            'is_active' => true,
            'autoplay' => true,
            'loop' => true,
            'shuffle' => false,
            'volume' => 60,
        ]);

        // Demo Visibility Rule: hide gift from Rekan Kerja
        $rekanKerja = \App\Models\GuestCategory::where('wedding_id', $wedding->id)->where('name', 'Rekan Kerja')->first();
        $bcaGift = \App\Models\GiftMethod::where('wedding_id', $wedding->id)->where('label', 'BCA')->first();

        if ($rekanKerja && $bcaGift) {
            \App\Models\VisibilityRule::create([
                'wedding_id'  => $wedding->id,
                'entity_type' => 'gift_method',
                'entity_id'   => $bcaGift->id,
                'scope'       => 'category',
                'scope_id'    => $rekanKerja->id,
                'is_visible'  => false,
            ]);
        }
    }

    private function seedTemplates(): void
    {
        Template::create([
            'key' => 'minang-elegance',
            'name' => 'Minang Elegance',
            'description' => 'Template elegan dengan identitas visual Minangkabau — Rumah Gadang, ukiran tradisional, dan palet Deep Maroon & Gold.',
            'category' => 'cultural',
            'is_active' => true,
            'sort_order' => 1,
            'default_settings' => [
                'terminology' => [
                    'wedding_of' => 'Baralek Gadang',
                    'invitation_title' => 'Walimatul \'Ursy',
                ],
                'palette' => [
                    'primary' => '#7C3238',
                    'secondary' => '#F5F0E8',
                    'accent' => '#B8960C',
                    'dark' => '#2C1810',
                ],
                'fonts' => [
                    'display' => 'Playfair Display',
                    'body' => 'Lato',
                ],
            ],
            'animation_personality' => [
                'opening' => 'ornament_reveal',
                'hero' => 'slow_zoom',
                'couple' => 'elegant_fade',
                'gallery' => 'stagger',
                'sections' => 'fade_up',
                'closing' => 'slow_fade',
            ],
        ]);

        Template::create([
            'key' => 'modern-luxury',
            'name' => 'Modern Luxury',
            'description' => 'Template modern dengan estetika mewah, tipografi kuat, dan komposisi editorial.',
            'category' => 'modern',
            'is_active' => true,
            'sort_order' => 2,
            'default_settings' => [
                'palette' => ['primary' => '#1A1A1A', 'secondary' => '#FAFAFA', 'accent' => '#C9A96E'],
                'fonts'   => ['display' => 'Cormorant Garamond', 'body' => 'Inter'],
            ],
            'animation_personality' => ['sections' => 'fade_up', 'hero' => 'cinematic'],
        ]);

        Template::create([
            'key' => 'floral-romantic',
            'name' => 'Floral Romantic',
            'description' => 'Template romantis dengan nuansa bunga, warna blush, dan tipografi elegan.',
            'category' => 'romantic',
            'is_active' => true,
            'sort_order' => 3,
            'default_settings' => [
                'palette' => ['primary' => '#b5606a', 'secondary' => '#f9f0f0', 'accent' => '#7a9e7e'],
                'fonts'   => ['display' => 'Libre Baskerville', 'body' => 'Open Sans'],
            ],
            'animation_personality' => ['sections' => 'fade_up', 'hero' => 'slow_zoom'],
        ]);

        Template::create([
            'key' => 'islamic-elegant',
            'name' => 'Islamic Elegant',
            'description' => 'Template Islami dengan nuansa hijau, kaligrafi, dan ornamen geometris.',
            'category' => 'religious',
            'is_active' => true,
            'sort_order' => 4,
            'default_settings' => [
                'palette' => ['primary' => '#1a4a2e', 'secondary' => '#fdf8f0', 'accent' => '#c9a84c'],
                'fonts'   => ['display' => 'Lora', 'body' => 'Nunito'],
                'terminology' => ['wedding_of' => 'Walimatul Ursy', 'invitation_title' => 'Undangan Pernikahan'],
            ],
            'animation_personality' => ['sections' => 'fade_up', 'hero' => 'elegant_fade'],
        ]);

        Template::create([
            'key' => 'traditional-nusantara',
            'name' => 'Traditional Nusantara',
            'description' => 'Template tradisional Nusantara dengan motif batik, warna coklat hangat, dan nuansa budaya.',
            'category' => 'cultural',
            'is_active' => true,
            'sort_order' => 5,
            'default_settings' => [
                'palette' => ['primary' => '#4a2c0a', 'secondary' => '#fdf5e4', 'accent' => '#c9a84c'],
                'fonts'   => ['display' => 'Playfair Display', 'body' => 'Lato'],
            ],
            'animation_personality' => ['sections' => 'fade_up', 'hero' => 'slow_zoom'],
        ]);

        Template::create([
            'key' => 'minimalist',
            'name' => 'Minimalist',
            'description' => 'Template minimalis bersih dengan tipografi kuat dan whitespace yang intentional.',
            'category' => 'modern',
            'is_active' => true,
            'sort_order' => 6,
            'default_settings' => [
                'palette' => ['primary' => '#111111', 'secondary' => '#ffffff', 'accent' => '#888888'],
                'fonts'   => ['display' => 'EB Garamond', 'body' => 'DM Sans'],
            ],
            'animation_personality' => ['sections' => 'fade_up', 'hero' => 'fade_up'],
        ]);
    }
}
