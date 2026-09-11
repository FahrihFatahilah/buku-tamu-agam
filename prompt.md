# NGUNDANG — FINAL MASTER PROMPT

You are a **Senior Software Architect, Senior Laravel Engineer, Senior UI/UX Product Designer, and DevOps Engineer**.

Build a production-ready wedding invitation platform called **Ngundang** that is reusable, scalable, secure, multi-tenant, and capable of serving many clients and weddings from **one Laravel codebase**.

Do not build a simple prototype, toy application, or superficial demo. Build a solid product foundation that can realistically evolve into a production SaaS platform.

---

# 1. PRODUCT VISION

Ngundang is a digital wedding invitation platform designed to serve multiple clients and weddings.

A single Laravel application must support:

* multiple clients
* multiple weddings/invitations
* multiple guests
* multiple templates
* multiple domains/subdomains
* configurable invitation content
* guest-specific content
* shared deployments
* optional dedicated deployments for premium/enterprise clients

Core hierarchy:

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

Follow these principles:

* SOLID
* DRY
* KISS
* Separation of Concerns
* Secure by Default
* Multi-Tenant by Default
* Reusable Architecture
* Configuration over Hardcoding
* Service Layer
* Policies / Gates
* Form Requests
* Events / Listeners
* Jobs / Queues
* Audit Logs
* Testable Architecture

Do not create a separate Laravel project for every wedding.

---

# 2. TECHNOLOGY STACK

Use:

* Latest stable Laravel
* Compatible latest stable PHP version
* MySQL
* Blade
* Tailwind CSS
* shadcn/ui
* Alpine.js or vanilla JavaScript
* Vite
* Laravel Authentication
* Laravel Storage
* A mature QR Code package
* PHPUnit/Pest
* Docker as an optional deployment method
* Traefik as a reverse proxy for platform deployments
* Cloudflare for DNS/CDN/domain management where appropriate

The application **must remain capable of running on standard PHP + MySQL shared hosting** without requiring Docker, Traefik, Redis, or Kubernetes as mandatory dependencies.

Docker and Traefik are deployment options, not core application dependencies.

---

# 3. MULTI-TENANCY

Use one Laravel application for all tenants.

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

Every request must have a clearly resolved tenant/wedding context.

Implement dedicated resolvers/services such as:

```text
TenantResolver
WeddingContext
DomainResolver
InvitationResolver
```

Do not rely solely on developer discipline for tenant isolation.

Use:

* Policies
* Gates
* Middleware
* Query scopes
* Explicit ownership validation
* Authorization on every mutation

Wedding A **must never** be able to read or modify Wedding B data.

---

# 4. USER ROLES

At minimum, implement:

## Super Admin

Can:

* manage all clients
* manage all weddings
* manage templates
* manage users
* manage domains
* manage deployments
* view audit logs
* manage system settings

## Client / Wedding Admin

Can only manage weddings they own:

* invitation
* couple
* event
* sections
* template
* appearance
* animations
* media
* music
* guests
* categories
* RSVP
* guest book
* gift
* visibility rules
* SEO
* domains

## Check-in Operator

Can only:

* search guests
* scan QR codes
* view guest status
* check guests in
* update actual pax according to permissions

Must not access:

* financial/gift configuration
* global settings
* template management
* client management
* deployment management

---

# 5. INVITATION DATA MODEL

The wedding/invitation entity should contain at least:

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

Add:

```text
public_id
```

as the public-facing identifier.

---

# 6. PUBLIC INVITATION ID

Never expose an auto-increment database ID as the public invitation identifier.

Use:

* ULID
* UUID
* or another random, high-entropy public identifier

Example:

```text
INV-8F3K2A
```

The `public_id` must be:

* unique
* difficult to guess
* stable
* independent from the database primary key
* unchanged when the invitation slug changes

Database constraint:

```text
public_id UNIQUE
```

---

# 7. PUBLIC INVITATION URL

The primary invitation URL must be:

```text
/{invitationId}/{invitationSlug}
```

Example:

```text
https://ngundang.com/INV-8F3K2A/bagas-rani
```

Subdomain example:

```text
https://bagasrani.ngundang.com/INV-8F3K2A/bagas-rani
```

Custom domain example:

```text
https://bagasrani.com/INV-8F3K2A/bagas-rani
```

Personal guest invitation URL:

```text
/{invitationId}/{invitationSlug}/u/{secureToken}
```

Example:

```text
https://ngundang.com/INV-8F3K2A/bagas-rani/u/X9K82...
```

---

# 8. INVITATION SLUG

The invitation slug must be:

* human-readable
* SEO-friendly
* derived from the couple/invitation name
* unique as required by routing
* editable by the client

Examples:

```text
bagas-rani
bagas-rani-wedding
bagas-rani-baralek
```

When the slug changes:

```text
INV-8F3K2A
```

must remain unchanged.

Old slugs must perform a canonical redirect to the current slug.

Example:

```text
/INV-8F3K2A/bagas-rani
```

redirects to:

```text
/INV-8F3K2A/bagas-rani-wedding
```

Changing the slug must never change the invitation's public ID.

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

Laravel must validate:

1. request host/domain
2. invitation public ID
3. invitation slug
4. domain ownership/mapping
5. wedding status
6. published status

Do not resolve invitations using `public_id` alone.

Example:

```text
bagasrani.ngundang.com/INV-B/andi-sari
```

If `bagasrani.ngundang.com` belongs to Wedding A, the application **must not** render Wedding B simply because `INV-B` exists.

The domain and invitation must be consistent.

---

# 10. CLOUDFLARE WILDCARD DNS

Main domain example:

```text
ngundang.com
```

Use wildcard DNS:

```text
*.ngundang.com
```

pointing to the platform/server.

With wildcard DNS, these subdomains:

```text
bagasrani.ngundang.com
andi-sari.ngundang.com
doni-putri.ngundang.com
```

do not require a new Cloudflare DNS record for every invitation.

When a new wedding is created, Laravel only needs to create a domain mapping in the database:

```text
bagasrani.ngundang.com
```

Do not call the Cloudflare API for every subdomain when wildcard DNS is already configured.

---

# 11. DOMAIN TABLE

Create a table such as:

```text
wedding_domains
```

Minimum fields:

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

Types:

```text
subdomain
custom
```

Custom domains must support ownership/DNS verification.

Do not assume the application controls the DNS of a custom domain owned by the client.

---

# 12. CLOUDFLARE SERVICE

Create an abstraction such as:

```text
CloudflareService
```

It may handle:

* DNS
* domain verification
* custom domains
* cache purging
* zone management

Cloudflare credentials must:

* remain server-side
* never be exposed to the browser
* use least-privilege permissions
* never be written to logs

Cloudflare API integration is not mandatory for every invitation.

---

# 13. TRAEFIK ARCHITECTURE

Use **one shared Traefik instance** for shared deployments.

Do not create:

```text
1 wedding = 1 Traefik
```

Do not create:

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

Traefik is responsible for routing traffic to the Laravel application.

Laravel is responsible for:

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

Important concepts:

```text
Container = Deployment Unit
Tenant / Client / Wedding = Data Boundary
Deployment = Runtime Environment
```

Creating a new client on shared infrastructure must **not** create:

* a new Laravel container
* a new MySQL container
* a new Traefik container

Dedicated deployments may provision isolated Laravel/MySQL/storage/runtime infrastructure when required.

---

# 15. DEDICATED DEPLOYMENT

A dedicated deployment may have:

```text
Laravel
MySQL
Storage
Runtime
Domain
```

separately provisioned.

Use the following deployment state machine:

```text
pending
provisioning
deploying
healthy
failed
suspended
terminated
```

Provisioning must be:

* idempotent
* retryable
* auditable
* secure

Never execute arbitrary Docker or shell commands directly from a browser request.

Use:

```text
DeploymentService
DeploymentProvisioner
Queue / Job
```

Do not expose the Docker socket to Laravel unless absolutely necessary and properly secured.

---

# 16. TEMPLATE ENGINE

Provide at least these templates:

```text
Minang Elegance
Modern Luxury
Floral Romantic
Islamic Elegant
Traditional Nusantara
Minimalist
```

Use a registry/resolver architecture:

```text
TemplateService
TemplateRenderer
```

Do not scatter logic such as:

```php
if ($template === 'minang') ...
elseif ($template === 'modern') ...
```

throughout controllers and views.

Template switching:

```text
Template A
↓
Template B
```

must never destroy wedding data.

Template presentation must remain separated from core wedding data.

---

# 17. MINANG ELEGANCE TEMPLATE

Create a template named:

```text
Minang Elegance
```

with a visual identity inspired by:

* Minangkabau culture
* Suku Minang
* Rumah Gadang
* gonjong
* traditional Minang carvings
* geometric traditional patterns
* songket-inspired textures
* ornamental borders
* elegant dividers
* Rumah Gadang silhouettes

Primary palette:

```text
Deep Maroon / Red
Cream
Muted Gold
Dark Brown
```

The style should feel:

* premium
* elegant
* modern
* traditional
* refined

Do not make it visually overloaded.

Traditional ornaments should be used strategically for:

* framing
* dividers
* accents
* silhouettes
* opening transitions
* subtle background textures

Do not place cultural ornaments in every section.

Terminology must be configurable, for example:

```text
The Wedding of
Baralek Gadang
Walimatul 'Ursy
```

depending on the selected configuration.

Use Minangkabau cultural elements respectfully and avoid excessive or inappropriate decorative usage.

---

# 18. SECTIONS

Provide at least:

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

Every section must support:

* enable/disable
* reorder
* title customization
* content customization
* section-specific settings

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

Use JSON `settings` for flexible presentation/configuration data where appropriate.

Keep core relational data relational.

---

# 19. ANIMATION ENGINE

Create a reusable animation system.

Presets:

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

Global animation presets:

```text
Elegant
Cinematic
Romantic
Traditional
Minimal
None
```

Templates may define a default animation personality.

Clients may override it when permitted.

---

# 20. MINANG ANIMATION PERSONALITY

Default animation mapping:

```text
Opening        → ornament_reveal
Hero           → slow_zoom
Rumah Gadang   → subtle_parallax
Couple         → elegant_fade
Gallery        → stagger
Sections       → ornament_reveal
Closing        → slow_fade
```

Animations must be:

* CSS-first
* transform/opacity based
* lightweight
* mobile-friendly
* non-blocking to first paint

Support:

```text
prefers-reduced-motion
```

Do not use:

* excessive bouncing
* spinning
* infinite floating elements
* excessive parallax
* animations on every element
* heavy JavaScript animation

---

# 21. HARD UI/UX REQUIREMENT — ANTI AI SLOP

This is a **HARD REQUIREMENT**.

Do not create a UI that looks like a generic AI-generated SaaS template.

The application must look like a product designed by a **senior product designer**.

Core philosophy:

> Less but Better.

> Every visual element must have a reason to exist.

Visual priorities:

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

Avoid:

* excessive glassmorphism
* excessive gradients
* colorful blobs
* neon glow
* giant gradient text
* excessive rounded cards
* excessive shadows
* `rounded-full` everywhere
* `rounded-3xl` everywhere
* excessive badges/pills
* excessive icons
* emojis as primary decoration
* random decorative SVGs
* meaningless illustrations
* nested cards
* cards inside cards
* every section wrapped in a Card
* every metric represented as a colorful card
* dashboards containing many colorful metric cards
* random floating elements
* decorative elements without purpose
* excessive animation
* generic AI-generated SaaS visual patterns

---

# 23. SHADCN/UI RULE

shadcn/ui may be used as a **technical component foundation**.

However:

> shadcn/ui is not the visual identity of Ngundang.

Do not simply use default shadcn styling and consider the UI finished.

Allowed components include:

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

Customize their visual language to match Ngundang.

**Component-first does not mean card-first.**

---

# 24. ADMIN UI DESIGN

The admin interface should feel like a:

```text
Professional Product Interface
```

not a:

```text
Generic SaaS Dashboard Template
```

Prioritize:

* whitespace
* strong typography
* clear hierarchy
* clean navigation
* clean tables
* subtle dividers
* restrained borders
* meaningful status indicators
* consistent spacing
* consistent grid

Statistics should be data-oriented.

Do not turn every statistic into a colorful card.

---

# 25. ADMIN TABLE DESIGN

Tables should be:

* clean
* readable
* compact but comfortable
* based on subtle dividers
* clearly hierarchical
* searchable
* filterable
* paginated

Use badges only when they genuinely improve status readability.

Do not turn every data point into a badge or pill.

---

# 26. ADMIN FORM DESIGN

Forms should follow:

```text
Label
Input
Helper Text
Validation
Error Message
```

Do not wrap every input in its own Card.

Group fields by context.

Example:

```text
Wedding Information

Couple Name
Wedding Date
Venue
Address
```

rather than:

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

Use clear:

```text
Primary
Secondary
Tertiary
Destructive
```

Not every button should be:

* filled
* pill-shaped
* `rounded-full`
* colorful

Buttons should communicate clear visual hierarchy.

---

# 28. ICON SYSTEM

Use icons sparingly.

Rules:

* use one consistent icon library
* icons must have meaningful purpose
* do not add icons simply to make the UI look richer
* do not replace useful text with icons
* emojis must not be the primary UI decoration

---

# 29. COLOR SYSTEM

Admin interface:

* neutral base
* one primary accent
* semantic colors only where necessary

Wedding template colors should be intentional.

Minang palette:

```text
Deep Maroon
Cream
Muted Gold
Dark Brown
```

Do not introduce many colors simply to make the design appear “premium”.

---

# 30. TYPOGRAPHY

Typography must be one of the primary visual focal points.

Use at most:

```text
1 primary font
+
1 complementary/display font
```

for a wedding template.

Create a clear hierarchy:

```text
Display
H1
H2
H3
Body
Caption
```

Pay attention to:

* font size
* line height
* letter spacing
* vertical rhythm
* readability

If the design does not feel premium without decorative elements, improve the typography first.

---

# 31. WHITESPACE

Whitespace must be intentional.

Avoid:

* overly dense layouts
* excessive empty space without purpose
* inconsistent spacing

Use a consistent spacing system.

---

# 32. PUBLIC INVITATION DESIGN

The public invitation is not a dashboard.

Do not structure the page as:

```text
Card
Card
Card
Card
Card
```

It should feel like:

```text
Editorial Wedding Website
```

Use:

* strong composition
* typography
* photography
* whitespace
* elegant section transitions
* subtle ornament
* clear content hierarchy

---

# 33. HERO DESIGN

The hero should have:

* strong composition
* couple photography
* clear typography hierarchy
* wedding identity
* subtle visual treatment

Avoid:

* random blobs
* excessive glow
* excessive gradients
* decorative noise
* excessive animation

Photography should be one of the primary focal points.

Use thoughtful editorial crops.

---

# 34. MINANG PUBLIC DESIGN

Minang Elegance should feel:

```text
Minangkabau
Elegant
Modern
Premium
Cultural
Restrained
```

Do not create:

```text
Traditional Overload
```

Traditional ornament should be strategically placed.

Rumah Gadang does not need to appear in every section.

---

# 35. RESPONSIVE DESIGN

Use a true mobile-first approach.

Do not simply shrink desktop layouts.

Design intentionally for:

```text
Mobile
Tablet
Desktop
Large Desktop
```

Mobile experience should receive the highest priority because most wedding invitations will be opened on smartphones.

---

# 36. ACCESSIBILITY

Implement:

* semantic HTML
* keyboard navigation
* visible focus states
* sufficient color contrast
* accessible labels
* ARIA where appropriate
* readable typography
* proper form validation
* reduced motion support
* screen-reader-friendly interactions

---

# 37. MEDIA MANAGEMENT

Support:

* hero images
* couple images
* family images
* gallery
* prewedding images
* video

Features:

* upload
* replace
* delete
* reorder
* alt text
* preview

Media metadata:

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

Use Laravel Storage abstraction.

Production should support:

* S3-compatible object storage
* CDN
* image optimization

Validate:

* MIME type
* file size
* dimensions
* extension

Do not trust file extensions alone.

Use queues for image processing where appropriate.

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

Because browsers may block autoplay:

If autoplay fails, show an elegant control such as:

```text
Putar Musik
```

Playlist features:

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

Do not automatically fetch copyrighted music from the internet.

The client is responsible for the rights/licenses of uploaded music.

---

# 39. GUEST MANAGEMENT

Guest fields:

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

Categories may include:

```text
Keluarga
Teman
Rekan Kerja
VIP
Teman Pengantin Pria
Teman Pengantin Wanita
```

Client must be able to:

* create
* edit
* delete
* move guest between categories
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

After import, show:

```text
Imported
Failed
Duplicate
```

Use batch processing for large imports.

Do not allow large imports to block a normal HTTP request for an unreasonable amount of time.

---

# 41. QR GUEST INVITATION

Support:

* generate
* preview
* download
* print
* copy personal URL
* regenerate token

The QR code should point to the secure personal invitation URL.

---

# 42. SECURE GUEST TOKEN

Personal URL:

```text
/{invitationId}/{invitationSlug}/u/{secureToken}
```

Token must be:

* cryptographically secure
* high entropy
* random
* unique
* revocable
* regeneratable

Never use:

```text
guest_id
name
category_id
phone
```

as the token.

Do not store guest identity in frontend state as the source of truth.

---

# 43. TOKEN SECURITY TEST

If the URL is:

```text
TOKEN_BUDI?name=Andi
```

the application must still resolve and display:

```text
Budi
```

Query parameters must not be able to change guest identity.

Likewise:

```text
?guest_id=123
```

must not change the guest resolved from the secure token.

Token rotation must invalidate the previous token.

The token must be validated against the correct wedding/invitation context, not merely looked up globally.

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

A guest may only modify their own RSVP through the secure invitation token.

Never authorize RSVP updates using a guest ID supplied by the client.

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

Statuses:

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

Admin can:

* search
* filter
* approve
* reject
* hide
* delete
* restore
* bulk moderate

Security requirements:

* validation
* rate limiting
* optional CAPTCHA
* duplicate detection where appropriate
* no arbitrary HTML
* XSS-safe output

When moderation is enabled, only approved messages may appear publicly.

---

# 46. QR CHECK-IN

Routes:

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
Show guest name
↓
Show category
↓
Show max pax
↓
Input actual pax
↓
Confirm
```

If the guest is already checked in:

* show the existing check-in
* require confirmation before updating it

Search fallback:

* name
* phone

If multiple matches exist:

Do not automatically select one.

Show the exact guest choices.

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

Use transactions.

Protect against duplicate/race-condition check-ins.

Every check-in should be auditable.

---

# 48. GENERIC GUEST VISIBILITY ENGINE

Do not build visibility rules only for gifts.

Create a generic visibility engine that can control:

* sections
* events
* gift
* gift methods
* maps
* dress code
* private reception
* family events
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

Guest context must come from the secure token.

Visibility must be resolved server-side.

Never:

```text
send private data to browser
↓
hide it using CSS/JavaScript
```

Private data must never be sent to the browser in the first place.

---

# 49. GIFT SYSTEM

Supported types:

```text
bank_transfer
qris
e_wallet
cash
custom
```

Bank fields:

```text
bank_name
account_number
account_holder
label
description
is_active
sort_order
```

QRIS fields:

```text
image
merchant_name
description
is_active
sort_order
```

Example visibility:

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

Individual guest override has the highest priority.

---

# 50. DASHBOARD

Dashboard should show:

* wedding/invitation
* selected template
* publish status
* total guests
* RSVP statistics
* checked-in statistics
* pending messages
* approved messages

Do not turn the dashboard into a grid of colorful cards.

Use a professional product-oriented information hierarchy.

---

# 51. EDITOR NAVIGATION

Sidebar/navigation:

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

The information architecture should remain easy for non-technical clients to understand.

---

# 52. PREVIEW SYSTEM

Template selection should provide:

* preview
* desktop preview
* tablet preview
* mobile preview

Prefer a:

```text
Live Preview
```

experience where practical without making the editor unnecessarily complex.

---

# 53. DRAFT / PUBLISHED / ARCHIVED

Wedding statuses:

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

Ideally support version/snapshot functionality so significant changes can be restored.

Public routes must only expose published content unless an authorized preview mechanism is used.

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

Canonical URL must reflect:

```text
domain
invitationId
current slug
```

Old slugs must redirect canonically to the current slug.

---

# 55. SECURITY

Implement:

* CSRF protection
* XSS protection
* SQL injection protection
* Policies
* Gates
* tenant isolation
* secure guest tokens
* rate limiting
* secure password hashing
* file validation
* MIME validation
* upload limits
* secure headers where feasible
* authorization for mutations
* HTTPS in production

Never log:

* passwords
* full invitation tokens
* secrets
* API credentials
* sensitive financial information

Health endpoint:

```text
/health
```

must not expose:

* secrets
* credentials
* internal topology
* sensitive database information

Apply rate limiting to at least:

* login
* RSVP
* guest book
* token endpoints
* check-in
* guest search
* domain verification
* deployment endpoints

---

# 56. AUDIT LOG

Audit events should include:

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
media deletion
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

Never store secrets in audit metadata.

---

# 57. CODE ARCHITECTURE

Use a service-oriented architecture.

Suggested services:

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

Use:

* Form Requests
* Policies
* DTOs/value objects where useful
* model scopes
* relationships
* events
* listeners
* jobs
* notifications

Avoid giant controllers.

Avoid giant Blade files.

Do not put business logic inside Blade templates.

---

# 58. DATABASE INDEXING

At minimum, consider indexes for:

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

Add composite indexes and unique constraints according to actual query patterns.

Example:

```text
unique(public_id)
unique(domain)
```

Use appropriate foreign keys.

---

# 59. PERFORMANCE

Prioritize:

* fast first paint
* mobile performance
* optimized images
* lazy loading
* minimal JavaScript
* efficient database queries
* eager loading
* pagination
* caching
* lightweight animation
* non-blocking audio

Avoid N+1 queries.

Use:

* eager loading
* query scopes
* pagination
* optimized queries

Safe candidates for caching:

* template registry
* template assets
* domain mapping
* published invitation configuration

Never cache personalized guest content without a guest-aware cache key.

---

# 60. QUEUES

Use queues for heavy work such as:

```text
image processing
thumbnail generation
QR batch generation
notifications
CSV imports
media optimization
deployment
```

Redis is supported but optional.

The application should have a reasonable fallback for simple/shared-hosting environments, such as database queues.

---

# 61. SCALING

Architecture should support future scaling:

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

Pay particular attention to:

* image processing
* media bandwidth
* audio/video traffic
* database load
* object storage
* CDN traffic

Use object storage + CDN as traffic and media volume increase.

---

# 62. BACKUP

Back up:

```text
MySQL
Object Storage
Application Configuration
Domain Configuration
Deployment Configuration
Secrets
```

Secrets must be managed securely.

Document:

* backup schedule
* restore procedure
* retention policy
* disaster recovery procedure

---

# 63. MONITORING

Monitor:

* application logs
* application errors
* health
* database
* storage
* queues
* deployment status

Provide a secure health endpoint.

---

# 64. TESTING

Create automated tests for:

## Authentication

* role authorization
* unauthorized access

## Tenant Isolation

Wedding A must never access Wedding B.

## Invitation

Test:

* valid public ID
* invalid public ID
* valid slug
* invalid slug
* old slug redirect
* unpublished invitation
* archived invitation
* domain mismatch

## Guest Token

Test:

* valid token
* invalid token
* revoked token
* token manipulation
* token rotation
* wrong wedding token

Explicit test:

```text
TOKEN_BUDI?name=Andi
```

must still resolve to Budi.

## RSVP

Guest can only update their own RSVP.

## Guest Book

Test:

* moderation
* XSS protection
* rate limiting
* invalid input

## Check-in

Test:

* valid QR scan
* invalid token
* double check-in
* race conditions
* guest search
* multiple matching guests

## Visibility

Verify:

```text
Individual Guest Override
>
Category Rule
>
Wedding Default
```

## Gift

Test visibility by guest/category.

## Templates

Test:

* template switching
* template settings
* sections
* animations

## Media

Test:

* tenant isolation
* MIME validation
* size validation
* deletion

## Playlist

Test:

* ordering
* active playlist
* deletion

## Domain

Explicitly test:

```text
Domain A + Invitation B
```

must be rejected.

Example:

```text
bagasrani.ngundang.com/INV-B/andi-sari
```

must not render Invitation B if the domain belongs to Invitation A.

## Deployment

Test:

```text
pending
→ provisioning
→ deploying
→ healthy
```

as well as:

```text
failed
→ retry
```

and:

```text
suspended
terminated
```

---

# 65. FACTORIES & SEEDERS

Create seeders/factories for:

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

Use a valid-looking demo public ID.

---

# 66. ADMIN EXPERIENCE

A client should be able to:

1. Login
2. Create a client
3. Create a wedding
4. Generate a public ID
5. Generate a slug
6. Configure the template
7. Configure sections
8. Upload media
9. Configure music
10. Import guests
11. Generate QR codes
12. Configure RSVP
13. Configure Guest Book
14. Configure Gift
15. Configure Visibility Rules
16. Configure SEO
17. Configure domain
18. Preview
19. Publish

The workflow should feel simple and intuitive.

Users should not need to understand the underlying technical architecture.

---

# 67. SHARED HOSTING SUPPORT

The core application must be capable of running on:

```text
PHP
MySQL
Laravel
```

without requiring:

```text
Docker
Traefik
Redis
Kubernetes
```

as mandatory dependencies.

Document shared-hosting deployment clearly.

---

# 68. DOCKER DEPLOYMENT

Provide Docker configuration for production deployment.

Example:

```text
Cloudflare
    ↓
Traefik
    ↓
Laravel
    ↓
MySQL
    ↓
Redis / Object Storage optional
```

Do not create one container per wedding in a shared deployment.

---

# 69. CUSTOM DOMAIN

Support:

```text
bagasrani.ngundang.com
```

and:

```text
bagasrani.com
```

Custom domains must support:

```text
verification
validation
mapping
SSL/TLS
```

Never claim a custom domain is active until it has actually been verified and configured successfully.

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

Resolver flow:

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

Guest flow:

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

Never send private content to the browser and hide it using:

```css
display: none;
```

or:

```javascript
if (...)
```

The server must determine what content is allowed to be sent.

Guest context:

```text
Wedding
Invitation
Guest
Category
Individual Overrides
```

must produce:

```text
Visible Content
```

before rendering.

---

# 72. UI COMPONENT ARCHITECTURE

Create reusable Blade components.

However, avoid unnecessary over-componentization.

Create a component when it:

* is reused
* has its own behavior
* has semantic responsibility
* provides meaningful consistency

Do not create components merely for trivial one-off wrappers such as:

```html
<div>
```

unless there is a clear architectural reason.

---

# 73. DESIGN SYSTEM

Create design tokens for:

* typography
* spacing
* radius
* borders
* shadows
* colors
* transitions
* breakpoints

Use restrained border radii.

Use subtle shadows.

Prefer borders, tonal contrast, whitespace, and hierarchy over large shadows.

---

# 74. VISUAL QUALITY TEST

Before considering the UI complete, perform the following reviews.

### Test 1

If the interface looks like:

```text
AI-generated SaaS dashboard
```

→ redesign it.

### Test 2

If there are too many:

* cards
* rounded corners
* shadows
* icons
* colors
* gradients
* animations

→ simplify it.

### Test 3

If the typography hierarchy is weak without decorative elements:

→ improve typography.

### Test 4

If ornament attracts more attention than the wedding content:

→ reduce ornament.

### Test 5

If every element moves:

→ remove unnecessary animation.

### Test 6

If mobile feels like a shrunken desktop:

→ redesign the mobile layout.

Target visual result:

```text
Premium
Calm
Refined
Editorial
Professional
```

---

# 75. README

Create comprehensive documentation covering:

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

The Cloudflare README must explicitly explain:

```text
*.ngundang.com
```

and why individual DNS records are not required for every invitation subdomain.

The shared-hosting README must explain that:

```text
Docker / Traefik / Redis
```

are not mandatory dependencies.

The dedicated deployment documentation must explain controlled provisioning.

---

# 76. ENVIRONMENT CONFIGURATION

Provide a complete `.env.example`.

At minimum:

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

Never commit secrets.

---

# 77. DEVELOPMENT PRINCIPLES

Do not build features in isolation without considering the complete system.

Every feature must consider:

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

For example, Guest CRUD is not simply:

```text
CRUD database
```

It must also consider:

```text
Tenant isolation
Authorization
Validation
Audit logging
Token lifecycle
QR generation
Visibility
RSVP
Check-in
```

---

# 78. IMPLEMENTATION ORDER

Build progressively.

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
* public ID
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
* uploads
* optimization
* gallery
* video
* music
* playlist

## Phase 5 — Guest System

* categories
* guest CRUD
* CSV import
* secure tokens
* QR codes

## Phase 6 — RSVP / Guest Book

* RSVP
* guest book
* moderation
* anti-spam

## Phase 7 — Visibility / Gift

* generic visibility engine
* gift methods
* guest/category overrides

## Phase 8 — Check-in

* scanner
* search
* check-in
* audit

## Phase 9 — Domains

* subdomains
* wildcard DNS architecture
* custom domains
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
* queues
* testing
* monitoring
* backup
* documentation

---

# 79. DEFINITION OF DONE

A feature is not considered complete merely because its endpoint or UI exists.

Every feature should satisfy, where applicable:

```text
✓ Database
✓ Model
✓ Validation
✓ Authorization
✓ Tenant Isolation
✓ Service Layer
✓ UI
✓ Error Handling
✓ Audit Logging
✓ Automated Tests
✓ Documentation
```

---

# 80. FINAL PRODUCT STANDARD

The final Ngundang product should feel like:

> A serious, premium, production-ready wedding invitation product.

It must not feel like:

> A Laravel demo template.

It must not feel like:

> A generic SaaS dashboard.

It must not feel like:

> An AI-generated interface overloaded with cards, gradients, shadows, badges, icons, and animations.

The public invitation should be:

```text
Elegant
Editorial
Emotional
Fast
Mobile-first
Culturally respectful
Premium
```

The admin should be:

```text
Clean
Efficient
Professional
Consistent
Accessible
Scalable
```

The architecture should be:

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

# FINAL IMPLEMENTATION INSTRUCTION

Start implementation based on this specification.

Do not remove important requirements.

When multiple implementation approaches are possible, choose the approach that is:

1. most maintainable
2. most secure
3. simplest
4. scalable
5. easy to test
6. easy to deploy
7. reusable

Do not add complexity merely to make the architecture look sophisticated.

**Build the simplest architecture that can reliably support the full product.**

For UI/UX:

> **Less but Better.**

> **Every visual element must have a reason to exist.**

If any design looks like a generic AI-generated SaaS template, **do not keep it — redesign it to be cleaner, more minimal, more editorial, and more professional.**
