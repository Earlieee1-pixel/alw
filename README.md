# ALW — Atomic Legendary Warriors

Internal platform for managing the ALW global network. Built with Laravel, Vue 3, and Inertia.js.

---

## Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Backend    | Laravel 13, PHP 8.3                 |
| Frontend   | Vue 3, Vite 8, Inertia.js           |
| Styling    | Tailwind CSS v4                     |
| Icons      | Heroicons v2                        |
| Auth       | Laravel Sanctum + Spatie Permission |
| Routing    | Ziggy (named routes in JS)          |
| Database   | MySQL 8                             |
| State      | Pinia                               |

---

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/               # Login, Register, Logout
│   │   │   └── DashboardController.php
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php
│   └── Models/
│       └── User.php                # Invite code, downline relationships
├── database/
│   ├── migrations/                 # All schema changes
│   └── seeders/
│       └── AdminSeeder.php         # Creates first admin user
├── resources/
│   ├── css/
│   │   └── app.css                 # Tailwind v4 + ALW brand colors
│   ├── js/
│   │   ├── pages/
│   │   │   ├── Landing.vue         # Public landing page
│   │   │   ├── Dashboard.vue       # Member dashboard (placeholder)
│   │   │   └── Auth/
│   │   │       ├── Login.vue
│   │   │       └── Register.vue    # Invite-only registration
│   │   ├── utils/
│   │   │   └── resolvePageComponent.js
│   │   └── app.js                  # Inertia + Pinia + Ziggy entry point
│   └── views/
│       └── app.blade.php           # Root Inertia template
└── routes/
    └── web.php                     # All application routes
```

---

## Getting Started

### Requirements

- PHP 8.3+
- Composer 2+
- Node.js 20+
- MySQL 8
- Laragon (recommended for Windows)

### Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file
cp .env.example .env

# 3. Generate app key
php artisan key:generate

# 4. Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alw
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations
php artisan migrate

# 6. Seed the first admin user
php artisan db:seed --class=AdminSeeder

# 7. Install JS dependencies
npm install
```

### Running Locally

Open two terminals, both inside the `backend/` folder:

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Vite asset compiler
npm run dev
```

Then open: **http://localhost:8000**

---

## Default Admin Credentials

Created by `AdminSeeder`. Change these immediately in production.

```
Email:    admin@alw.com
Password: password123
```

---

## Authentication Flow

- **Login** — `/login` — standard email + password
- **Register** — `/register?ref=INVITE_CODE` — invite-only
  - Every member has a unique 8-character invite code
  - Register page is inaccessible without a valid `?ref=` code in the URL
  - The referring member automatically becomes the new member's upline
- **Logout** — POST to `/logout`

### Roles

Managed by Spatie Laravel Permission.

| Role    | Description                               |
|---------|-------------------------------------------|
| admin   | Full access — manages the entire platform |
| leader  | Can manage their downline and post videos |
| member  | Standard access — view and join calls     |

---

## Brand Colors

| Name       | Hex       | Usage                        |
|------------|-----------|------------------------------|
| Teal 500   | `#288783` | Primary — buttons, accents   |
| Cream 200  | `#FFEBD0` | Background — light sections  |
| Teal 800   | `#103c3b` | Dark sections, sidebars      |

---

## Code Conventions

- **Comments in code** — Bisaya, brief. Describes what the function does, not how.
- **User-facing text** — English only.
- **PHP** — PSR-12 via Laravel Pint. Run `vendor/bin/pint` before committing.
- **Vue** — Composition API with `<script setup>`. One component per file.
- **File placement** — Auth in `Auth/`, pages in `pages/`, reusable pieces in `components/`, API calls in `services/`.

---

## Planned Features

- [x] Landing page — public ALW homepage
- [x] Auth — invite-only register, login, logout
- [x] Shared layout — sidebar, role-based nav, mobile responsive
- [x] Member dashboard — stats, recent members table, invite link copy
- [x] Admin dashboard — platform-wide stats, all members table
- [x] Role middleware — admin/leader/member access control
- [ ] Downline network tree viewer
- [ ] Training video library (YouTube/Vimeo embed)
- [ ] Jitsi video call rooms (admin-generated room codes)
- [ ] Full member management page (activate/suspend)
- [ ] Profile page

---

## Deployment

For production, run:

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Recommended: [Laravel Cloud](https://cloud.laravel.com/) or any VPS with Nginx + PHP-FPM.
