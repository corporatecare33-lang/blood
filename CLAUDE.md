# CLAUDE.md — RoktoDoot (রক্তদূত) Blood Donor Platform

Read this file fully before touching code. It is the single source of truth for
facts, content, architecture and design. **If a fact is not in this file, in the
database seeders, or in `database/data/`, do not invent it.** Ask instead.

---

## 1. What this project is

A Laravel rebuild of **https://www.bandhanblood.com/** (originally an Angular SPA
backed by `https://a.bandhanblood.com/api/*`). The service was renamed
**RoktoDoot (রক্তদূত)** by the owner on 2026-11-09 (formerly BloodBond / ব্লাডবন্ড, before that Bandhan / বন্ধন).
RoktoDoot is a free, voluntary blood
donor network in Bangladesh: donors register with their blood group and location;
people who need blood search donors (login required) or post a blood request.

Source research (2026‑11‑09) is summarised in §3. A design reference screenshot of
badhan.org lives in `docs/reference/` — it was used **only** to confirm feature
ideas (public blood-request board, tips, terms/privacy). Do not copy its design.

## 2. Verified facts (the ONLY business facts allowed in copy)

| Fact | Value | Source |
|---|---|---|
| Name | **RoktoDoot / রক্তদূত** (Bangla UI uses রক্তদূত). Formerly BloodBond / ব্লাডবন্ড (same day), and Bandhan / বন্ধন before that. OG image: `public/images/og/roktodoot-share.jpg`. Internal ids (`config/bandhan.php`, DB `bandhan_blood`, logo file names) intentionally keep `bandhan`. | owner, 2026-11-09 |
| Logo tagline | রক্ত দিন জীবন বাঁচান। রক্ত নিন নিজে বাঁচুন। (স্বেচ্ছায় রক্তদাতাদের সংগঠন) | logo image |
| Contact email | bandhanblood24@gmail.com | site footer |
| Facebook | **Removed at the owner's request (2026-11-09).** Do not link Facebook anywhere. | owner |
| Sponsor | TohsinBD — **name only, plain text, no link** (owner, 2026-11-09). Removed from the footer (owner, 2026-11-09); now only in `llms.txt`. | owner |
| Footer credit | "Designed and Developed By Digital Webars" → https://digitalwebars.com/ (owner, 2026-11-09) | owner |
| Android app | **Removed at the owner's request (2026-11-09).** Do not link the APK. | owner |
| External links | **None**, except the footer credit link above. Owner rule (2026-11-09): the public site links to no other website. `mailto:` for the contact email is allowed. Exception (owner, 2026-11-10): admin-added YouTube videos are **embedded** in the home "ভিডিও গ্যালারি" (thumbnail from i.ytimg.com, player from youtube-nocookie.com after a tap) — embeds only, never an `<a>` to YouTube on the public site. | owner |
| iPhone app | **Not published.** Source links to a dummy App Store id (`id1234567890`). Do NOT link it. | `data/data.json` |
| Hero claim | "Voluntary blood donation-based platform to connect blood searchers with donors" | hero |
| Claim | Connects donors and searchers using SMS; find by blood type, location, donor history | hero |
| What is RoktoDoot | "An automated blood service that connects blood searchers with voluntary donors in a moment through SMS. Always a free service for all." | home |
| Why RoktoDoot | 100% automated · Always free · 24×7 service · All data is secured | home |
| Conditions (আমাদের শর্তসমূহ) | 5 rules — see `config/bandhan.php` → `conditions` | `data/data.json` |
| Occupation options | Student, Others | `data/data.json` |
| Gender options | Male, Female, Others | `data/data.json` |

**Never add:** phone numbers, street addresses, founding year, member/donation
counts, testimonials, team names, awards, partner hospitals. None are verified.
Live numbers shown on the site must be computed from the database (e.g. count
of registered donors) — never hard-coded.

Source placeholders that were intentionally **not** carried over: About page
"TITLE/description" blocks, the lorem-ipsum "Let's change the world" paragraph,
empty "Our Best Services" heading, `#` social links for YouTube/Twitter, dummy
iOS link. They were replaced with real copy derived from the facts above.

Medical guidance on `/tips` follows WHO *Blood donor selection* (2012) general
criteria and always tells readers the blood bank makes the final screening
decision. Values used: age 18–65 (RoktoDoot's own rule: minimum 18), weight ≥ 50 kg,
haemoglobin ≥ 12.0 g/dL (women) / ≥ 13.0 g/dL (men), whole-blood interval
≥ 12 weeks (men) / ≥ 16 weeks (women). Do not add other medical claims.

## 3. Source site map (original → this app)

| Original (hash routes) | New route | Notes |
|---|---|---|
| `#/` | `/` | home |
| `#/about` | `/about` | About sections are DB-editable (`about_sections`) |
| `#/gallery` | `/gallery` | DB-driven, admin upload; empty state when none. 10 owner-supplied photos ship via migration `2026_11_10_000020_add_first_gallery_photos` (WebP in `public/images/gallery/`; `images/` paths are served from `public/` and never deleted). Titles describe the scene only — no places, dates or events. Home shows the first 6 under "ছবির গ্যালারি" (right before the video gallery), hidden when none are published. |
| `#/search-donors` | `/search-donors` | **auth required** (as original) — protects donor phone numbers |
| `#/blood-request` | `/blood-requests`, `/blood-requests/new` | public board + post form |
| `#/register` | `/register` | full donor form (fields in §6) |
| `#/login` | `/login` | mobile number + password |
| "Forgot password" | `/forgot-password` (2 steps in one Livewire component) | OTP over SMS (original had GetOTP/TokenVerify) |
| `#/account-update` | `/account` | profile, last donation, availability |
| `#/admin-*` | `/admin/*` | own sign-in at `/admin/login` (admins only); dashboard, donors, blood requests, blog, gallery, videos, about, thanas, settings, profile, admins |
| — | `/tips`, `/terms`, `/privacy` | new, real content |
| — (badhan.org "Latest News" idea) | `/blog`, `/blog/{slug}` | DB-driven blog (`posts`), written at `/admin/blog`; home shows the latest 3 under "সর্বশেষ খবর" only when posts exist. Three evergreen guides ship via migration `2026_11_10_000010_add_first_blog_posts` (covers in `public/images/blog/`, rendered from brand type — paths starting `images/` are served from `public/` and never deleted). Never invent news or events: activity reports need real details and photos from the owner. |
| — (owner request, 2026-11-10) | home "ভিডিও গ্যালারি" (last section, right above the footer) | DB-driven (`videos`), managed at `/admin/videos` by pasting a YouTube link; title auto-filled via YouTube oEmbed. Hidden when no video is published. No videos are seeded. |

Original API endpoints (reference only, not called): `DonerReg/{CreateDonerReg,userlogin,Search,GetOTP,TokenVerify}`,
`Admin/{Login,Search}`, `Thana/*`, `Gallery/*`, `AboutUs/*`.

## 4. Stack (installed — do not add libraries without a real need)

| Concern | Choice | Why |
|---|---|---|
| Framework | Laravel 12 (PHP 8.2 on XAMPP) | Laravel 13 needs PHP 8.3 |
| Interactivity | Livewire 4 (class components in `app/Livewire`) | forms, search, admin CRUD |
| JS reactivity | Alpine.js **bundled inside Livewire** | never install `alpinejs` separately (double-boot bug) |
| SPA navigation | `wire:navigate` on internal links + `@persist` | no full reloads, no Inertia needed |
| CSS | Tailwind CSS v4 (`@tailwindcss/vite`), tokens in `resources/css/app.css` `@theme` | |
| UI kit | shadcn-style Blade components in `resources/views/components/ui/*` using shadcn semantic tokens (`primary`, `muted`, `border`, `ring`, `destructive`…) | shadcn itself is React-only |
| Icons | Lucide via `mallardduck/blade-lucide-icons` → `<x-lucide-droplet class="size-5" />` | one consistent icon set |
| Motion | GSAP + ScrollTrigger in `resources/js/motion.js`, code-split, driven by `data-hero`, `data-intro`, `data-reveal`, `data-reveal-stagger`, `data-draw`, `data-count-to` attributes; reduced motion → short fades only | add attributes in Blade, never inline JS |
| Fonts | **Anek Bangla** (display) instanced to wght 500–800 / wdth 80–100 in `resources/fonts/` (source: `@fontsource-variable/anek-bangla`) + **Hind Siliguri** (body, Fontsource); body digits ০–৯ come from a 3 KB Noto Sans Bengali subset (`Bangla Digits`) because Hind's "১" is illegible | self-hosted, no Google Fonts request |
| Cache | Laravel cache (`file` store) via `App\Support\SiteCache` | locations, about, gallery, home requests |
| DB | MySQL/MariaDB `bandhan_blood` (XAMPP); tests use SQLite in-memory | |
| SMS | `App\Contracts\SmsGateway`; `log` driver (dev) — set `SMS_DRIVER` + provider credentials for production | never fake "sent" in prod |

**Inertia.js is deliberately NOT installed.** Inertia (React/Vue pages) and
Livewire are competing rendering models; using both doubles the JS, the routing
model and the mental load. Livewire + `wire:navigate` already gives SPA behaviour.

## 5. Design system — Bangla-first mobile app look

**Visual reference: `docs/reference/mobile-app-mockup.jpg`** (user-approved, 2026-11-09).
Decisions confirmed with the user: keep our own brand (RoktoDoot, formerly BloodBond / Bandhan) (the mockup's "মধুখালী নিউজ"
pill is NOT our brand) · no WhatsApp banner (no verified number — it is an
"urgent request" banner linking to `/blood-requests/new`) · show area names, never
distances (no coordinates exist) · Bangla-first UI with a mobile bottom tab bar.

Shell: red app bar on mobile (home: menu · "রক্তদূত" pill · search; other pages:
back · page title from `Seo::appBar()` · menu) + slide-in drawer; desktop gets a
red header with logo + nav. Bottom tab bar `components/site/tab-bar` (হোম · ডোনার ·
রিকোয়েস্ট · প্রোফাইল), `lg:hidden`, body has `.pb-tabbar`.

Colour tokens (`@theme` in `resources/css/app.css`):

| Token | Hex | Use |
|---|---|---|
| `crimson` / `primary` | `#CF1F2A` | app bar, primary buttons, hero card, avatars |
| `crimson-deep` | `#A5141E` | hover, footer |
| `crimson-soft` | `#FDECEC` | tinted icon circles, soft buttons |
| `ink` / `foreground` | `#1F1416` | text |
| `blush` / `background` | `#F7F0EF` | page canvas |
| `paper` / `card` | `#FFFFFF` | every content block (`.card` = rounded-xl + shadow-card) |
| `marigold` | `#F4B400` | focus ring only |
| `leaf` | `#1B7A47` | "এখন দিতে পারবেন" status |

Type: Anek Bangla (display headings, wght 500–800) · Hind Siliguri (body 400–700).
Blood-group labels (`.group-glyph`) use Hind Siliguri bold so "O+" never reads "0+".
Radius: inputs/buttons/chips `rounded-lg` (8px), cards `rounded-xl` (12px).
Group chips: red-outlined, filled red when selected (`components/site/group-chips`).

Copy rules: all UI text in Bangla; numbers/dates via `AppSupportBn`
(`Bn::num`, `Bn::date`, `Bn::dateTime`, `Bn::ago`) so digits are Bangla. Blood
groups, phone numbers and the sponsor name stay in Latin script (`lang="en"`).
Location names use `bn_name`. OTP SMS text is romanized Bangla (ASCII = 1 SMS
segment). Validation messages: `lang/bn/validation.php`; `APP_LOCALE=bn`.

## 6. Data model

- `users` — donor + admin accounts: `name, mobile (unique, 01XXXXXXXXX), email?,
  gender, blood_group, occupation, date_of_birth, last_donated_at?, division_id,
  district_id, thana_id?, address?, password, role (donor|admin|super_admin),
  is_available (bool), is_active (bool)`.
- `divisions`, `districts`, `thanas` — seeded from `database/data/bd-geo.json`
  (8 / 64 / 494, with `bn_name`). Thanas are admin-editable.
- `blood_requests` — `patient_name, blood_group, bags, needed_at, hospital,
  district_id, contact_name, contact_mobile, note?, status (open|fulfilled|closed),
  user_id?`.
- `gallery_items` — `title, caption?, image_path, width, height, sort, is_published`.
- `videos` — `title, youtube_id (11 chars; parsed from any YouTube link by Video::idFrom), sort, is_published`.
- `about_sections` — `title, body, sort`.
- `posts` — `title, slug (unique; Bangla letters kept via Post::slugFrom), excerpt?, body (Markdown, raw HTML escaped,
  external links get rel=nofollow noopener), cover_path?/cover_thumb_path? (WebP via ImageProcessor, dir `blog/`),
  cover_width/height (large file), cover_alt?, is_published, published_at, user_id?`. Public = `Post::published()`
  (is_published and published_at ≤ now; a future date = scheduled). Admins can preview drafts at the public URL (noindex).
- `otp_codes` — `mobile, code_hash, purpose, expires_at, attempts`.
- `site_settings` — `key, value (json)`: admin overrides for the editable facts in
  `config/bandhan.php`. Read them only through `App\Support\Site` (`Site::email()`,
  `Site::conditions()`…), which falls back to config; never read those config keys in views.

Enums in `app/Enums`: `BloodGroup`, `Gender`, `Occupation`, `Role`, `RequestStatus`.
A donor is "eligible" when `is_available` and last donation ≥ 90 days ago (or never).

## 7. Code layout

```
app/Enums, app/Models, app/Contracts, app/Services (Sms*), app/Support (Seo, SiteCache)
app/Http/Controllers (public pages, sitemap, robots, llms), app/Http/Middleware (EnsureAdmin)
app/Livewire/{Auth,Donors,Requests,Account,Admin}/*
resources/views/components/{ui,site,layouts}  → shadcn-style primitives, site sections, layouts
resources/views/pages/*                        → controller-rendered public pages
resources/views/livewire/**                    → Livewire views
resources/views/components/layouts/admin*.blade.php → admin shell (inset sidebar, collapses to an icon rail; drawer on mobile)
resources/views/components/admin/*             → admin-only pieces (confirm + confirm-host AlertDialog, pagination, column-chart, sort-header, empty)
resources/views/errors/{404,419,500,503}.blade.php
```

Conventions: controllers set SEO via `Seo::make()->title()->description()…` and
the layout renders it (title, description, canonical, OG, Twitter, JSON-LD).
One `<h1>` per page. Error pages call `Seo::withoutCanonical()`. Every internal `<a>` uses `wire:navigate` (except downloads).
No links to other websites (see §2 "External links").

## 8. Commands

```
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # seeds geo data, about, super admin from .env
php artisan storage:link
npm run dev  |  npm run build
php artisan serve                   # http://127.0.0.1:8000
php artisan test
```

SMS in development is written to `storage/logs/laravel.log` (look for "SMS (log driver)") — use it to read OTP codes locally.

Super admin is created from `ADMIN_MOBILE` / `ADMIN_PASSWORD` in `.env` — no
default credentials are committed.

## 9. Definition of done

`npm run build` clean · `php artisan test` green · every route returns 200
(or the intended redirect) · zero console errors · no horizontal scroll at 320px ·
one h1 per page · unique title/description/canonical · sitemap/robots/llms
served · no placeholder text · no invented facts.
