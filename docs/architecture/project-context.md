# NasDan (MyDay) — Full Project Context

> This document is a complete reference for the NasDan/MyDay wedding invitation platform, intended to give AI assistants full context about the project's architecture, features, codebase, and design decisions.

> **Docs index:** [../README.md](../README.md) · **Product / visual design:** [../design/project-design.md](../design/project-design.md)

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Business Model & Target Market](#2-business-model--target-market)
3. [Application Surfaces](#3-application-surfaces)
4. [Tech Stack](#4-tech-stack)
5. [Dependencies](#5-dependencies)
6. [Directory Structure](#6-directory-structure)
7. [Database Schema](#7-database-schema)
8. [Eloquent Models & Enums](#8-eloquent-models--enums)
9. [Routes & Endpoints](#9-routes--endpoints)
10. [Authentication & Authorization](#10-authentication--authorization)
11. [Features In Detail](#11-features-in-detail)
12. [Livewire Components](#12-livewire-components)
13. [Filament Panels](#13-filament-panels)
14. [Jobs & Notifications](#14-jobs--notifications)
15. [Invitation Themes, Templates & Reveals](#15-invitation-themes-templates--reveals)
16. [Push Notifications (Web Push / VAPID)](#16-push-notifications-web-push--vapid)
17. [Referral / Affiliate Program](#17-referral--affiliate-program)
18. [Analytics & Link Tracking](#18-analytics--link-tracking)
19. [Localization](#19-localization)
20. [Integrations](#20-integrations)
21. [Environment Variables & Configuration](#21-environment-variables--configuration)
22. [Build, Development & Deployment](#22-build-development--deployment)
23. [Testing](#23-testing)
24. [Key Architectural Decisions](#24-key-architectural-decisions)
25. [Known Limitations & Future Opportunities](#25-known-limitations--future-opportunities)

---

## 1. Project Overview

**NasDan** (brand name; repo folder: `myday`) is a **digital wedding invitation SaaS platform** built for couples primarily in Bosnia and Herzegovina, and the broader Balkans/European market.

The platform allows couples to:

- Create beautiful, personalized digital wedding invitations
- Manage their guest list with RSVP tracking
- Share invitations via unique personal or public links
- Receive guest messages (text, audio, and photos)
- Send web push notifications directly to guests' devices
- Track how many people opened their invitation and from what channels
- Earn money by referring other couples (affiliate program)

The name "NasDan" is a portmanteau of "naš dan" (Bosnian for "our day"). The product is multilingual: Bosnian (primary), English, and German.

---

## 2. Business Model & Target Market

- **Target:** Couples getting married in Bosnia/Herzegovina and neighboring countries (Croatia, Serbia, etc.)
- **Pricing tiers:** Basic, Plus, Premium, Deluxe (displayed on landing page — informational only, no self-serve checkout)
- **Activation flow:** Couples sign up for free, invitation starts **inactive**; an admin manually activates it (implied vetting or payment step outside the app)
- **Referral income:** Registered couples get a unique referral link; when a new couple signs up through it, the referrer earns a configurable fee percentage (default 10%) — paid out manually via PayPal or bank transfer
- **No Stripe or payment gateway** is currently integrated in the codebase
- Admin is the sole human operator managing activations and payouts

---

## 3. Application Surfaces

There are three distinct user-facing surfaces:

### 3a. Marketing Landing Page (`/`)
- Full single-page marketing website
- Sections: Hero, How It Works, Benefits, Live Demo, Guest Interaction, Pricing, Contact
- Enquiry/contact form (stored in DB, no email to admin by default)
- Live demo invitations embedded (Islamic & Christian wedding themes)
- Language switcher (bs/en/de)

### 3b. Couple Dashboard (`/app`)
- Powered by **Filament 4** (couple panel)
- Couples manage their entire wedding invitation here
- Resources: MyWedding, GuestMessages, PushNotifications
- Pages: Dashboard, Referrals, EditProfile
- Login at `/app/login`

### 3c. Guest Invitation Pages (`/e/{slug}` and `/e/{slug}/{token}`)
- Fully public or token-protected invitation pages
- Powered by **Livewire 3** + Blade view components
- Guests can RSVP, view schedule/location/gallery, listen to music, leave messages, subscribe to push notifications
- Routes:
  - `/e/{slug}` — public link (if `link_mode = public`)
  - `/e/{slug}/{token}` — personal link for a specific guest (always accessible)
  - `/e/{slug}/{token}/contact` — guest message page
  - `/e/{slug}/{token}/push` — push notification opt-in page

---

## 4. Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | 8.3+ |
| Framework | Laravel | ^13.8 |
| Admin/App UI | Filament | ^4.11 |
| Reactive UI | Livewire | ^3.5 |
| JavaScript | Alpine.js | (via Livewire) |
| CSS framework | Tailwind CSS | ^4.0 |
| Build tool | Vite | ^8.0 |
| Database (dev) | SQLite | (default) |
| Database (prod) | MySQL / PostgreSQL | (Laravel standard) |
| Queue driver | Database | (default) |
| Cache driver | Database | (default) |
| Session driver | Database | |
| File storage (dev) | Local `public` disk | |
| File storage (prod) | AWS S3 | via `league/flysystem-aws-s3-v3` |
| Email delivery | Resend | via `resend/resend-laravel` |
| PDF generation | Spatie Laravel PDF + DomPDF | |
| QR codes | chillerlan/php-qrcode | ^5.0 |
| Web Push | laravel-notification-channels/webpush | ^11.0 |
| User-agent parsing | jenssegers/agent | ^2.6 |
| Testing | PHPUnit | ^12.5 |
| Deployment target | Laravel Cloud | |

**Architecture style:** Monolith — no separate API or SPA. Server-rendered Blade + Livewire + Filament.

---

## 5. Dependencies

### PHP (composer.json)

**Production dependencies:**

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^13.8 | Core Laravel framework |
| `filament/filament` | ^4.11 | Admin panel + couple dashboard |
| `livewire/livewire` | ^3.5 | Reactive full-stack components |
| `laravel-notification-channels/webpush` | ^11.0 | VAPID web push notifications |
| `resend/resend-laravel` | ^1.4 | Transactional email via Resend |
| `spatie/laravel-pdf` | ^2.12 | PDF generation (brochures) |
| `dompdf/dompdf` | ^3.1 | PDF rendering engine |
| `chillerlan/php-qrcode` | ^5.0 | QR code generation |
| `league/flysystem-aws-s3-v3` | ^3.0 | S3 file storage driver |
| `jenssegers/agent` | ^2.6 | User agent / device detection |
| `laravel/tinker` | ^3.0 | Laravel REPL |

**Development dependencies:**

| Package | Version | Purpose |
|---------|---------|---------|
| `fakerphp/faker` | ^1.23 | Test data generation |
| `laravel/pail` | ^1.2 | Log tailing |
| `laravel/pint` | ^1.13 | Code style fixer |
| `mockery/mockery` | ^1.6 | Mocking library |
| `nunomaduro/collision` | ^8.1 | Better error reporting |
| `phpunit/phpunit` | ^12.5 | Testing framework |

**Composer scripts:**
- `composer dev` — Starts `artisan serve`, `queue:listen`, `pail` (logs), and `vite` concurrently
- `composer setup` — Full install: composer, migrate, npm build
- `composer test` — Runs PHPUnit

### JavaScript (package.json)

All JS deps are dev-only (build tools only):

| Package | Version | Purpose |
|---------|---------|---------|
| `vite` | ^8.0.0 | Build tool / dev server |
| `laravel-vite-plugin` | ^3.1 | Laravel + Vite integration |
| `@tailwindcss/vite` | ^4.0.0 | Tailwind CSS 4 Vite plugin |
| `tailwindcss` | ^4.0.0 | Utility CSS framework |
| `concurrently` | ^9.0.1 | Parallel process runner (for `composer dev`) |

---

## 6. Directory Structure

```
myday/
├── app/
│   ├── Actions/                        # Action classes (push subscription handlers)
│   │   ├── StorePushSubscriptionAction.php
│   │   └── StoreUserPushSubscriptionAction.php
│   ├── Console/
│   │   └── Commands/
│   │       └── GenerateReferralAccounts.php    # Artisan: referrals:generate-accounts
│   ├── Filament/
│   │   ├── App/                        # Couple panel (/app)
│   │   │   ├── Pages/
│   │   │   │   ├── AppDashboard.php
│   │   │   │   ├── EditProfile.php
│   │   │   │   └── ReferralsPage.php
│   │   │   ├── Resources/
│   │   │   │   ├── GuestMessagesResource/
│   │   │   │   ├── MyWeddingResource/      # Manages WeddingEvent + relations
│   │   │   │   └── PushNotificationsResource/
│   │   │   ├── Schemas/                # Reusable Filament form schemas
│   │   │   └── Widgets/
│   │   │       ├── MyReferralsWidget.php
│   │   │       ├── RecentGuestMessagesWidget.php
│   │   │       ├── ReferralPayoutsWidget.php
│   │   │       ├── UserPushDevicesWidget.php
│   │   │       ├── VisitChartWidget.php
│   │   │       └── VisitStatsWidget.php
│   │   ├── Resources/                  # Admin panel (/admin)
│   │   │   ├── GuestResource/
│   │   │   ├── ReferralPayoutResource/
│   │   │   ├── UserResource/
│   │   │   └── WeddingEventResource/
│   │   ├── Imports/
│   │   │   └── GuestImport.php         # CSV guest importer
│   │   └── Widgets/
│   │       ├── PlatformStatsWidget.php
│   │       └── PlatformVisitChartWidget.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DownloadBrochureController.php
│   │   │   ├── DownloadGuestPhotosController.php
│   │   │   ├── DownloadReferralQrCodeController.php
│   │   │   ├── ReferralLinkController.php
│   │   │   ├── VerifyEmailController.php
│   │   │   └── WeddingEventCalendarController.php
│   │   └── Middleware/
│   │       └── SetAppLocale.php        # Sets locale from session
│   ├── Jobs/
│   │   ├── RecordLinkVisit.php
│   │   ├── SendCoupleRsvpNotificationJob.php
│   │   └── SendGuestPushNotificationsJob.php
│   ├── Livewire/
│   │   ├── ContactForm.php             # Landing enquiry form
│   │   ├── GuestContactPage.php        # Guest messages (text/audio/photo)
│   │   ├── GuestPushNotificationsPage.php
│   │   ├── InvitationPage.php          # Main invitation page
│   │   ├── LandingPage.php             # Marketing homepage
│   │   ├── VerifyEmailNotice.php
│   │   └── WeddingOnboarding.php       # 3-step signup wizard
│   ├── Models/
│   │   ├── Enquiry.php
│   │   ├── EventPhoto.php
│   │   ├── Guest.php
│   │   ├── GuestMessage.php
│   │   ├── LinkVisit.php
│   │   ├── PushNotificationLog.php
│   │   ├── Referral.php
│   │   ├── ReferralPayout.php
│   │   ├── ScheduleItem.php
│   │   ├── User.php
│   │   └── WeddingEvent.php
│   ├── Notifications/
│   │   ├── CoupleRsvpPushNotification.php
│   │   ├── GuestPushNotification.php
│   │   └── NewGuestMessageNotification.php
│   ├── Observers/
│   │   └── GuestMessageObserver.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AppPanelProvider.php        # Filament couple panel config
│   │   └── AdminPanelProvider.php      # Filament admin panel config
│   ├── Support/                        # Helper classes
│   │   ├── Clipboard.php
│   │   ├── Locale.php
│   │   ├── MediaDisk.php
│   │   └── MessengerLinks.php          # WhatsApp/Viber/Telegram deep link generators
│   └── Traits/
│       └── Referrable.php
│
├── bootstrap/
│   ├── app.php                         # Laravel 13 bootstrap
│   └── providers.php
│
├── config/
│   ├── app.php                         # supported_locales, default_locale
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php                 # local, public, s3 disks
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── referral.php                    # fee %, cookie name, code format
│   ├── services.php                    # Resend, Postmark keys
│   ├── session.php
│   └── webpush.php                     # VAPID keys, push subscription table
│
├── database/
│   ├── factories/
│   │   ├── GuestFactory.php
│   │   └── WeddingEventFactory.php
│   ├── migrations/                     # 32 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── WeddingEventSeeder.php
│
├── lang/
│   ├── bs/                             # Bosnian translations
│   ├── en/                             # English translations
│   └── de/                             # German translations
│
├── public/
│   ├── icons/                          # Brand logos / favicons
│   ├── js/filament/                    # Published Filament JS assets
│   ├── build/                          # Compiled Vite assets (gitignored)
│   └── sw.js                           # Service worker for web push
│
├── resources/
│   ├── css/
│   │   ├── app.css                     # Tailwind 4 main stylesheet
│   │   └── filament/app/theme.css      # Custom Filament theme
│   ├── js/
│   │   ├── app.js                      # Alpine.js bootstrap, countdown, helpers
│   │   └── invitation.js               # Invitation-specific JS
│   └── views/
│       ├── components/                 # Blade view components
│       │   ├── invitation/             # All invitation sub-components
│       │   │   ├── reveals/            # envelope, wax-seal, curtain, storybook
│       │   │   ├── templates/          # classic, editorial, story
│       │   │   ├── hero.blade.php
│       │   │   ├── countdown.blade.php
│       │   │   ├── schedule.blade.php
│       │   │   ├── location.blade.php
│       │   │   ├── gallery.blade.php
│       │   │   ├── rsvp.blade.php
│       │   │   ├── music-player.blade.php
│       │   │   └── push-enable.blade.php
│       │   └── landing/                # Landing page sections
│       │       ├── hero.blade.php
│       │       ├── how-it-works.blade.php
│       │       ├── benefits.blade.php
│       │       ├── demo.blade.php
│       │       ├── guest-interaction.blade.php
│       │       ├── pricing.blade.php
│       │       ├── contact.blade.php
│       │       └── footer.blade.php
│       ├── livewire/                   # Livewire component views
│       ├── pdf/                        # PDF templates (brochures)
│       └── layouts/                    # App, invitation, auth layouts
│
├── routes/
│   ├── web.php                         # All HTTP routes
│   └── console.php                     # Artisan schedule
│
├── tests/
│   ├── Feature/
│   │   ├── BrochureDownloadTest.php
│   │   ├── ReferralQrCodeDownloadTest.php
│   │   └── ExampleTest.php
│   └── Unit/
│       └── ExampleTest.php
│
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── vite.config.js
└── .env.example
```

---

## 7. Database Schema

The application has **32 migrations** creating the following tables:

### `users`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint UNSIGNED | Primary key |
| `name` | varchar | Full name |
| `email` | varchar | Unique |
| `email_verified_at` | timestamp | Nullable |
| `password` | varchar | Bcrypt |
| `is_admin` | boolean | Default false |
| `locale` | varchar | bs/en/de |
| `referral_fee_percentage` | decimal(5,2) | Nullable, default from config (10%) |
| `paypal_email` | varchar | Nullable, for manual payout |
| `bank_account_info` | text | Nullable |
| `remember_token` | varchar | |
| `created_at`, `updated_at` | timestamp | |

### `wedding_events`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint UNSIGNED | Primary key |
| `user_id` | FK → users | Nullable (admin events have no user) |
| `slug` | varchar | Unique URL identifier (e.g. `ana-i-marko`) |
| `bride_name` | varchar | |
| `groom_name` | varchar | |
| `wedding_date` | datetime | |
| `location_name` | varchar | Nullable |
| `location_address` | varchar | Nullable |
| `location_lat` | decimal | Nullable |
| `location_lng` | decimal | Nullable |
| `theme` | varchar | See InvitationTheme enum |
| `template` | varchar | See InvitationTemplate enum |
| `reveal_animation` | varchar | Nullable, see InvitationReveal enum |
| `link_mode` | varchar | `public` or `token_only` |
| `music_url` | varchar | Nullable, YouTube URL |
| `hero_image` | varchar | Nullable, file path |
| `rsvp_deadline` | date | Nullable |
| `is_active` | boolean | Default true (admin sets false until activated) |
| `is_demo` | boolean | For landing page demo events |
| `send_message` | text | Invite message template with `{name}` and `{link}` placeholders |
| `motto` | varchar | Nullable, displayed on invitation |
| `created_at`, `updated_at` | timestamp | |

### `guests`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint UNSIGNED | Primary key |
| `wedding_event_id` | FK → wedding_events | Cascade delete |
| `name` | varchar | |
| `email` | varchar | Nullable |
| `phone` | varchar | Nullable |
| `token` | varchar(32) | Unique, auto-generated for personal links |
| `rsvp_status` | varchar | Nullable; `yes` or `no` |
| `rsvp_responded_at` | timestamp | Nullable |
| `rsvp_manual_override` | boolean | If true, couple has manually set status |
| `rsvp_note` | text | Nullable, guest note with RSVP |
| `plus_one_allowed` | boolean | Whether guest can bring a +1 |
| `plus_one_name` | varchar | Nullable, +1's name if confirmed |
| `invite_sent_at` | timestamp | Nullable |
| `invite_platform` | varchar | Nullable, see InvitePlatform enum |
| `deleted_at` | timestamp | Soft deletes |
| `created_at`, `updated_at` | timestamp | |

### `schedule_items`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `wedding_event_id` | FK | |
| `time` | varchar | e.g. "14:00" |
| `title` | varchar | Event name |
| `description` | text | Nullable |
| `sort_order` | integer | |

### `event_photos`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `wedding_event_id` | FK | |
| `path` | varchar | File path |
| `title` | varchar | Nullable |
| `sort_order` | integer | |

### `guest_messages`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `wedding_event_id` | FK | |
| `guest_id` | FK → guests | Nullable (for anonymous submissions) |
| `sender_name` | varchar | |
| `type` | varchar | `text`, `audio`, `photo` |
| `content` | text | For text messages |
| `file_path` | varchar | Nullable, single file |
| `file_paths` | JSON | Nullable, multiple photo paths |
| `seen_at` | timestamp | Nullable, when couple viewed it |
| `created_at`, `updated_at` | timestamp | |

### `enquiries`
Landing page contact form submissions:
`name`, `email`, `phone`, `groom_name`, `bride_name`, `wedding_date`, `theme`, `notes`, timestamps

### `link_visits`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `wedding_event_id` | FK | |
| `guest_id` | FK → guests | Nullable (null for public link visits) |
| `link_type` | varchar | `public` or `personal` |
| `ip_hash` | varchar | Hashed for privacy |
| `user_agent` | text | Raw UA string |
| `referer` | varchar | Nullable, HTTP referer |
| `device_type` | varchar | mobile/tablet/desktop |
| `browser` | varchar | |
| `os` | varchar | |
| `visited_at` | timestamp | |

### `push_subscriptions`
Polymorphic (`subscribable_type`, `subscribable_id`):
`endpoint`, `public_key`, `auth_token`, `content_encoding`, `device_label`, timestamps

### `push_notification_logs`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `wedding_event_id` | FK | |
| `title` | varchar | Notification title |
| `body` | text | Notification body |
| `recipient_type` | varchar | `all`, `unanswered`, `selected` |
| `sent_to_count` | integer | How many endpoints were targeted |
| `status` | varchar | `pending`, `sent`, `failed` |
| `failed_reason` | text | Nullable |
| `sent_at` | timestamp | Nullable |

### `referrals`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `user_id` | FK → users | The new user who was referred |
| `referrer_id` | FK → users | Nullable, who referred them |
| `referral_code` | varchar | Unique code (e.g. `_ab12cd34`) |

### `referral_payouts`
| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | PK |
| `referrer_id` | FK → users | Who gets paid |
| `amount` | decimal | |
| `currency` | varchar | Default EUR |
| `period` | varchar | e.g. "2026-Q1" |
| `status` | varchar | `pending` or `paid` |
| `paid_at` | timestamp | Nullable |
| `payment_proof` | varchar | Nullable, screenshot path |
| `payment_link` | varchar | Nullable |
| `notes` | text | Nullable |

### Laravel System Tables
`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, `notifications` (for DB notification channel)

---

## 8. Eloquent Models & Enums

### Models

**`User`**
- Implements `MustVerifyEmail`, `HasPushSubscriptions` (from webpush)
- Uses `Referrable` trait
- Relationships: `hasOne(WeddingEvent)`, `hasOne(Referral)`, `hasMany(ReferralPayout)` (as referrer)
- Methods: `canAccessPanel(Panel $panel)` — admin vs app routing

**`WeddingEvent`**
- Belongs to `User`
- Has many: `Guest`, `ScheduleItem`, `EventPhoto`, `GuestMessage`, `LinkVisit`, `PushNotificationLog`
- Hero image stored on `MEDIA_DISK`
- Cast: theme, template, reveal as enums; `is_active`, `is_demo` as booleans

**`Guest`**
- Belongs to `WeddingEvent`
- Has many: `GuestMessage`, `LinkVisit`, `PushSubscription` (polymorphic)
- Uses `SoftDeletes`
- `token` auto-generated (32 chars) on creation
- `HasPushSubscriptions` (webpush package)

**`GuestMessage`**
- Belongs to `WeddingEvent`, belongs to `Guest` (nullable)
- Observed by `GuestMessageObserver` (triggers push notification to couple)

**`Referral`**
- Belongs to `User` (the referred user)
- Belongs to `User` as referrer (`referrer`)

**`ReferralPayout`**
- Belongs to `User` (referrer)

### PHP Enums

| Enum | Values |
|------|--------|
| `InvitationTheme` | `amber-gold`, `royal-wedding`, `lavender-dream`, `winter-magic`, `pearl-white`, `dusty-rose` |
| `InvitationTemplate` | `classic`, `editorial`, `story` |
| `InvitationReveal` | `envelope`, `wax-seal`, `curtain`, `storybook` |
| `LinkMode` | `public`, `token_only` |
| `LinkType` | `public`, `personal` |
| `RsvpStatus` | `yes`, `no` |
| `GuestMessageType` | `text`, `audio`, `photo` |
| `InvitePlatform` | `whatsapp`, `viber`, `telegram`, `messenger`, `facebook`, `manual` |
| `PushNotificationRecipientType` | `all`, `unanswered`, `selected` |
| `PushNotificationStatus` | `pending`, `sent`, `failed` |
| `ReferralPayoutStatus` | `pending`, `paid` |

---

## 9. Routes & Endpoints

All routes are in `routes/web.php`. There is no separate `api.php`.

### Public (Unauthenticated) Routes

| Method | Path | Handler | Name |
|--------|------|---------|------|
| GET | `/` | `LandingPage` (Livewire) | `home` |
| POST | `/lang/{locale}` | Closure | `lang.switch` |
| GET | `/ref/{code}` | `ReferralLinkController` | `referral.link` |
| GET | `/onboarding` | `WeddingOnboarding` (Livewire) | `onboarding` |
| GET | `/login` | Redirect → `/app/login` | `login` |
| GET | `/email/verify/{id}/{hash}` | `VerifyEmailController` | `verification.verify` |
| GET | `/sitemap.xml` | Closure → XML response | `sitemap` |
| GET | `/robots.txt` | Closure → text response | `robots` |
| GET | `/e/{slug}/calendar.ics` | `WeddingEventCalendarController` | `invitation.ics` |
| GET | `/e/{slug}/{token}/manifest.webmanifest` | `InvitationManifestController` | `invitation.manifest` |
| GET | `/e/{slug}/{token}/contact` | `GuestContactPage` (Livewire) | `invitation.contact.guest` |
| GET | `/e/{slug}/{token}/push` | `GuestPushNotificationsPage` (Livewire) | `invitation.push.guest` |
| GET | `/e/{slug}` | `InvitationPage` (Livewire) | `invitation.show` |
| GET | `/e/{slug}/{token}` | `InvitationPage` (Livewire) | `invitation.guest` |
| POST | `/push/subscribe/{guest:token}` | `StorePushSubscriptionAction` | `push.subscribe` |

### Authenticated Routes (middleware: `auth`)

| Method | Path | Middleware | Handler |
|--------|------|------------|---------|
| GET | `/onboarding/verify-email` | auth | `VerifyEmailNotice` (Livewire) |
| POST | `/email/verification-notification` | auth, throttle:6,1 | Closure (resend verification email) |
| GET | `/app-api/guest-messages/photos/download/{message?}` | auth, verified | `DownloadGuestPhotosController` |
| GET | `/app-api/referrals/qr-code/download/{format?}` | auth, verified | `DownloadReferralQrCodeController` |
| GET | `/app-api/referrals/brochure/download` | auth, verified | `DownloadBrochureController` |
| POST | `/push/user/subscribe` | auth, verified | `StoreUserPushSubscriptionAction` |

### Filament Auto-Registered Panels

| Panel | Base Path | Access Guard |
|-------|-----------|-------------|
| Couple app | `/app` | Non-admin, email verified |
| Admin | `/admin` | `is_admin = true` |

### Health Check

| Method | Path | Notes |
|--------|------|-------|
| GET | `/up` | Laravel default health check |

---

## 10. Authentication & Authorization

- **Auth driver:** Laravel session-based (`web` guard)
- **Auth provider:** Eloquent `User` model
- **Login pages:** `/app/login` (couples), `/admin/login` (admins) — Filament handles these
- **No registration page** — Couples sign up only through the `/onboarding` wizard
- **Email verification required** — `User` implements `MustVerifyEmail`
  - Unverified users redirected to `/onboarding/verify-email`
  - Verification link throttled (6 requests per minute)
  - Signed route with ID + hash
- **Password reset:** Standard Laravel password reset table and flow
- **Panel access:**
  ```php
  // User::canAccessPanel()
  admin panel  → $user->is_admin === true
  app panel    → $user->is_admin === false
  ```
- **No OAuth / Social login**
- **No API tokens / Laravel Sanctum**
- **Session:** Database driver, 120-minute lifetime
- **Couple panel middleware:** `auth`, `verified` — must be logged in AND have verified email
- **Guest invitation pages:** No auth required (public or token-based)

---

## 11. Features In Detail

### 11.1 Wedding Onboarding (3-Step Wizard)

Route: `GET /onboarding` → Livewire `WeddingOnboarding`

Step 1 — Couple details:
- Bride name, groom name, wedding date
- Choose theme (visual preview)
- Choose template

Step 2 — Account creation:
- Email, password, name
- Referral code attribution from cookie (`nasdan_ref`)

Step 3 — (Implicit) Email verification prompt

On completion:
- Creates `User` record
- Creates `WeddingEvent` with `is_active = false`
- Logs in the user
- Creates a `Referral` record (with referrer if cookie present)
- Redirects to verification page

### 11.2 Digital Invitation Page

Route: `/e/{slug}` (public) or `/e/{slug}/{token}` (personal)
Component: `InvitationPage` (Livewire)

Features:
- Theme + template applied from `WeddingEvent` settings
- Reveal animation (plays once per session)
- Hero section with couple photo, names, wedding date
- Countdown timer (Alpine.js, live)
- Event schedule timeline
- Location with map coordinates (Google Maps link)
- Photo gallery (lightbox)
- Background music player (YouTube embed, auto-plays on interact)
- RSVP form:
  - Yes/No radio
  - Plus-one section (if `plus_one_allowed`)
  - Notes/message field
  - Guests can edit their RSVP
  - Anonymous RSVP on public links creates a guest record on the fly
- Save-the-date: Google Calendar link + `.ics` download
- Push notification opt-in button → redirects to `/push` page
- Locale switcher
- Demo mode: switches theme/template/reveal in real time (for landing page demo)

### 11.3 Guest Management (Couple Dashboard)

Path: `/app/moje-vjencanje` → MyWeddingResource → Guests relation manager

Features:
- Create/edit/delete guests (name, email, phone)
- Toggle plus-one allowed
- Manual RSVP override
- Mark invite as sent (platform tracking: WhatsApp, Viber, Telegram, Facebook Messenger, Manual)
- Copy personalized invite message (template fills `{name}` and `{link}`)
- Direct messenger deep links (opens WhatsApp/Viber/etc. with pre-filled message)
- CSV import (bulk upload via `GuestImport`)
- View RSVP status, notes, plus-one name

### 11.4 Guest Messages

Route: `/e/{slug}/{token}/contact` → `GuestContactPage`
Dashboard: `/app/poruke-gostiju` → `GuestMessagesResource`

Types:
- **Text:** typed message from guest
- **Audio:** recorded in-browser (MediaRecorder API, uploaded as audio file)
- **Photo:** up to 10 photos uploaded

Photo upload constraint: Only allowed from the **wedding day** through **30 days after**.

On new message → `GuestMessageObserver` fires `NewGuestMessageNotification` push to couple.

Couple views messages in Filament, marks as seen.

### 11.5 RSVP System

- Guests click Yes/No on invitation page
- Optional plus-one name if allowed
- Optional note
- RSVP stored as `rsvp_status` on `Guest` model with `rsvp_responded_at` timestamp
- Couple can manually override RSVP in dashboard
- RSVP triggers push notification to couple (via `SendCoupleRsvpNotificationJob`)
- Anonymous RSVP on public invitation creates a new `Guest` record automatically

### 11.6 Push Notifications (Couple Sending to Guests)

Path: `/app/push-notifications` → `PushNotificationsResource`

Couple can:
- Write notification title + body
- Choose recipients: All guests / Unanswered only / Selected guests
- Send → creates `PushNotificationLog` → dispatches `SendGuestPushNotificationsJob`
- Job iterates all targeted guests with active push subscriptions and sends via VAPID

Guest opt-in:
- Route: `/e/{slug}/{token}/push` → `GuestPushNotificationsPage`
- Requests browser push permission
- Stores subscription via `POST /push/subscribe/{guest:token}`
- Service worker: `/public/sw.js` handles push event display and notification click

### 11.7 Link Visit Tracking

Every page view of an invitation (`/e/{slug}` or `/e/{slug}/{token}`) dispatches `RecordLinkVisit` job.

Recorded data: `wedding_event_id`, `guest_id` (if personal link), `link_type`, `ip_hash`, `user_agent`, `device_type`, `browser`, `os`, `referer`, `visited_at`

Displayed in couple dashboard via:
- `VisitStatsWidget` — totals (public vs personal, device breakdown)
- `VisitChartWidget` — timeline chart

Admin dashboard shows platform-wide stats via `PlatformVisitChartWidget`.

### 11.8 Save The Date

- Google Calendar link generated dynamically from wedding event data
- `.ics` file downloadable at `GET /e/{slug}/calendar.ics`

### 11.9 PWA Support

- Each guest's personal invitation has a `manifest.webmanifest` at `/e/{slug}/{token}/manifest.webmanifest`
- Includes wedding couple names, colors (based on theme), icons
- Allows "Add to Home Screen" on mobile

---

## 12. Livewire Components

| Component Class | View Path | Purpose |
|-----------------|-----------|---------|
| `LandingPage` | `livewire/landing-page` | Full marketing homepage, loads demo invitation |
| `ContactForm` | `livewire/contact-form` | Enquiry form on landing page |
| `WeddingOnboarding` | `livewire/wedding-onboarding` | 3-step signup wizard |
| `VerifyEmailNotice` | `livewire/verify-email-notice` | Post-signup email verification prompt |
| `InvitationPage` | `livewire/invitation-page` | Main guest invitation (all features) |
| `GuestContactPage` | `livewire/guest-contact-page` | Guest message submission (text/audio/photo) |
| `GuestPushNotificationsPage` | `livewire/guest-push-notifications-page` | Push notification opt-in for guests |

Livewire 3 features used:
- `wire:model`, `wire:click`, `wire:submit`
- Lazy loading, deferred loading
- File uploads (Livewire file upload with temp storage)
- Polling (none — push-based instead)
- URL query params
- Persistent component state

---

## 13. Filament Panels

### Couple Panel (`/app`) — `AppPanelProvider`

**Resources:**
- `MyWeddingResource` — Edit wedding details, manage guests (with CSV import), schedule, photos
- `GuestMessagesResource` — View/manage guest messages, mark as seen
- `PushNotificationsResource` — Create and send push notifications

**Pages:**
- `AppDashboard` — Main dashboard with widgets
- `EditProfile` — User profile, PayPal/bank info for referral payouts
- `ReferralsPage` — Referral code, stats, earnings, payout history + QR code/brochure downloads

**Widgets (Dashboard):**
- `WeddingOverviewWidget` — Wedding summary stats (guests, RSVPs, messages)
- `VisitStatsWidget` — Visit totals and device breakdown
- `VisitChartWidget` — Visits over time chart
- `RecentGuestMessagesWidget` — Last 5 messages
- `MyReferralsWidget` — Referral overview
- `ReferralPayoutsWidget` — Payout history
- `UserPushDevicesWidget` — Couple's own push subscriptions (devices)

### Admin Panel (`/admin`) — `AdminPanelProvider`

**Resources:**
- `UserResource` — Full user management (activate, set admin, manage referral fee)
- `WeddingEventResource` — Manage all wedding events (activate/deactivate, view all sub-resources)
- `GuestResource` — Platform-wide guest management
- `ReferralPayoutResource` — Create/manage payouts to referrers

**Relation Managers (on WeddingEventResource):**
Guests, ScheduleItems, EventPhotos, GuestMessages, LinkVisits

**Widgets:**
- `PlatformStatsWidget` — Total users, events, guests, messages platform-wide
- `PlatformVisitChartWidget` — Platform visit chart

---

## 14. Jobs & Notifications

### Queue Jobs

| Job | Queue | Trigger | Purpose |
|-----|-------|---------|---------|
| `RecordLinkVisit` | default | Invitation page load | Log visit analytics async |
| `SendCoupleRsvpNotificationJob` | default | Guest submits RSVP | Push notification to couple |
| `SendGuestPushNotificationsJob` | default | Couple sends push | Deliver push to all targeted guests |

Queue connection: database (default). Needs `php artisan queue:listen` in production.

### Laravel Notifications

| Notification | Channel | Trigger | Recipient |
|-------------|---------|---------|-----------|
| `NewGuestMessageNotification` | Database + WebPush | Guest submits message | Couple (User) |
| `CoupleRsvpPushNotification` | WebPush | Guest RSVPs | Couple (User) |
| `GuestPushNotification` | WebPush | Couple sends push | Guest(s) |

### Observers

`GuestMessageObserver` — on `GuestMessage::created`:
- Dispatches `SendCoupleRsvpNotificationJob` (or similar) to notify couple of new message

---

## 15. Invitation Themes, Templates & Reveals

### Themes (6)
| Key | Display Name | Visual Style |
|-----|-------------|-------------|
| `amber-gold` | Amber Gold | Warm gold tones, elegant |
| `royal-wedding` | Royal Wedding | Deep blue/navy with gold accents |
| `lavender-dream` | Lavender Dream | Soft purple/lavender |
| `winter-magic` | Winter Magic | Cool whites and icy blues |
| `pearl-white` | Pearl White | Clean white, minimalist |
| `dusty-rose` | Dusty Rose | Warm rose/mauve tones |

Each theme defines: primary/secondary colors, font pairings, background patterns.

### Templates (3)
| Key | Style |
|-----|-------|
| `classic` | Traditional layout, centered, formal typography |
| `editorial` | Magazine-style, bold typography, asymmetric |
| `story` | Scroll-based narrative layout |

Templates define the structural layout of sections within the theme's color palette.

### Reveal Animations (4)
One-time animations played before showing the full invitation:
| Key | Animation |
|-----|-----------|
| `envelope` | Envelope opens to reveal invitation |
| `wax-seal` | Wax seal breaks/melts away |
| `curtain` | Curtains part to reveal |
| `storybook` | Storybook cover opens; spread fades into invitation |

Reveals are Alpine.js-driven, play once per session (sessionStorage flag).

---

## 16. Push Notifications (Web Push / VAPID)

**Package:** `laravel-notification-channels/webpush` v11

**VAPID keys** configured in `.env`:
```
VAPID_SUBJECT=mailto:contact@nasdan.ba
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
```

**Service Worker:** `/public/sw.js`
- Registers on guest push page
- Listens for `push` events
- Displays notification with title, body, icon, URL
- Handles notification click (opens invitation URL)

**Subscription flow (guests):**
1. Guest visits `/e/{slug}/{token}/push`
2. Browser requests permission
3. JS creates PushSubscription from browser
4. POST to `/push/subscribe/{guest:token}` → `StorePushSubscriptionAction`
5. Subscription stored in `push_subscriptions` table (polymorphic to `Guest`)

**Subscription flow (couples):**
1. Couple visits device settings in `/app` dashboard
2. POST to `/push/user/subscribe` → `StoreUserPushSubscriptionAction`
3. Subscription polymorphic to `User`

**Sending to guests:**
```
Couple → PushNotificationsResource → SendGuestPushNotificationsJob
  → iterates Guest::pushSubscriptions → sends WebPush notification
  → logs to PushNotificationLog
```

**Content encoding:** `aesgcm` (default)

---

## 17. Referral / Affiliate Program

**Config file:** `config/referral.php`
```php
'default_fee_percentage' => 10,
'cookie_name' => 'nasdan_ref',
'cookie_expiry_minutes' => 525600, // ~1 year
'route_prefix' => 'ref',
'code_prefix' => '_',
'code_length' => 8,
```

**Referral link:** `GET /ref/{code}` → `ReferralLinkController`
- Sets `nasdan_ref` cookie with referral code
- Redirects to `/onboarding`

**Attribution:**
- On `WeddingOnboarding` signup completion: reads `nasdan_ref` cookie
- Creates `Referral` record with `referrer_id` if cookie present
- Cookie cleared after attribution

**Couple referral page** (`/app/referrals`):
- Shows unique referral link
- QR code download (PNG/SVG via `chillerlan/php-qrcode`)
- Brochure PDF download (A5 marketing sheet via Spatie PDF)
- Stats: how many referred, total earnings, pending/paid breakdown
- Payout history table

**Admin payout management** (`/admin/referral-payouts`):
- Create payout records for referrers
- Set amount, currency (EUR default), period, status
- Mark as paid (upload payment proof, add payment link/notes)

**Artisan command:** `php artisan referrals:generate-accounts`
- Generates referral records for users who don't have one
- Used for migrating existing users to referral system

**No automated payout** — all payouts are manual (PayPal or bank transfer).

---

## 18. Analytics & Link Tracking

### Link Visit Recording

Triggered on every invitation page view (InvitationPage Livewire mount):
```
Request received → dispatch RecordLinkVisit::class
  → parse user agent (jenssegers/agent)
  → hash IP address (SHA-256 for privacy)
  → store LinkVisit record
```

Data captured:
- `device_type`: mobile / tablet / desktop
- `browser`: Chrome, Firefox, Safari, etc.
- `os`: iOS, Android, Windows, macOS, etc.
- `referer`: HTTP referer header
- `link_type`: public or personal
- `guest_id`: if personal link

### Couple Dashboard Widgets

**`VisitStatsWidget`:**
- Total visits (public + personal)
- Unique visitors (by IP hash)
- Device type breakdown (mobile/tablet/desktop)
- OS breakdown
- Browser breakdown

**`VisitChartWidget`:**
- Line chart: visits per day (last 30 days)
- Separate series: public vs personal

### Admin Dashboard

**`PlatformVisitChartWidget`:**
- Platform-wide visit volume over time
- Helps admin understand platform usage trends

### Google Analytics

- Tracking ID: `G-35Q0MPXC0W` (from `.env` `GOOGLE_ANALYTICS_ID`)
- Injected via Blade component in layouts
- Standard GA4 event tracking (no custom events in codebase)

---

## 19. Localization

**Supported locales:** `bs` (Bosnian), `en` (English), `de` (German)
**Default locale:** `bs`

**Translation files:** `lang/{bs,en,de}/`

**Locale switching:**
- `POST /lang/{locale}` → stores in session
- `SetAppLocale` middleware reads from session on every request
- Landing page and invitation pages have locale switchers

**Per-user locale:** `users.locale` column stores user's preferred locale
**Per-wedding locale:** Not stored — defaults to user's or app locale

**Filament panels:** Use app locale (translations applied to all panel strings)

---

## 20. Integrations

| Integration | Status | Details |
|-------------|--------|---------|
| **Stripe** | Not integrated | No payment processing; pricing is informational |
| **PayPal** | Manual only | Users store paypal_email; admin pays manually |
| **Bank Transfer** | Manual only | Users store bank_account_info; admin pays manually |
| **Resend** | Active | Transactional emails (verification, notifications) |
| **AWS S3** | Configured | Production media storage (hero images, gallery, audio, photos) |
| **Google Analytics (GA4)** | Active | Via tracking ID injected in Blade layouts |
| **Web Push (VAPID)** | Active | Guest/couple push notifications |
| **YouTube** | Embedded | Background music for invitations (YouTube IFrame API) |
| **Google Calendar** | Link generation | Save-the-date links (no OAuth, no API) |
| **WhatsApp** | Deep link | `wa.me/?text=...` for invite sharing |
| **Viber** | Deep link | `viber://chat?...` for invite sharing |
| **Telegram** | Deep link | `t.me/share/url?...` for invite sharing |
| **Facebook Messenger** | Deep link | `fb-messenger://...` for invite sharing |
| **DomPDF / Spatie PDF** | Active | PDF brochures + QR code downloads |
| **chillerlan/php-qrcode** | Active | QR code generation for referral links |
| **jenssegers/agent** | Active | User-agent / device detection for analytics |
| **Postmark** | Configured, unused | In services.php, available as mail driver |
| **Laravel Cloud** | Deployment target | Referenced in env/filesystem config |

---

## 21. Environment Variables & Configuration

### Core Laravel Variables

| Variable | Default | Purpose |
|----------|---------|---------|
| `APP_NAME` | Laravel | Application name |
| `APP_ENV` | local | Environment (local/production/staging) |
| `APP_KEY` | (empty) | Application encryption key — MUST be set |
| `APP_DEBUG` | true | Debug mode — set `false` in production |
| `APP_URL` | http://localhost | Base URL |
| `APP_LOCALE` | en | Laravel locale (set to `bs` in production) |
| `APP_FALLBACK_LOCALE` | en | Fallback locale |

### Database

| Variable | Default | Purpose |
|----------|---------|---------|
| `DB_CONNECTION` | sqlite | `sqlite` (dev) or `mysql`/`pgsql` (prod) |
| `DB_HOST` | 127.0.0.1 | Database host |
| `DB_PORT` | 3306 | Database port |
| `DB_DATABASE` | | Database name |
| `DB_USERNAME` | | Database user |
| `DB_PASSWORD` | | Database password |

### Infrastructure

| Variable | Default | Purpose |
|----------|---------|---------|
| `SESSION_DRIVER` | database | Session storage driver |
| `SESSION_LIFETIME` | 120 | Session lifetime in minutes |
| `QUEUE_CONNECTION` | database | Queue driver |
| `CACHE_STORE` | database | Cache driver |
| `FILESYSTEM_DISK` | local | Default filesystem disk |
| `MEDIA_DISK` | public | Disk for wedding media uploads |

### Email

| Variable | Default | Purpose |
|----------|---------|---------|
| `MAIL_MAILER` | log | Mail driver (log for dev, resend for prod) |
| `RESEND_API_KEY` | | Resend API key for production email |
| `MAIL_FROM_ADDRESS` | | From address |
| `MAIL_FROM_NAME` | | From name |

### AWS S3 (Production Media)

| Variable | Default | Purpose |
|----------|---------|---------|
| `AWS_ACCESS_KEY_ID` | | S3 access key |
| `AWS_SECRET_ACCESS_KEY` | | S3 secret key |
| `AWS_DEFAULT_REGION` | | S3 region (e.g. eu-central-1) |
| `AWS_BUCKET` | | S3 bucket name |
| `AWS_USE_PATH_STYLE_ENDPOINT` | false | For S3-compatible services |

### Web Push

| Variable | Default | Purpose |
|----------|---------|---------|
| `VAPID_SUBJECT` | | mailto: URL for VAPID auth |
| `VAPID_PUBLIC_KEY` | | VAPID public key (base64url) |
| `VAPID_PRIVATE_KEY` | | VAPID private key (base64url) |

### Analytics & Tracking

| Variable | Default | Purpose |
|----------|---------|---------|
| `GOOGLE_ANALYTICS_ID` | G-35Q0MPXC0W | GA4 measurement ID |

### App-Specific Config Files

**`config/app.php` additions:**
```php
'supported_locales' => ['en', 'bs', 'de'],
'default_locale' => 'bs',
'locale_labels' => [
    'bs' => 'Bosanski',
    'en' => 'English',
    'de' => 'Deutsch',
],
```

**`config/referral.php`:**
```php
'default_fee_percentage' => 10,
'cookie_name' => 'nasdan_ref',
'cookie_expiry_minutes' => 525600,
'route_prefix' => 'ref',
'code_prefix' => '_',
'code_length' => 8,
```

**`config/webpush.php`:**
Standard laravel-notification-channels/webpush config with VAPID keys, push subscription DB table name.

---

## 22. Build, Development & Deployment

### Local Development

```bash
# First time setup
composer setup
# Runs: composer install, php artisan migrate, npm ci, npm run build

# Daily development
composer dev
# Runs concurrently:
#   php artisan serve       (http://localhost:8000)
#   php artisan queue:listen
#   php artisan pail        (log tailing)
#   npm run dev             (Vite HMR)
```

### Frontend Build

```bash
npm run dev    # Vite dev server with HMR
npm run build  # Production build → public/build/
```

**Vite inputs** (`vite.config.js`):
- `resources/css/app.css` — Main Tailwind 4 stylesheet
- `resources/js/app.js` — Alpine.js + invitation JS
- `resources/css/filament/app/theme.css` — Custom Filament theme

**Font:** Instrument Sans (loaded via Bunny Fonts in Vite plugin — GDPR-friendly alternative to Google Fonts)

### Production Deployment (Laravel Cloud)

1. Set all required `.env` variables (APP_KEY, APP_URL, DB, S3, Resend, VAPID, GA)
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci && npm run build`
4. `php artisan key:generate` (if APP_KEY not set)
5. `php artisan migrate --force`
6. `php artisan storage:link`
7. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
8. Set `APP_DEBUG=false`, `APP_ENV=production`
9. Configure queue worker: `php artisan queue:work --sleep=3 --tries=3`
10. Set `MEDIA_DISK=s3` for S3 media storage

**No Dockerfile or CI/CD configuration** is in the repo (deployment is via Laravel Cloud native tooling).

---

## 23. Testing

**Framework:** PHPUnit 12.5 with Laravel test helpers

**Test files:**
- `tests/Feature/BrochureDownloadTest.php` — Tests referral brochure PDF download
- `tests/Feature/ReferralQrCodeDownloadTest.php` — Tests QR code download endpoint
- `tests/Feature/ExampleTest.php` — Placeholder
- `tests/Unit/ExampleTest.php` — Placeholder

**Database:** In-memory SQLite (`phpunit.xml` sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)

**Run tests:**
```bash
composer test
# or
php artisan test
```

Test coverage is minimal (only referral downloads tested). Most features are untested.

---

## 24. Key Architectural Decisions

1. **Monolith over SPA:** No React/Vue frontend. Everything is server-rendered Blade + Livewire + Filament. This simplifies deployment and matches the team's PHP expertise.

2. **Filament for both Admin and Couple dashboards:** Rather than building two custom dashboards, Filament's multi-panel support is used. This provides rich CRUD UI quickly but means the couple's dashboard feels more like an admin tool than a consumer product.

3. **No payment gateway integration:** Pricing is informational; activation is manual. This keeps complexity low for the initial version but prevents self-serve scaling.

4. **SQLite in dev, MySQL/Postgres in prod:** Standard Laravel setup. The database-queue/session/cache drivers mean Redis is optional — simplifying infrastructure.

5. **Database queue driver:** Simple, no Redis dependency needed. Suitable for low-to-moderate volume. Would need to switch to Redis/Horizon for high throughput.

6. **Guest tokens instead of auth:** Guests don't have accounts. Each guest has a 32-char token in their personal URL. This provides privacy (tokens are unguessable) without requiring guest accounts.

7. **IP hashing for analytics:** IPs are SHA-256 hashed before storage — privacy-preserving analytics without full anonymization.

8. **Manual referral payouts:** No Stripe Connect or PayPal API. Admin manually processes payouts based on `referral_payouts` table records. Simple but doesn't scale.

9. **Soft deletes on guests:** Guests are soft-deleted so RSVP/visit history is preserved even when "deleted" from the dashboard.

10. **S3 for media in production:** All uploaded media (hero photos, guest photos, audio recordings) stored on S3 in production, local disk in dev. Configured via `MEDIA_DISK` env var.

11. **Tailwind CSS 4:** Uses the new Vite-based Tailwind 4 config (no `tailwind.config.js` — configuration is in CSS via `@theme` directives).

---

## 25. Known Limitations & Future Opportunities

### Current Limitations

- **No self-serve payments** — Activation requires admin intervention; can't scale to many users
- **No Stripe integration** — Revenue model relies on manual processes
- **Manual affiliate payouts** — No automation; admin bottleneck for any growth
- **Minimal test coverage** — Risk of regressions; only 2 feature tests exist
- **Database queue driver** — Will struggle under load; no Redis/Horizon
- **No email to admin for enquiries** — Contact form stored in DB but admin must check manually
- **Single tenant per user** — Each user can only have one wedding event
- **No mobile app** — PWA only; no native iOS/Android app

### Opportunities

- Self-serve checkout with Stripe (Checkout Sessions)
- Automated referral payouts via Stripe Connect or PayPal Payouts API
- Email notifications for admin (new enquiries, new signups)
- More templates and themes
- Video support for guest messages
- Post-wedding photo album feature (extending the 30-day window)
- Guest chat/comments section
- QR code RSVP (couple prints QR codes for table cards)
- Guest seating planner
- Email RSVP reminders (scheduled jobs)
- Multi-event support per user (rehearsal dinner, etc.)
- White-label / reseller mode
- Mobile app (PWA is a start)
- More locales (HR, SR, SL, etc.)
- Analytics export (CSV/PDF report for couple)
- CMS for admin to manage landing page content

---

*Generated: July 2026. This document reflects the state of the codebase at that time.*
