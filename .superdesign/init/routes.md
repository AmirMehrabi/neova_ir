# Route map

Router: Laravel routes in `routes/web.php`. All main product pages are server-rendered Blade views. The app is RTL Persian.

| URL | Name | View / controller | Layout |
|---|---|---|---|
| `/` | `home` | `resources/views/welcome.blade.php` | standalone landing |
| `/auth` | `auth` | `resources/views/auth.blade.php` | `layouts.app` |
| `/dashboard` | `dashboard` | `DashboardController@index`; redirects members to Today, otherwise `dashboard.blade.php` onboarding | `layouts.app` |
| `/{workspace}/today` | `today` | `resources/views/today.blade.php` | `workspace-shell` |
| `/{workspace}/board` | `workspace.board` | workspace board aggregation | `workspace-shell` |
| `/{workspace}/projects` | `projects.index` | `resources/views/projects.blade.php` | `workspace-shell` |
| `/{workspace}/team` | `team.index` | `resources/views/team.blade.php` | `workspace-shell` |
| `/{workspace}/{project}/board` | `board` | `resources/views/board.blade.php` | `workspace-shell` + embedded navbar |
| `/{workspace}/settings` | `workspaces.settings` | `resources/views/workspaces/settings.blade.php` | `workspace-shell` |
| `/profile` | `profile` | `resources/views/profile.blade.php` | standalone app layout |
| `/notifications` | `notifications.index` | `resources/views/notifications/index.blade.php` | standalone app layout |

Authenticated product architecture: Today is the effective dashboard/home after login. Its sibling pages are workspace board, projects, team, project boards, workspace settings, profile, and notifications. The route file also defines task planning/state, board CRUD, cycle configuration, attachments, member roles, invitations, and search endpoints.
