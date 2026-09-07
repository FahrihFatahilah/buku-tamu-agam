# NGUNDANG — FINAL MASTER PROMPT

Anda adalah **Senior Software Architect, Senior Laravel Engineer, Senior UI/UX Product Designer, dan DevOps Engineer**.

Bangun aplikasi platform undangan pernikahan bernama **Ngundang** yang production-ready, reusable, scalable, secure, multi-tenant, dan dapat digunakan untuk banyak client/wedding dari **satu Laravel codebase**.

Jangan membuat prototype/mainan/demo sederhana. Bangun fondasi aplikasi yang benar-benar dapat dikembangkan menjadi produk SaaS.

---

# 1. PRODUCT VISION

Ngundang adalah platform undangan pernikahan digital multi-client.

Satu aplikasi Laravel harus dapat melayani:

* banyak client
* banyak wedding/invitation
* banyak guest
* banyak template
* banyak domain/subdomain
* banyak konfigurasi invitation
* shared deployment
* optional dedicated deployment untuk client premium/enterprise

Arsitektur utama:

```text
Super Admin
    ↓
Client
    ↓
Wedding / Invitation
    ↓
Guest
    ↓
Category
    ↓
Template
    ↓
Content / Sections
    ↓
Visibility Rules
```

Gunakan prinsip:

* SOLID
* DRY
* KISS
* Separation of Concerns
* Secure by Default
* Multi-tenant by Default
* Reusable Architecture
* Configuration over Hardcoding
* Service Layer
* Policies / Gates
* Form Requests
* Events / Listeners
* Jobs / Queues
* Audit Logs
* Testable Architecture

Jangan membuat satu Laravel project terpisah untuk setiap wedding.

---

# 2. TECHNOLOGY STACK

Gunakan:

* Laravel latest stable
* PHP versi yang kompatibel dengan Laravel terbaru
* MySQL
* Blade
* Tailwind CSS
* shadcn/ui
* Alpine.js atau vanilla JavaScript
* Vite
* Laravel Authentication
* Laravel Storage
* QR Code package yang mature
* PHPUnit/Pest
* Docker sebagai deployment option
* Traefik sebagai reverse proxy untuk deployment platform
* Cloudflare untuk DNS/CDN/domain management bila digunakan

Aplikasi **harus tetap dapat berjalan pada shared hosting PHP + MySQL** tanpa menjadikan Docker, Traefik, Redis, atau Kubernetes sebagai dependency wajib.

Docker dan Traefik adalah deployment option, bukan dependency inti aplikasi.

---

# 3. MULTI-TENANCY

Gunakan satu Laravel application untuk seluruh tenant.

Hierarchy:

```text
Super Admin
    ├── Client A
    │     ├── Wedding A1
    │     └── Wedding A2
    │
    ├── Client B
    │     └── Wedding B1
    │
    └── Client C
          └── Wedding C1
```

Setiap request harus memiliki tenant/wedding context yang jelas.

Implementasikan resolver/service seperti:

```text
TenantResolver
WeddingContext
DomainResolver
InvitationResolver
```

Jangan mengandalkan developer discipline saja untuk tenant isolation.

Gunakan:

* Policies
* Gates
* Middleware
* Query scopes
* Explicit wedding/client ownership validation
* Authorization pada setiap mutation

Wedding A **tidak boleh** membaca atau memodifikasi data Wedding B.

---

# 4. USER ROLES

Minimal:

## Super Admin

Dapat:

* manage semua client
* manage semua wedding
* manage templates
* manage users
* manage domains
* manage deployment
* melihat audit logs
* system settings

## Client / Wedding Admin

Hanya dapat mengelola wedding yang dimilikinya:

* invitation
* couple
* event
* sections
* template
* appearance
* animation
* media
* music
* guests
* categories
* RSVP
* guest book
* gift
* visibility rules
* SEO
* domain

## Check-in Operator

Hanya dapat:

* mencari guest
* scan QR
* melihat guest status
* check-in
* update actual pax sesuai permission

Tidak boleh mengakses:

* financial/gift configuration
* global settings
* template management
* deployment
* client management

---

# 5. INVITATION DATA MODEL

Wedding/invitation minimal memiliki:

```text
id
client_id
public_id
slug
title
couple_name
groom_name
bride_name
groom_nickname
bride_nickname
description
quote
date
venue
address
latitude
longitude
maps_url
status
published_at
seo_title
seo_description
og_image
favicon
created_at
updated_at
```

Tambahkan:

```text
public_id
```

sebagai identifier publik.

---

# 6. PUBLIC INVITATION ID

Jangan pernah menggunakan auto-increment database ID sebagai public identifier.

Gunakan:

* ULID
* UUID
* atau random high-entropy public identifier

Contoh:

```text
INV-8F3K2A
```

`public_id` harus:

* unique
* stable
* tidak mudah ditebak
* tidak berubah ketika slug berubah

Database:

```text
public_id UNIQUE
```

---

# 7. PUBLIC INVITATION URL

URL utama wajib:

```text
/{invitationId}/{invitationSlug}
```

Contoh:

```text
https://ngundang.com/INV-8F3K2A/bagas-rani
```

Subdomain:

```text
https://bagasrani.ngundang.com/INV-8F3K2A/bagas-rani
```

Custom domain:

```text
https://bagasrani.com/INV-8F3K2A/bagas-rani
```

Guest personal URL:

```text
/{invitationId}/{invitationSlug}/u/{secureToken}
```

Contoh:

```text
https://ngundang.com/INV-8F3K2A/bagas-rani/u/X9K82...
```

---

# 8. INVITATION SLUG

Slug harus:

* readable
* SEO-friendly
* berasal dari nama invitation/couple
* unique sesuai kebutuhan routing
* dapat diubah

Contoh:

```text
bagas-rani
bagas-rani-wedding
bagas-rani-baralek
```

Jika slug berubah:

```text
INV-8F3K2A
```

tetap sama.

Old slug harus melakukan canonical redirect menuju slug terbaru.

Contoh:

```text
/INV-8F3K2A/bagas-rani
```

redirect:

```text
/INV-8F3K2A/bagas-rani-wedding
```

Jangan membuat `invitationId` berubah ketika slug berubah.

---

# 9. DOMAIN RESOLUTION

Request flow:

```text
Internet
    ↓
Cloudflare
    ↓
Traefik
    ↓
Laravel
    ↓
DomainResolver
    ↓
InvitationResolver
    ↓
Wedding
```

Laravel harus memvalidasi:

1. host/domain
2. invitation public_id
3. invitation slug
4. domain ownership/mapping
5. wedding status
6. published status

Jangan hanya lookup berdasarkan `public_id`.

Contoh:

```text
bagasrani.ngundang.com/INV-B/andi-sari
```

Jika domain `bagasrani.ngundang.com` milik Wedding A, request tersebut **tidak boleh** menampilkan Wedding B hanya karena `INV-B` valid.

Domain dan invitation harus konsisten.

---

# 10. CLOUDFLARE WILDCARD DNS

Main domain contoh:

```text
ngundang.com
```

Gunakan wildcard DNS:

```text
*.ngundang.com
```

yang diarahkan ke platform/server.

Dengan wildcard DNS:

```text
bagasrani.ngundang.com
andi-sari.ngundang.com
doni-putri.ngundang.com
```

tidak perlu membuat DNS record Cloudflare baru satu per satu.

Saat client membuat wedding:

Laravel cukup membuat domain mapping di database:

```text
bagasrani.ngundang.com
```

Tidak perlu memanggil Cloudflare API untuk setiap subdomain jika wildcard DNS sudah aktif.

---

# 11. DOMAIN TABLE

Buat table seperti:

```text
wedding_domains
```

Minimal:

```text
id
wedding_id
domain
type
is_primary
is_active
verification_status
ssl_status
timestamps
```

Type:

```text
subdomain
custom
```

Custom domain harus memiliki ownership/DNS verification.

Jangan mengasumsikan aplikasi memiliki kontrol DNS custom domain milik user.

---

# 12. CLOUDFLARE SERVICE

Buat abstraction:

```text
CloudflareService
```

Jika dibutuhkan untuk:

* DNS
* domain verification
* custom domain
* cache purge
* zone management

API credential:

* hanya server-side
* jangan expose ke browser
* gunakan least privilege
* jangan log secret

Cloudflare API bukan dependency wajib untuk setiap invitation.

---

# 13. TRAEFIK ARCHITECTURE

Gunakan **satu shared Traefik** untuk shared deployment.

Jangan membuat:

```text
1 wedding = 1 Traefik
```

Jangan membuat:

```text
1 client = 1 Traefik
```

Shared architecture:

```text
Internet
   ↓
Cloudflare
   ↓
Traefik
   ↓
Laravel Application
   ↓
MySQL
```

Traefik hanya menangani routing ke Laravel.

Laravel menangani:

```text
DomainResolver
InvitationResolver
TenantResolver
```

---

# 14. DEPLOYMENT ARCHITECTURE

Support:

```text
shared
dedicated
enterprise
```

Default:

```text
shared
```

Konsep penting:

```text
Container = Deployment Unit
Tenant/Client/Wedding = Data Boundary
Deployment = Runtime Environment
```

Membuat client baru pada shared deployment **tidak boleh** membuat:

* Laravel container baru
* MySQL container baru
* Traefik container baru

Dedicated deployment hanya digunakan jika memang diperlukan.

---

# 15. DEDICATED DEPLOYMENT

Dedicated deployment dapat memiliki:

```text
Laravel
MySQL
Storage
Runtime
Domain
```

terpisah.

Gunakan deployment state machine:

```text
pending
provisioning
deploying
healthy
failed
suspended
terminated
```

Provisioning harus:

* idempotent
* retryable
* auditable
* secure

Jangan menjalankan arbitrary Docker/shell command langsung dari browser.

Gunakan:

```text
DeploymentService
DeploymentProvisioner
Queue/Job
```

Jangan expose Docker socket ke Laravel kecuali benar-benar diperlukan dan sudah diamankan.

---

# 16. TEMPLATE ENGINE

Template minimal:

```text
Minang Elegance
Modern Luxury
Floral Romantic
Islamic Elegant
Traditional Nusantara
Minimalist
```

Gunakan registry/resolver:

```text
TemplateService
TemplateRenderer
```

Jangan:

```php
if ($template === 'minang') ...
elseif ($template === 'modern') ...
elseif ...
```

di banyak tempat.

Template harus terpisah dari wedding data.

Switch template:

```text
Template A
↓
Template B
```

tidak boleh menghapus wedding data.

---

# 17. MINANG ELEGANCE TEMPLATE

Buat template:

```text
Minang Elegance
```

dengan visual identity:

* Minangkabau
* Suku Minang
* Rumah Gadang
* gonjong
* ukiran Minang
* pola geometris tradisional
* songket-inspired texture
* ornamental border
* divider
* silhouette Rumah Gadang

Palette:

```text
Deep Maroon / Red
Cream
Muted Gold
Dark Brown
```

Gaya:

* premium
* elegant
* modern
* traditional
* refined

Jangan membuatnya terlalu ramai.

Ornament hanya digunakan sebagai:

* framing
* divider
* accent
* silhouette
* opening transition
* subtle background texture

Bukan di setiap bagian halaman.

Gunakan terminology yang configurable:

```text
The Wedding of
Baralek Gadang
Walimatul 'Ursy
```

sesuai konfigurasi template/client.

Hormati konteks budaya Minangkabau dan hindari penggunaan ornamen tradisional secara berlebihan atau tidak relevan.

---

# 18. SECTIONS

Minimal:

```text
Opening
Hero
Couple
Quote
Countdown
Event
Venue
Maps
Love Story
Gallery
Video
RSVP
Guest Book
Gift
Timeline
Closing
```

Section harus dapat:

* enable
* disable
* reorder
* customize title
* customize content
* customize settings

Table:

```text
wedding_sections

id
wedding_id
section_key
title
is_enabled
sort_order
settings
timestamps
```

`settings` dapat menggunakan JSON untuk configuration yang memang fleksibel.

Relational data tetap relational.

---

# 19. ANIMATION ENGINE

Buat reusable animation system.

Preset:

```text
fade_up
fade_down
fade_left
fade_right
zoom_in
zoom_out
scale_in
blur_reveal
slide_reveal
stagger
parallax
ornament_reveal
curtain
cinematic
```

Configuration:

```text
preset
duration
delay
speed
direction
intensity
stagger
```

Global presets:

```text
Elegant
Cinematic
Romantic
Traditional
Minimal
None
```

Template dapat menentukan default animation personality.

Client dapat override jika diizinkan.

---

# 20. MINANG ANIMATION PERSONALITY

Default:

```text
Opening        → ornament_reveal
Hero           → slow_zoom
Rumah Gadang   → subtle_parallax
Couple         → elegant_fade
Gallery        → stagger
Sections       → ornament_reveal
Closing        → slow_fade
```

Animation harus:

* CSS-first
* transform/opacity
* IntersectionObserver bila perlu
* lightweight
* mobile-friendly
* tidak menghambat first paint

Wajib support:

```text
prefers-reduced-motion
```

Jangan menggunakan:

* bounce berlebihan
* spin
* infinite floating
* excessive parallax
* animasi setiap elemen
* heavy JS animation

---

# 21. HARD UI/UX RULE — ANTI AI SLOP

Ini adalah **HARD REQUIREMENT**.

Jangan membuat UI yang terlihat seperti hasil generic AI-generated SaaS template.

Aplikasi harus terlihat seperti produk yang dirancang oleh **Senior Product Designer**.

Prinsip utama:

> Less but Better.

> Every visual element must have a reason to exist.

Prioritas visual:

```text
Typography
Whitespace
Hierarchy
Grid
Alignment
Spacing
Content
Photography
Subtle Ornament
```

---

# 22. FORBIDDEN GENERIC AI-SLOP DESIGN

Hindari:

* excessive glassmorphism
* gradient background berlebihan
* colorful blobs
* neon glow
* giant gradient text
* terlalu banyak rounded cards
* terlalu banyak shadow
* rounded-full untuk hampir semua elemen
* rounded-3xl di mana-mana
* badge/pill berlebihan
* icon berlebihan
* emoji sebagai dekorasi utama
* decorative SVG random
* nested cards
* card di dalam card
* card di dalam card lagi
* setiap section dibungkus Card
* setiap statistik menjadi colorful card
* dashboard dengan 10+ colorful metric cards
* random illustrations
* meaningless gradients
* excessive floating elements
* excessive animation
* UI yang terlihat seperti template AI SaaS generik

---

# 23. SHADCN/UI RULE

shadcn/ui boleh digunakan sebagai **technical foundation**.

Namun:

> shadcn/ui bukan visual identity Ngundang.

Jangan menggunakan default shadcn styling secara mentah lalu menganggap UI selesai.

Gunakan komponen seperti:

```text
Button
Input
Textarea
Select
Checkbox
Switch
Dialog
Sheet
Tabs
Card
Table
Badge
Dropdown
Toast
Alert
Calendar
Form
Command
Pagination
```

tetapi visual system harus dikustomisasi.

**Component-first tidak berarti card-first.**

---

# 24. ADMIN UI DESIGN

Admin harus terasa seperti:

```text
Professional Product Interface
```

bukan:

```text
Generic SaaS Dashboard Template
```

Gunakan:

* whitespace yang baik
* typography kuat
* hierarchy jelas
* clean navigation
* clean table
* subtle dividers
* restrained borders
* meaningful status
* consistent spacing
* consistent grid
* predictable interaction

Stats harus data-oriented.

Jangan membuat semua statistik menjadi card warna-warni.

---

# 25. ADMIN TABLE

Table harus:

* clean
* readable
* compact tetapi tidak sempit
* subtle divider
* clear hierarchy
* pagination
* filtering
* search
* meaningful status

Badge hanya digunakan jika memang membantu membaca status.

Jangan membuat setiap data menjadi badge/pill.

---

# 26. ADMIN FORM

Form:

```text
Label
Input
Helper text
Validation
Error message
```

Jangan membungkus setiap input ke dalam Card.

Gunakan grouping berdasarkan konteks.

Contoh:

```text
Wedding Information

Couple Name
Wedding Date
Venue
Address
```

bukan:

```text
Card
  Card
    Input
Card
  Card
    Input
```

---

# 27. BUTTON HIERARCHY

Gunakan:

```text
Primary
Secondary
Tertiary
Destructive
```

Tidak semua button harus:

* filled
* pill
* rounded-full
* colorful

Gunakan visual hierarchy yang jelas.

---

# 28. ICONS

Gunakan icon secara minimal.

Rules:

* satu icon library yang konsisten
* icon harus meaningful
* jangan menambahkan icon hanya agar UI terlihat ramai
* jangan mengganti semua text dengan icon
* emoji bukan primary UI decoration

---

# 29. COLOR SYSTEM

Admin:

* neutral base
* satu primary accent
* semantic colors seperlunya

Wedding template:

Minang:

```text
Deep Maroon
Cream
Muted Gold
Dark Brown
```

Jangan menggunakan banyak warna hanya untuk membuat UI terlihat “premium”.

---

# 30. TYPOGRAPHY

Typography harus menjadi salah satu focal point.

Gunakan maksimal:

```text
1 primary font
+
1 complementary/display font
```

untuk wedding template.

Hierarchy wajib jelas:

```text
Display
H1
H2
H3
Body
Caption
```

Perhatikan:

* font size
* line height
* letter spacing
* vertical rhythm
* readability

Jika desain tidak terlihat premium tanpa dekorasi, perbaiki typography terlebih dahulu.

---

# 31. WHITESPACE

Whitespace harus intentional.

Jangan:

* terlalu padat
* terlalu kosong tanpa alasan
* random spacing

Gunakan spacing system konsisten.

---

# 32. PUBLIC INVITATION DESIGN

Public invitation bukan dashboard.

Jangan membuat halaman wedding seperti:

```text
Card
Card
Card
Card
Card
```

Public invitation harus terasa seperti:

```text
Editorial Wedding Website
```

Gunakan:

* strong composition
* typography
* photography
* whitespace
* elegant section transitions
* subtle ornament
* clear content hierarchy

---

# 33. HERO DESIGN

Hero harus memiliki:

* strong composition
* couple photography
* typography hierarchy
* wedding identity
* subtle visual treatment

Jangan menggunakan:

* random blobs
* glow berlebihan
* gradient berlebihan
* decorative noise
* excessive animation

Photography menjadi salah satu focal point utama.

Gunakan editorial crop yang baik.

---

# 34. MINANG PUBLIC DESIGN

Minang Elegance harus terasa:

```text
Minangkabau
Elegant
Modern
Premium
Cultural
Restrained
```

Jangan:

```text
Traditional overload
```

Ornament digunakan secara strategis.

Rumah Gadang tidak perlu muncul di setiap section.

---

# 35. RESPONSIVE DESIGN

Mobile-first.

Jangan hanya mengecilkan desktop.

Design untuk:

```text
Mobile
Tablet
Desktop
Large Desktop
```

Prioritas utama adalah pengalaman mobile karena mayoritas undangan akan dibuka dari smartphone.

---

# 36. ACCESSIBILITY

Wajib:

* semantic HTML
* keyboard navigation
* visible focus state
* sufficient contrast
* accessible labels
* ARIA bila diperlukan
* readable typography
* proper form validation
* reduced motion
* screen-reader friendly interaction

---

# 37. MEDIA MANAGEMENT

Support:

* hero image
* couple image
* family image
* gallery
* prewedding
* video

Features:

* upload
* replace
* delete
* reorder
* alt text
* preview

Storage metadata:

```text
wedding_id
section
file_path
original_name
mime_type
file_size
width
height
alt_text
sort_order
metadata
timestamps
```

Gunakan Laravel Storage abstraction.

Production sebaiknya support:

* S3-compatible object storage
* CDN
* image optimization

Upload harus memvalidasi:

* MIME
* file size
* dimensions
* extension

Jangan trust extension saja.

Image processing dapat dilakukan melalui queue.

---

# 38. MUSIC / PLAYLIST

Support:

* MP3/audio upload
* active song
* replace
* delete
* autoplay
* loop
* volume
* start position

Karena browser dapat memblokir autoplay:

Jika autoplay gagal, tampilkan control yang elegan:

```text
Putar Musik
```

Playlist support:

* add
* delete
* reorder
* next
* previous
* shuffle
* repeat

Tables:

```text
playlists
playlist_items
```

Jangan otomatis mengambil copyrighted music dari internet.

Client bertanggung jawab atas hak/lisensi musik yang digunakan.

---

# 39. GUEST MANAGEMENT

Guest:

```text
id
wedding_id
category_id
name
phone
email
invitation_token
max_pax
notes
status
timestamps
```

Categories:

```text
Keluarga
Teman
Rekan Kerja
VIP
Teman Pengantin Pria
Teman Pengantin Wanita
```

Client dapat:

* create
* edit
* delete
* move category
* search
* filter
* import CSV

---

# 40. CSV IMPORT

CSV format:

```text
name
phone
email
category
max_pax
```

Setelah import tampilkan:

```text
Imported
Failed
Duplicate
```

Gunakan batch processing untuk file besar.

Jangan membuat request HTTP sangat lama untuk import besar.

---

# 41. QR GUEST INVITATION

Support:

* generate
* preview
* download
* print
* copy personal URL
* regenerate token

QR harus mengarah ke personal invitation URL.

---

# 42. SECURE GUEST TOKEN

Personal URL:

```text
/{invitationId}/{invitationSlug}/u/{secureToken}
```

Token harus:

* cryptographically secure
* high entropy
* random
* unique
* revocable
* regeneratable

Jangan menggunakan:

```text
guest_id
name
category_id
phone
```

sebagai token.

Jangan menyimpan identity guest di frontend sebagai source of truth.

---

# 43. TOKEN SECURITY TEST

Jika URL:

```text
TOKEN_BUDI?name=Andi
```

maka sistem tetap harus menampilkan:

```text
Budi
```

Query parameter tidak boleh mengubah identity.

Contoh:

```text
?guest_id=123
```

juga tidak boleh mengubah guest yang resolved dari secure token.

Token harus diverifikasi terhadap wedding/invitation yang benar.

Token rotation harus membuat token lama invalid.

---

# 44. RSVP

Fields:

```text
attendance_status
pax
note
```

Statuses:

```text
attending
not_attending
maybe
pending
```

Guest hanya boleh mengubah RSVP dirinya sendiri berdasarkan secure token.

Jangan menggunakan guest ID dari request sebagai authorization.

---

# 45. GUEST BOOK

Fields:

```text
name
message
attendance_status
pax
status
```

Status:

```text
pending
approved
rejected
hidden
```

Default:

```text
Moderation ON
```

Admin dapat:

* search
* filter
* approve
* reject
* hide
* delete
* restore
* bulk moderation

Security:

* validation
* rate limiting
* optional CAPTCHA
* duplicate detection
* no arbitrary HTML
* XSS-safe output

Jika moderation ON:

hanya approved message yang boleh public.

---

# 46. QR CHECK-IN

Route:

```text
/check-in
/check-in/{token}
```

Flow:

```text
Scan QR
↓
Validate token
↓
Resolve wedding
↓
Resolve guest
↓
Show name
↓
Show category
↓
Show max pax
↓
Input actual pax
↓
Confirm
```

Jika sudah check-in:

tampilkan existing check-in.

Update harus membutuhkan confirmation.

Search fallback:

* name
* phone

Jika multiple matches:

jangan memilih otomatis.

Tampilkan pilihan exact guest.

---

# 47. CHECK-IN TABLE

```text
guest_checkins

id
wedding_id
guest_id
checked_in_at
pax
checked_in_by
notes
timestamps
```

Gunakan transaction untuk check-in.

Cegah double check-in race condition.

Semua check-in harus dapat diaudit.

---

# 48. GENERIC GUEST VISIBILITY ENGINE

Jangan membuat visibility engine khusus gift saja.

Harus generic.

Bisa digunakan untuk:

* sections
* events
* gift
* gift methods
* maps
* dress code
* private reception
* family event
* special notes
* custom content

Priority:

```text
Individual Guest Override
        ↓
Category Rule
        ↓
Wedding Default
```

Guest context harus berasal dari secure token.

Visibility harus diproses server-side.

Jangan:

```text
send private data to browser
↓
hide with CSS/JS
```

Data private tidak boleh dikirim ke browser sama sekali.

---

# 49. GIFT SYSTEM

Types:

```text
bank_transfer
qris
e_wallet
cash
custom
```

Bank:

```text
bank_name
account_number
account_holder
label
description
is_active
sort_order
```

QRIS:

```text
image
merchant_name
description
is_active
sort_order
```

Contoh visibility:

```text
Keluarga
→ BCA
→ Mandiri
→ QRIS

Teman
→ QRIS

Rekan Kerja
→ none

VIP
→ all
```

Individual override harus memiliki priority tertinggi.

---

# 50. DASHBOARD

Dashboard menampilkan:

* wedding/invitation
* template
* publish status
* total guests
* RSVP
* checked-in
* pending messages
* approved messages

Jangan membuat dashboard menjadi grid berisi banyak colorful cards.

Gunakan hierarchy yang lebih editorial/product-oriented.

---

# 51. EDITOR NAVIGATION

Sidebar:

```text
General
Couple
Event
Template
Appearance
Animations
Sections
Photos
Music
Playlist
Guests
Categories
RSVP
Guest Book
Gift
Visibility Rules
Settings
SEO
Domains
```

Gunakan struktur yang mudah dipahami.

---

# 52. PREVIEW SYSTEM

Template selector harus memiliki:

* preview
* desktop preview
* tablet preview
* mobile preview

Idealnya terdapat:

```text
Live Preview
```

tanpa membuat editor menjadi terlalu kompleks.

---

# 53. DRAFT / PUBLISHED / ARCHIVED

Wedding status:

```text
draft
published
archived
```

Support:

* draft
* preview
* publish
* unpublish
* archive

Idealnya support version/snapshot sehingga perubahan besar dapat dipulihkan.

Public route hanya menampilkan published content kecuali preview memiliki authorization yang benar.

---

# 54. SEO

Support:

```text
SEO title
SEO description
OG image
favicon
canonical URL
social preview
WhatsApp share preview
```

Canonical harus mengikuti:

```text
domain
invitationId
current slug
```

Old slug harus canonical redirect.

---

# 55. SECURITY

Implementasikan:

* CSRF
* XSS protection
* SQL injection protection
* Policies
* Gates
* tenant isolation
* secure token
* rate limiting
* secure password hashing
* file validation
* MIME validation
* upload size limits
* secure headers bila feasible
* authorization pada mutation
* HTTPS production

Jangan pernah log:

* password
* full invitation token
* secrets
* API credentials
* sensitive financial data

Health endpoint:

```text
/health
```

tidak boleh membocorkan:

* secrets
* credentials
* internal topology
* database details yang sensitif

Rate limit minimal untuk:

* login
* RSVP
* guest book
* token endpoints
* check-in
* guest search
* domain verification
* deployment

---

# 56. AUDIT LOG

Audit events:

```text
login
logout
guest CRUD
token generation
token regeneration
check-in
RSVP changes
guestbook moderation
template changes
visibility changes
gift changes
media upload
media delete
publish
domain changes
deployment actions
```

Fields:

```text
user_id
action
entity_type
entity_id
metadata
ip
user_agent
timestamp
```

Metadata tidak boleh menyimpan secret.

---

# 57. CODE ARCHITECTURE

Gunakan service layer.

Contoh:

```text
TenantResolver
WeddingContext
DomainResolver
InvitationResolver

WeddingService
InvitationService
GuestService
GuestVisibilityService
CheckInService
QrCodeService

TemplateService
TemplateRenderer

MediaService
MusicService
PlaylistService

GiftService
RsvpService
GuestBookService

DomainService
CloudflareService

DeploymentService
DeploymentProvisioner

AuditLogService
```

Gunakan:

* Form Requests
* Policies
* Resources/DTO jika membantu
* Model scopes
* Relationships
* Events
* Listeners
* Jobs
* Notifications

Hindari giant controllers.

Hindari giant Blade files.

Hindari business logic di Blade.

---

# 58. DATABASE INDEXING

Index minimal:

```text
client_id
wedding_id
public_id
slug
domain
invitation_token
category_id
status
created_at
```

Tambahkan composite index dan unique constraint sesuai query pattern.

Contoh:

```text
unique(public_id)
unique(domain)
```

Gunakan foreign keys yang sesuai.

---

# 59. PERFORMANCE

Prioritas:

* fast first paint
* mobile performance
* optimized images
* lazy loading
* minimal JS
* efficient queries
* eager loading
* pagination
* caching
* lightweight animation
* non-blocking audio

Hindari N+1.

Gunakan:

* eager loading
* scopes
* pagination
* query optimization

Cache:

* template registry
* template assets
* domain mapping
* published invitation config bila aman

Jangan cache personalized guest content tanpa guest-aware cache key.

---

# 60. QUEUE

Gunakan queue untuk pekerjaan berat:

```text
image processing
thumbnail generation
QR batch generation
notifications
CSV import
media optimization
deployment
```

Redis boleh digunakan.

Tetapi aplikasi harus memiliki fallback yang masuk akal untuk deployment sederhana/shared hosting, misalnya database queue.

---

# 61. SCALING

Architecture harus dapat berkembang:

```text
Load Balancer
      ↓
Laravel Node 1
Laravel Node 2
Laravel Node 3
      ↓
MySQL
Redis
Object Storage
CDN
```

Bottleneck yang harus diperhatikan:

* image processing
* media bandwidth
* audio/video
* database
* object storage
* CDN traffic

Gunakan object storage + CDN ketika scale meningkat.

---

# 62. BACKUP

Backup:

```text
MySQL
Object Storage
Application Configuration
Domain Configuration
Deployment Configuration
Secrets
```

Secrets harus dikelola secara aman.

Dokumentasikan:

* backup schedule
* restore procedure
* retention
* disaster recovery

---

# 63. MONITORING

Support monitoring untuk:

* application logs
* errors
* health
* database
* storage
* queues
* deployment status

Sediakan health endpoint yang aman.

---

# 64. TESTING

Wajib membuat test untuk:

## Authentication

* role authorization
* unauthorized access

## Tenant Isolation

Wedding A tidak boleh mengakses Wedding B.

## Invitation

* valid public_id
* invalid public_id
* valid slug
* invalid slug
* old slug redirect
* unpublished invitation
* archived invitation
* domain mismatch

## Guest Token

* valid token
* invalid token
* expired/revoked token
* token manipulation
* token rotation
* wrong wedding token

Test eksplisit:

```text
TOKEN_BUDI?name=Andi
```

tetap menghasilkan Budi.

## RSVP

Guest hanya dapat mengubah RSVP miliknya.

## Guest Book

* moderation
* XSS
* rate limiting
* invalid input

## Check-in

* valid scan
* invalid token
* double check-in
* race condition
* guest search
* multiple search matches

## Visibility

Test:

```text
Individual Override
>
Category Rule
>
Wedding Default
```

## Gift

Test visibility per guest/category.

## Templates

* template switching
* settings
* sections
* animations

## Media

* tenant isolation
* MIME validation
* size validation
* deletion

## Playlist

* ordering
* active playlist
* delete

## Domain

Test:

```text
Domain A + Invitation B
```

harus ditolak.

Contoh:

```text
bagasrani.ngundang.com/INV-B/andi-sari
```

tidak boleh merender invitation B jika domain tersebut milik invitation A.

## Deployment

Test:

```text
pending
→ provisioning
→ deploying
→ healthy
```

dan failure/retry/suspend/terminate.

---

# 65. FACTORIES & SEEDERS

Buat:

```text
Super Admin
Demo Client
Demo Wedding
Templates
Categories
Guests
RSVP
Guest Book
Gift
Visibility Rules
Media placeholders
Playlist
Domain
```

Demo wedding:

```text
Andi & Sari
```

Categories:

```text
Keluarga
Teman
VIP
Rekan Kerja
```

Template:

```text
Minang Elegance
```

Gunakan demo public ID yang valid secara format.

---

# 66. ADMIN EXPERIENCE

Admin harus dapat:

1. Login
2. Create client
3. Create wedding
4. Generate public_id
5. Generate slug
6. Configure template
7. Configure sections
8. Upload media
9. Configure music
10. Import guests
11. Generate QR
12. Configure RSVP
13. Configure Guest Book
14. Configure Gift
15. Configure Visibility Rules
16. Configure SEO
17. Configure domain
18. Preview
19. Publish

Flow harus terasa sederhana.

Jangan membuat user harus memahami arsitektur teknis.

---

# 67. SHARED HOSTING SUPPORT

Core application harus dapat berjalan pada:

```text
PHP
MySQL
Laravel
```

tanpa:

```text
Docker
Traefik
Redis
Kubernetes
```

sebagai dependency wajib.

Dokumentasikan deployment shared hosting.

---

# 68. DOCKER DEPLOYMENT

Sediakan Docker configuration untuk deployment production.

Contoh architecture:

```text
Cloudflare
    ↓
Traefik
    ↓
Laravel
    ↓
MySQL
    ↓
Redis/Object Storage optional
```

Jangan membuat container per wedding pada shared deployment.

---

# 69. CUSTOM DOMAIN

Support:

```text
bagasrani.ngundang.com
```

dan:

```text
bagasrani.com
```

Custom domain harus melalui:

```text
verification
validation
mapping
SSL/TLS
```

Jangan mengklaim domain berhasil sebelum benar-benar terverifikasi.

---

# 70. DOMAIN + INVITATION ROUTING CONTRACT

Public route:

```text
/{invitationId}/{invitationSlug}
```

Guest route:

```text
/{invitationId}/{invitationSlug}/u/{secureToken}
```

Resolver harus bekerja seperti:

```text
Host
 ↓
DomainResolver
 ↓
Wedding
 ↓
Invitation public_id
 ↓
Validate slug
 ↓
Canonical redirect if necessary
 ↓
Published check
 ↓
Render invitation
```

Guest:

```text
Host
 ↓
DomainResolver
 ↓
Wedding
 ↓
Invitation
 ↓
SecureToken
 ↓
Guest
 ↓
VisibilityContext
 ↓
Render personalized invitation
```

---

# 71. PERSONALIZED CONTENT SECURITY

Jangan pernah mengirim data private ke browser kemudian menyembunyikannya dengan:

```css
display:none
```

atau:

```javascript
if (...)
```

Server harus menentukan data apa yang boleh dikirim.

Guest context:

```text
Wedding
Invitation
Guest
Category
Individual Overrides
```

kemudian menghasilkan:

```text
Visible Content
```

---

# 72. UI COMPONENT ARCHITECTURE

Buat reusable Blade components.

Namun jangan over-componentization.

Gunakan component ketika:

* digunakan kembali
* memiliki behavior sendiri
* memiliki semantic responsibility
* membutuhkan consistency

Jangan membuat component hanya untuk:

```text
<div>
```

yang dipakai satu kali tanpa alasan.

---

# 73. DESIGN SYSTEM

Buat design tokens untuk:

* typography
* spacing
* radius
* border
* shadow
* colors
* transitions
* breakpoints

Radius harus restrained.

Shadow harus subtle.

Border/tonal contrast lebih diutamakan daripada shadow besar.

---

# 74. VISUAL QUALITY TEST

Sebelum dianggap selesai, lakukan evaluasi:

### Test 1

Jika UI terlihat seperti:

```text
AI-generated SaaS dashboard
```

→ redesign.

### Test 2

Jika terlalu banyak:

* cards
* radius
* shadows
* icons
* colors
* gradients
* animations

→ simplify.

### Test 3

Jika typography hierarchy tidak kuat tanpa decoration:

→ improve typography.

### Test 4

Jika ornament lebih menarik perhatian daripada wedding content:

→ reduce ornament.

### Test 5

Jika setiap element bergerak:

→ remove unnecessary animation.

### Test 6

Jika mobile terlihat seperti desktop yang diperkecil:

→ redesign mobile layout.

Target akhir:

> Premium, calm, refined, editorial, professional.

---

# 75. README

Buat README lengkap yang menjelaskan:

```text
Architecture
Installation
Environment
Database
Multi-tenancy
Invitation Public ID
Invitation Slug
Public URL
Guest Token
Templates
Sections
Animations
Visibility Rules
Gift
Media
Music
Playlist
RSVP
Guest Book
Check-in
Cloudflare
Wildcard DNS
Traefik
Custom Domains
Docker
Shared Deployment
Dedicated Deployment
Shared Hosting
Object Storage
Queue
Redis
Caching
Scaling
Backup
Security
Testing
Troubleshooting
```

README Cloudflare harus menjelaskan secara eksplisit:

```text
*.ngundang.com
```

sehingga tidak perlu membuat DNS record baru untuk setiap invitation subdomain.

README shared hosting harus menjelaskan bahwa:

```text
Docker/Traefik/Redis
```

bukan dependency wajib.

README dedicated deployment harus menjelaskan controlled provisioning.

---

# 76. ENVIRONMENT CONFIGURATION

Gunakan `.env.example`.

Minimal konfigurasi:

```text
APP_NAME
APP_ENV
APP_KEY
APP_URL

DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD

FILESYSTEM_DISK

QUEUE_CONNECTION
CACHE_STORE
SESSION_DRIVER

CLOUDFLARE_API_TOKEN
CLOUDFLARE_ZONE_ID

MAIL_*
```

Jangan commit secret.

---

# 77. DEVELOPMENT PRINCIPLE

Jangan membangun fitur secara isolated tanpa memikirkan integrasi.

Setiap feature harus memperhatikan:

```text
Security
Authorization
Tenant Isolation
Performance
UX
Accessibility
Testing
Maintainability
```

Contoh:

Guest CRUD bukan hanya:

```text
CRUD database
```

tetapi harus mencakup:

```text
Tenant isolation
Authorization
Validation
Audit
Token lifecycle
QR
Visibility
RSVP
Check-in
```

---

# 78. IMPLEMENTATION ORDER

Bangun secara bertahap:

## Phase 1 — Foundation

* Laravel setup
* authentication
* roles
* database
* multi-tenancy
* policies
* audit logs

## Phase 2 — Invitation Core

* wedding
* public_id
* slug
* invitation resolver
* domain resolver
* public routing
* publishing

## Phase 3 — Template Engine

* template registry
* template renderer
* Minang Elegance
* sections
* appearance
* animations

## Phase 4 — Media

* storage
* upload
* optimization
* gallery
* video
* music
* playlist

## Phase 5 — Guest System

* categories
* guest CRUD
* CSV import
* secure token
* QR

## Phase 6 — RSVP / Guest Book

* RSVP
* guest book
* moderation
* anti-spam

## Phase 7 — Visibility / Gift

* generic visibility engine
* gift methods
* guest/category override

## Phase 8 — Check-in

* scanner
* search
* check-in
* audit

## Phase 9 — Domains

* subdomain
* wildcard DNS architecture
* custom domain
* Cloudflare integration

## Phase 10 — Deployment

* shared deployment
* Docker
* Traefik
* dedicated deployment
* deployment state machine

## Phase 11 — Hardening

* security audit
* performance
* caching
* queue
* testing
* monitoring
* backup
* documentation

---

# 79. DEFINITION OF DONE

Fitur tidak dianggap selesai hanya karena endpoint atau UI sudah dibuat.

Setiap feature harus memenuhi:

```text
✓ Database
✓ Model
✓ Validation
✓ Authorization
✓ Tenant Isolation
✓ Service Layer
✓ UI
✓ Error Handling
✓ Audit where applicable
✓ Tests
✓ Documentation where applicable
```

---

# 80. FINAL PRODUCT STANDARD

Hasil akhir Ngundang harus terasa seperti:

> produk wedding invitation premium yang serius dan production-ready.

Bukan:

> template Laravel demo.

Bukan:

> generic SaaS dashboard.

Bukan:

> AI-generated UI penuh card, gradient, shadow, badge, icon, dan animasi.

Public invitation harus:

```text
Elegant
Editorial
Emotional
Fast
Mobile-first
Culturally respectful
Premium
```

Admin harus:

```text
Clean
Efficient
Professional
Consistent
Accessible
Scalable
```

Architecture harus:

```text
Reusable
Secure
Multi-tenant
Testable
Maintainable
Deployable
Scalable
```

---

# FINAL INSTRUCTION

Mulai implementasi berdasarkan spesifikasi di atas.

Jangan mengurangi requirement penting.

Jika terdapat beberapa cara implementasi, pilih pendekatan yang:

1. paling maintainable
2. paling secure
3. paling sederhana
4. paling scalable
5. paling mudah dites
6. paling mudah dideploy
7. paling reusable

Jangan menambahkan kompleksitas hanya demi terlihat sophisticated.

**Build the simplest architecture that can reliably support the full product.**

Untuk UI:

> **Less but Better.**

> **Every visual element must have a reason to exist.**

Jika suatu desain terlihat seperti AI-generated SaaS template, **jangan pertahankan desain tersebut — redesign menjadi lebih clean, minimal, editorial, dan profesional.**
