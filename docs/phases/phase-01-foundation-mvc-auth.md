# Phase 1: Foundation, MVC Architecture, Database Connection & Authentication

## 1. Objectives
Establish a clean, robust, and extensible PHP 8.3 MVC structure connected to MySQL `gym-premium`, complete with PSR-4 autoloading via Composer, a secure routing and session handling engine, authentication (register gym, login, logout), and a futuristic component-based UI layout system.

---

## 2. Detailed Deliverables & Checklist

### 2.1 Environment & Composer Initialization
- [ ] Initialize `composer.json` with PSR-4 autoloading (`"App\\": "app/"`).
- [ ] Add dependencies: `vlucas/phpdotenv` (environment loading), `phpoffice/phpspreadsheet` (for Phase 5), `dompdf/dompdf` or pure CSS thermal printing for receipts.
- [ ] Create `.env.example` and `.env` with:
  ```env
  APP_NAME="GymFlow"
  APP_ENV=development
  APP_DEBUG=true
  APP_URL=http://gym.test

  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_NAME=gym-premium
  DB_USER=root
  DB_PASS=123
  DB_CHARSET=utf8mb4

  SESSION_LIFETIME=7200
  CSRF_SECRET=gymflow_csrf_secret_random_token_2026
  ```

### 2.2 MVC Framework Scaffolding
- [ ] **Front Controller (`public/index.php`):** Boots environment, initializes error handling, starts secure session, and dispatches the router.
- [ ] **Router (`app/Services/Router.php`):** Clean URL dispatching supporting GET, POST, Route Groups, and Middleware filters.
- [ ] **Base Controller (`app/Controllers/Controller.php`):** View rendering helper, JSON response helper, redirection helper, flash messages, validation accessor.
- [ ] **Database Connection (`app/Services/Database.php`):** Singleton PDO factory configured with strict error modes, persistent prepared statements, and UTF-8 collation.
- [ ] **CSRF Protection Service (`app/Services/Csrf.php`):** Generates and verifies session-bound tokens for all mutating POST/PUT/DELETE requests.
- [ ] **Request & Validator Engine (`app/Validators/Validator.php`):** Input sanitization, required fields, email, phone formatting, unique checks.

### 2.3 Authentication System
- [ ] **AuthController (`app/Controllers/AuthController.php`):**
  - `showLogin()`: Modern split-screen login view (matching the mockup design).
  - `login()`: Validates credentials, checks account status, regenerates session ID, sets auth context.
  - `showRegister()`: Tenant gym registration + initial owner account creation.
  - `register()`: Atomic transaction creating `gyms`, `users`, and `gym_users` records.
  - `logout()`: Clears and destroys session safely.
- [ ] **AuthMiddleware (`app/Middleware/AuthMiddleware.php`):** Blocks unauthenticated users, redirects to `/login`.
- [ ] **GuestMiddleware (`app/Middleware/GuestMiddleware.php`):** Redirects already authenticated users to `/dashboard`.

### 2.4 Component-Based UI Foundation
- [ ] **Main Layout (`app/Views/layouts/main.php`):** Responsive sidebar, modern topbar with gym switcher & user avatar dropdown, alert toasts, and dynamic content injection.
- [ ] **Auth Layout (`app/Views/layouts/auth.php`):** Futuristic split-screen presentation (left: gym branding & value props; right: crisp card form).
- [ ] **Component Helper (`app/helpers.php`):** `render_component($name, $data = [])` utility for cleanly injecting components (`metric_card`, `status_badge`, `alert`).
- [ ] **CSS Design Tokens (`public/assets/css/app.css`):**
  - Modern fonts (`Inter` / `Plus Jakarta Sans`).
  - Unified all-around subtle borders (`border: 1px solid rgba(0, 0, 0, 0.07)`).
  - Strictly **NO single-sided borders** or pseudo-element stripes.
  - Smooth micro-animations and soft multi-layered shadows.

---

## 3. Verification Criteria
- [ ] Browser navigates to `/` and redirects correctly based on auth state.
- [ ] Owner can register a new gym and account in MySQL `gym-premium`.
- [ ] Owner can log in with valid credentials, gets rejected on invalid credentials with safe feedback.
- [ ] Session is maintained securely across pages; logout destroys session.
- [ ] CSRF token validation blocks unauthorized forged requests.
