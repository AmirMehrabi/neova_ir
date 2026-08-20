# Extractable components

## WorkspaceShell
- Source: `resources/views/components/workspace-shell.blade.php`
- Category: layout
- Description: Primary authenticated RTL shell with sidebar, topbar, workspace/project navigation, command search, account controls, and mobile navigation.
- Extractable props: `activeItem` (string, default `today`), `workspaceName` (string), `userName` (string), `projectNames` (array), `sidebarCollapsed` (boolean).
- Hardcoded: Neova logo asset, Persian navigation labels, layout structure, icon positions, styling.

## Navbar
- Source: `resources/views/components/navbar.blade.php`
- Category: layout
- Description: Sticky responsive navigation used for standalone and embedded board contexts.
- Extractable props: `dark`, `light`, `embedded`, `title`, `showSearch`, `notificationCount`.
- Hardcoded: logo positions/assets, notification and user-menu structure, sizing and styles.

## NotificationMenu
- Source: `resources/views/components/notification-menu.blade.php`
- Category: basic
- Description: Notification bell with unread badge and compact dropdown list.
- Extractable props: `unreadCount`, `isOpen`.
- Hardcoded: bell icon, Persian labels, dropdown styling.

## AppPageHeader
- Source: `resources/views/components/app-page-header.blade.php`
- Category: layout
- Description: Dark standalone page header with back navigation, Neova logo, title, notifications, and avatar.
- Extractable props: `title`, `showBack`.
- Hardcoded: logo asset, colors, icon shapes, sizing.

## Breadcrumb
- Source: `resources/views/components/breadcrumb.blade.php`
- Category: basic
- Description: Horizontal RTL breadcrumb with compact chevrons.
- Extractable props: `items`.
- Hardcoded: chevron icon and typography.
