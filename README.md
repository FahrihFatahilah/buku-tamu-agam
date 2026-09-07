# Ngundang

Platform undangan pernikahan digital multi-tenant. Satu Laravel codebase untuk banyak client, banyak wedding, banyak template.

---

## Architecture

```
Super Admin
    └── Client
          └── Wedding (public_id: INV-XXXXXX, slug: groom-bride)
                ├── Sections (16 sections, enable/disable/reorder)
                ├── Events (akad, resepsi, dll)
                ├── Guests (secure token per guest)
                ├── RSVP
                ├── Guest Book (moderation)
                ├── Gift Methods (bank, QRIS, e-wallet)
                ├── Visibility Rules (generic engine)
                ├── Media (hero, gallery, video)
                ├── Playlist (audio upload)
                └── Domains (subdomain + custom)
```

### Multi-tenancy

Satu Laravel application melayani semua tenant. Isolasi dilakukan melalui:

- **Policies** — setiap mutation divalidasi kepemilikan
- **Query scopes** — `forClient()`, `published()`
- **Middleware** — auth + role check
- **Service layer** — tidak ada business logic di controller/blade
- **Domain resolver** — validasi domain + invitation ownership

### Key Services

| Service | Tanggung Jawab |
|---|---|
| `DomainResolver` | Resolve wedding dari host, cache 5 menit |
| `InvitationResolver` | Validasi public_id + slug + domain |
| `InvitationService` | Facade: resolve + visibility filtering |
| `TemplateRenderer` | Build view data untuk invitation |
| `GuestTokenService` | Resolve guest dari secure token |
| `GuestVisibilityService` | Server-side visibility engine |
| `CheckInService` | Check-in dengan race condition protection |
| `WeddingService` | Wedding CRUD + slug management |
| `MediaService` | Upload dengan MIME validation |
| `PlaylistService` | Audio upload + playlist management |
| `QrCodeService` | Generate QR SVG/PNG per guest |
| `RsvpService` | RSVP upsert + audit |
| `GuestBookService` | Guest book submit + moderation |
| `DeploymentService` | State machine + provisioning via queue |
| `AuditLogService` | Audit trail semua aksi penting |
| `CloudflareService` | Cloudflare API (opsional) |
| `TemplateService` | Template registry (cached 1 jam) |

---

## Public URL Structure

```
/{invitationId}/{invitationSlug}
/{invitationId}/{invitationSlug}/u/{secureToken}
```

Contoh:
```
https://ngundang.com/INV-8F3K2A/bagas-rani
https://ngundang.com/INV-8F3K2A/bagas-rani/u/a3f9b2...
https://bagasrani.ngundang.com/INV-8F3K2A/bagas-rani
https://bagasrani.com/INV-8F3K2A/bagas-rani
```

### Invitation Public ID

Format: `INV-XXXXXX` (6 karakter alphanumeric uppercase)

- Tidak pernah berubah meskipun slug berubah
- Tidak menggunakan auto-increment database ID
- Cryptographically random

### Slug

- Readable, SEO-friendly
- Berasal dari nama pengantin
- Dapat diubah
- Perubahan slug otomatis membuat redirect 301 dari slug lama

---

## Guest Token Security

Setiap tamu memiliki `invitation_token` yang:

- 64 karakter hex (32 bytes random)
- Cryptographically secure (`random_bytes`)
- Unik per tamu
- Dapat di-regenerate (token lama langsung invalid)
- Divalidasi terhadap wedding yang benar

**PENTING:** Query parameter (`?name=Andi`, `?guest_id=123`) tidak dapat mengubah identitas tamu. Token adalah satu-satunya sumber kebenaran.

---

## Templates

| Key | Nama | Palette |
|---|---|---|
| `minang-elegance` | Minang Elegance | Deep Maroon, Cream, Gold |
| `modern-luxury` | Modern Luxury | Black, White, Gold |
| `floral-romantic` | Floral Romantic | Rose, Blush, Sage |
| `islamic-elegant` | Islamic Elegant | Green, Cream, Gold |
| `traditional-nusantara` | Traditional Nusantara | Brown, Cream, Gold |
| `minimalist` | Minimalist | Black, White, Grey |

Template switching tidak menghapus wedding data. Sections, guests, RSVP, dll tetap utuh.

---

## Sections (16)

```
opening, hero, couple, quote, countdown, event, venue,
maps, love_story, gallery, video, rsvp, guest_book,
gift, timeline, closing
```

Setiap section dapat: enable/disable, reorder, custom title, custom settings.

---

## Animations

Preset global: `elegant`, `cinematic`, `romantic`, `traditional`, `minimal`, `none`

Konfigurasi per wedding: preset, duration (fast/normal/slow), intensity (subtle/normal/strong)

Selalu support `prefers-reduced-motion`. CSS-first, IntersectionObserver untuk reveal.

---

## Visibility Rules

Priority (tertinggi ke terendah):

```
Individual Guest Override
        ↓
Category Rule
        ↓
Wedding Default (visible jika tidak ada rule)
```

Data private **tidak pernah dikirim ke browser** kemudian disembunyikan dengan CSS/JS. Server menentukan data apa yang boleh dikirim.

Berlaku untuk: gift_method, event, section.

---

## Gift Methods

Types: `bank_transfer`, `qris`, `e_wallet`, `cash`, `custom`

Visibility dapat dikonfigurasi per kategori tamu atau per tamu individual.

---

## Media

Collections: `hero`, `couple`, `gallery`, `family`, `prewedding`, `video`

- MIME validation (bukan hanya extension)
- Alt text untuk accessibility
- Reorder via drag-drop
- S3-compatible via Laravel Storage abstraction

---

## Music & Playlist

- Upload MP3/OGG/WAV (maks 20MB)
- Autoplay dengan fallback elegan jika browser memblokir
- Loop, shuffle, volume control
- Multiple tracks dengan reorder

---

## RSVP

Fields: `attendance_status` (attending/not_attending/maybe), `pax`, `note`

Guest hanya boleh mengubah RSVP dirinya sendiri berdasarkan secure token. Guest ID dari request body diabaikan.

---

## Guest Book

- Moderation ON by default (pending → approved/rejected/hidden)
- Bulk moderation
- XSS-safe (Blade auto-escape)
- Rate limited (30 req/menit)

---

## Check-in

Flow: Scan QR → Validate token → Show guest info → Input pax → Confirm

- Race condition protection via `DB::transaction()` + `lockForUpdate()`
- Search fallback (name/phone)
- Multiple matches → operator pilih manual (tidak auto-select)
- Audit log setiap check-in

---

## Domain Resolution

```
Request Host
    ↓
DomainResolver (cache 5 menit)
    ↓
Validasi: domain harus milik wedding yang sama dengan public_id
    ↓
InvitationResolver (validasi slug, published status)
    ↓
Render invitation
```

**Domain mismatch ditolak.** `bagasrani.ngundang.com/INV-B/andi-sari` akan 404 jika domain tersebut milik Wedding A.

---

## Cloudflare Wildcard DNS

Untuk subdomain `*.ngundang.com`:

1. Buat satu DNS record di Cloudflare: `*.ngundang.com → server IP`
2. Tidak perlu membuat DNS record baru untuk setiap subdomain
3. Saat client membuat wedding, Laravel hanya menyimpan domain mapping di database

Cloudflare API hanya dibutuhkan untuk:
- Custom domain SSL provisioning
- Cache purge

Bukan dependency wajib untuk setiap invitation.

---

## Installation

### Requirements

- PHP 8.2+
- MySQL 8.0+ (atau SQLite untuk development)
- Composer
- Node.js 18+

### Setup

```bash
git clone https://github.com/yourorg/ngundang.git
cd ngundang

composer install
npm install

cp .env.example .env
php artisan key:generate

# Edit .env sesuai kebutuhan

php artisan migrate --seed
php artisan storage:link

npm run build
```

### Demo Credentials

```
Super Admin:  admin@ngundang.com / password
Client Admin: clientadmin@demo.com / password
Operator:     operator@demo.com / password
```

Demo wedding: `http://localhost:8000/INV-DEMO01/andi-sari`

---

## Shared Hosting Deployment

**Docker, Traefik, Redis, dan Kubernetes BUKAN dependency wajib.**

Aplikasi dapat berjalan pada shared hosting PHP + MySQL biasa:

```bash
# Upload files ke public_html atau subdirectory
# Set document root ke /public

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=ngundang
DB_USERNAME=user
DB_PASSWORD=pass

QUEUE_CONNECTION=database  # Tidak perlu Redis
CACHE_STORE=database       # Tidak perlu Redis
SESSION_DRIVER=database

php artisan migrate --seed
npm run build
php artisan storage:link
```

Untuk queue pada shared hosting, gunakan cron:
```
* * * * * cd /path/to/ngundang && php artisan queue:work --stop-when-empty
```

---

## Docker Deployment

```bash
docker compose up -d
```

Architecture:
```
Cloudflare (wildcard DNS *.ngundang.com)
    ↓
Traefik (reverse proxy, SSL termination)
    ↓
Laravel Application (shared, satu container)
    ↓
MySQL
```

**Satu Traefik untuk semua wedding.** Tidak ada container per wedding.

---

## Dedicated Deployment

Untuk client premium yang membutuhkan isolated environment:

```
State machine: pending → provisioning → deploying → healthy
                                                   → failed → provisioning (retry)
                                                   → suspended
                                                   → terminated
```

Provisioning dilakukan melalui `DeploymentService` + `ProvisionDeployment` Job, bukan langsung dari browser.

**Tidak ada arbitrary Docker/shell command dari web process.**

---

## Object Storage

Untuk production dengan traffic tinggi, gunakan S3-compatible storage:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=ngundang-media
AWS_URL=https://cdn.ngundang.com
```

Aplikasi menggunakan Laravel Storage abstraction — tidak ada hardcoded path.

---

## Queue

Gunakan queue untuk:
- Image processing
- QR batch generation
- CSV import besar (>100 rows)
- Notifications
- Deployment provisioning

Default: `database` queue (tidak perlu Redis).
Production: gunakan `redis` untuk performa lebih baik.

```env
QUEUE_CONNECTION=database  # shared hosting
QUEUE_CONNECTION=redis     # production
```

---

## Caching

| Item | TTL | Driver |
|---|---|---|
| Domain mapping | 5 menit | Cache |
| Template registry | 1 jam | Cache |

Cache invalidated otomatis saat domain dihapus atau template diupdate.

Jangan cache personalized guest content tanpa guest-aware cache key.

---

## Scaling

Architecture yang dapat berkembang:

```
Load Balancer
      ↓
Laravel Node 1 / Node 2 / Node 3
      ↓
MySQL (primary + replica)
Redis (queue + cache)
Object Storage (S3)
CDN
```

Bottleneck utama: media bandwidth, image processing, database.

---

## Backup

### Yang harus di-backup:

| Item | Frekuensi | Retensi |
|---|---|---|
| MySQL database | Harian | 30 hari |
| Object storage (media) | Mingguan | 90 hari |
| `.env` dan secrets | Setiap perubahan | Permanent |
| Domain configuration | Setiap perubahan | Permanent |

### Restore procedure:

```bash
# 1. Restore database
mysql -u user -p ngundang < backup.sql

# 2. Restore media (jika menggunakan local storage)
tar -xzf media-backup.tar.gz -C storage/app/public/

# 3. Regenerate caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Disaster recovery:

1. Provision server baru
2. Deploy aplikasi
3. Restore database dari backup terbaru
4. Restore media
5. Update DNS jika diperlukan
6. Verifikasi health endpoint: `GET /health`

---

## Security

- CSRF protection pada semua form
- Rate limiting: login (5/menit), RSVP (30/menit), guest book (30/menit), domain verify (10/menit)
- MIME validation pada upload (bukan hanya extension)
- Secure token dengan `random_bytes(32)`
- Policies pada semua mutation
- Tenant isolation di service layer
- Audit log untuk semua aksi penting
- Private data tidak pernah dikirim ke browser kemudian disembunyikan
- Health endpoint tidak membocorkan secrets atau internal topology

---

## Monitoring

Health endpoint: `GET /health`

Response:
```json
{
  "status": "ok",
  "timestamp": "2025-01-01T00:00:00Z",
  "database": "ok",
  "storage": "ok",
  "queue": { "pending": 0, "failed": 0 }
}
```

Status `degraded` (HTTP 503) jika: database error, storage error, atau >10 failed jobs.

---

## Testing

```bash
php artisan test
```

Test coverage:
- Invitation routing (valid, invalid, old slug redirect, domain mismatch)
- Guest token security (manipulation, wrong wedding, regeneration)
- Check-in (valid scan, double check-in, race condition, search, multiple matches)
- Visibility engine (priority: individual > category > default)
- Authorization (role isolation, tenant isolation, inactive user)
- RSVP & Guest Book (token identity, XSS, validation, moderation)
- Media (MIME validation, tenant isolation)
- Gift visibility (category rule, individual override)
- Playlist (upload, delete, settings)
- Template switching (data preservation)
- Deployment state machine
- Wedding management (archive, preview, OG image, CSV async)

---

## Troubleshooting

### Undangan tidak muncul (404)

1. Pastikan status wedding = `published`
2. Pastikan `public_id` format benar: `INV-XXXXXX`
3. Jika menggunakan custom domain, pastikan domain sudah terverifikasi
4. Cek `domain_mapping` cache: `php artisan cache:clear`

### Domain mismatch

Domain harus terdaftar di `wedding_domains` dan milik wedding yang sama dengan `public_id` di URL.

### Queue tidak berjalan

```bash
php artisan queue:work --stop-when-empty  # shared hosting
php artisan queue:work --daemon           # server
```

### Storage link hilang

```bash
php artisan storage:link
```

### Cache stale

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```
