# Visitor Management SaaS

Multi-tenant visitor check-in system. CodeIgniter 4 (`^4.7`) on PHP `^8.2`, served from
XAMPP at `C:\xampp\htdocs\visitor-management-saas`.

## Commands

Run from the project root:

```bash
php spark serve              # dev server (or just use the XAMPP vhost)
php spark migrate            # apply migrations
php spark migrate:rollback   # undo last batch
php spark db:seed UserSeeder      # seed the initial users
php spark db:seed DemoDataSeeder  # seed demo tenants/visitors
composer test                # phpunit (aliased in composer.json scripts)
vendor/bin/phpunit           # phpunit directly
```

## Layout

Standard CI4 `app/` tree. PSR-4: `App\` → `app/`, `Config\` → `app/Config/`.

- `app/Controllers/` — `AuthController`, `DashboardController`, `OrganizationController`,
  `BranchController`, `UserController`, `VisitorController`, plus `Home` and `BaseController`
- `app/Models/` — `OrganizationModel`, `BranchModel`, `UserModel`, `VisitorModel`, `VisitModel`
- `app/Filters/AuthFilter.php` — the single auth/role gate, registered as `auth` in `app/Config/Filters.php`
- `app/Views/` — `layouts/main.php` is the shell; `partials/` holds `sidebar`, `topbar`,
  `flash_messages`; feature folders are `auth/`, `dashboard/`, `organizations/`, `branches/`,
  `users/`, `visitors/`
- `app/Database/Migrations/` — organizations → branches → users → visitors → visits, then
  `UpdateUsersForSuperAdmin`
- `app/Config/Routes.php` — all routes are declared explicitly, no auto-routing

## Domain model

Tenancy nests: **organization → branch → user**, with visitors and visits recorded against a
branch. A `visit` is one check-in event; a `visitor` is the person, reusable across visits.

## Roles

`AuthFilter` takes the role as a filter argument, e.g. `['filter' => 'auth:super_admin']`.
Four roles exist, each with its own dashboard action on `DashboardController`:
`super_admin`, `organization_admin`, `branch_manager`, `receptionist`.

## Conventions

- Routes are nested under the owning resource and use explicit verbs, e.g.
  `organizations/(:num)/branches/(:num)/edit`. Updates are `POST .../update`, not PUT/PATCH.
- Controllers stay thin; query logic belongs on the models.
- Views extend `layouts/main.php` and use CI4's section syntax.
- Flash messages go through `app/Views/partials/flash_messages.php`.
- Never edit `vendor/` — it is Composer-managed.
- Real credentials live in `.env` (untracked). Don't hardcode them or read them into output.

## Current state

Routes for `organizations/*`, `users/*`, and `visitors/*` are all gated on `auth:super_admin`,
including `visitors/checkin`. The non-super-admin roles have dashboards but no scoped resource
routes yet, so a receptionist currently cannot reach the check-in screen. Treat this as
incomplete rather than intentional when touching routing or the auth filter.
