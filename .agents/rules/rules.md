# GymFlow — Technology Stack & Conventions

## 1. Stack Specifications
- **Runtime:** PHP 8.3.30 (Laragon environment)
- **Database:** MySQL 8.4.3 (InnoDB, character set: `utf8mb4_unicode_ci`, database name: `gym-premium`)
- **Backend Architecture:** PHP MVC (Controllers, Models, Services, Middlewares, Validators)
- **Frontend Architecture:** Component-Based UI (PHP partials + modern CSS design tokens + Vanilla JS modules)
- **Styling Framework:** Bootstrap 5.3 + Custom Futuristic Styling Tokens (`app/public/assets/css/app.css`)
- **Icons:** Bootstrap Icons (CDN / local SVG sprite)
- **Charts:** Chart.js 4+
- **Spreadsheets:** PhpSpreadsheet (Excel & CSV import/export)
- **Testing:** PHPUnit 10+ / custom integration test runner

---

## 2. Directory Layout
```
gym/
├── app/
│   ├── Controllers/         # HTTP request handlers (thin)
│   ├── Models/              # Data access & query builders
│   ├── Services/            # Business logic & domain workflows
│   ├── Middleware/          # AuthMiddleware, RoleMiddleware, CsrfMiddleware
│   ├── Validators/          # Form request validation & sanitization
│   └── Views/
│       ├── layouts/         # main.php, auth.php
│       ├── components/      # metric_card.php, data_table.php, status_badge.php, receipt.php, modal.php
│       ├── partials/        # sidebar.php, topbar.php, footer.php
│       ├── dashboard/       # index.php
│       ├── members/         # index.php, create.php, edit.php, view.php
│       ├── plans/           # index.php, create.php, edit.php
│       ├── payments/        # index.php, create.php, receipt.php
│       ├── renewals/        # index.php, expiring.php
│       ├── reports/         # collections.php, dues.php, plans.php
│       └── imports/         # index.php, preview.php
├── config/
│   ├── app.php              # App settings, timezone, currency (INR ₹ default)
│   └── database.php         # PDO connection config for `gym-premium`
├── database/
│   ├── migrations/          # SQL / PHP migration scripts
│   └── seeders/             # Demo data generator for local dev
├── docs/
│   ├── database.md          # Complete schema specification
│   ├── phases/              # Detailed phase execution files (Phase 1 to 7)
│   └── api.md               # Internal JSON endpoints documentation
├── public/
│   ├── index.php            # Front controller & routing bootstrap
│   ├── .htaccess            # Apache URL rewrite rules
│   └── assets/
│       ├── css/             # Modern CSS tokens, variables, components
│       ├── js/              # Client-side reactivity, chart controllers, AJAX
│       └── images/          # Logos, placeholder avatars
├── routes/
│   └── web.php              # Clean route definitions
├── storage/
│   ├── logs/                # Application & error logs
│   ├── imports/             # Temporary CSV/XLSX uploaded files
│   └── exports/             # Generated member & financial exports
├── tests/                   # Integration and unit tests
├── .agents/
│   └── rules/
│       └── rules.md         # This rules file
├── .env.example             # Template environment configuration
├── .env                     # Local credentials (DB_NAME=gym-premium)
├── composer.json            # Dependencies definition
├── GEMINI.md                # Assistant rules & memory
└── README.md                # Project documentation & status
```

---

## 3. UI Component Contract
Every UI view should compose reusable components from `app/Views/components/`:
- `render_component('metric_card', [...])`
- `render_component('status_badge', ['status' => 'active'])`
- `render_component('data_table', ['headers' => [...], 'rows' => [...]])`
- `render_component('quick_action', [...])`
- `render_component('receipt_modal', [...])`

Unified styling rules apply strictly to all components. NO single-sided border stripes, NO accent lines under text.
