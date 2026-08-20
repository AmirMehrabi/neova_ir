# Key page dependency trees

## Effective dashboard: `/{workspace}/today`
Entry: `resources/views/today.blade.php`
- `resources/views/layouts/app.blade.php`
  - `resources/css/app.css`
    - `resources/css/board.css`
    - `resources/css/today.css`
  - `resources/js/app.js`
- `resources/views/components/workspace-shell.blade.php`
  - `resources/views/components/notification-menu.blade.php`
  - `public/assets/logo/horizental-logo-black-transparent.png`
  - `public/assets/logo/logo-black-transparent.png`

## Workspace projects: `/{workspace}/projects`
Entry: `resources/views/projects.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/components/workspace-shell.blade.php`
- `resources/views/components/notification-menu.blade.php`
- `resources/css/app.css`
- `resources/css/today.css`

## Team: `/{workspace}/team`
Entry: `resources/views/team.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/components/workspace-shell.blade.php`
- `resources/views/components/notification-menu.blade.php`
- `resources/css/app.css`
- `resources/css/today.css`

## Workspace aggregate board: `/{workspace}/board`
Entry: `resources/views/board.blade.php` (workspace aggregation branch supplied by controller)
- `resources/views/components/workspace-shell.blade.php`
- `resources/views/components/navbar.blade.php`
- `resources/views/components/notification-menu.blade.php`
- `resources/css/app.css`
- `resources/css/board.css`
- `resources/css/today.css`

## Project board: `/{workspace}/{project}/board`
Entry: `resources/views/board.blade.php`
- `resources/views/components/workspace-shell.blade.php`
- `resources/views/components/navbar.blade.php`
- `resources/views/components/notification-menu.blade.php`
- `resources/css/app.css`
- `resources/css/board.css`
- `resources/css/today.css`

## Workspace settings
Entry: `resources/views/workspaces/settings.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/components/workspace-shell.blade.php`
- `resources/views/components/notification-menu.blade.php`
- `resources/css/app.css`
- `resources/css/today.css`

## Profile
Entry: `resources/views/profile.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/components/app-page-header.blade.php`
- `resources/views/components/notification-menu.blade.php`
- `resources/css/app.css`

## Landing
Entry: `resources/views/welcome.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/css/app.css`
- Neova logo and signature assets

## Authentication
Entry: `resources/views/auth.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/css/app.css`
- Neova horizontal logo asset
