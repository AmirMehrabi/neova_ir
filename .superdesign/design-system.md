# Neova — Minimal Product Design System

## Product context

Neova is a Persian RTL workspace for small product and delivery teams. Its core jobs are planning today's work, scanning project health, managing Kanban boards, coordinating teammates, and resolving blockers without visual noise. The authenticated product includes Dashboard/Today, Workspace Board, Projects, Team, Project Board, Notifications, Profile, and Workspace Settings.

The dashboard is the visual source of truth for every subsequent page. It should feel calm, precise, modern, and unmistakably useful at first glance—minimal without becoming empty or generic.

## Public landing page override

- Audience: small Iranian teams across industries, not only software teams. Speak to founders, team leads, agencies, operations teams, and makers in plain Persian.
- Positioning: Neova brings projects, today's work, ownership, deadlines, and task-level discussion into one clear Persian workspace.
- Offer: free during beta, no bank card required, phone-number sign-in, and no installation. Never imply that the product will remain permanently free.
- Voice: direct, candid, useful, and conversational in the spirit of 37signals editorial writing. Avoid translated SaaS cliches, inflated promises, invented metrics, and vague phrases such as "all-in-one solution."
- Visual direction: unmistakably Neova, informed by Basecamp/37signals editorial rhythm rather than copied. Use generous reading space, strong headline/body contrast, alternating warm-light product sections and deep-ink proof sections, underlined editorial links, and real product UI as the primary visual evidence.
- Landing hero: kicker «برای تیم‌های کوچکی که کار واقعی انجام می‌دهند»; headline «کارها را از توی چت بیرون بیاورید.»; primary CTA «رایگان در نسخه بتا شروع کنید»; secondary CTA «نئووا را در ۲ دقیقه ببینید».
- Page narrative: minimal header; conversion hero with readable product preview; three outcome statements; relatable scattered-work problem; short founder letter; product stories for Today, project boards, and task details; concrete yes-answer capability list; three explicitly marked testimonial placeholders; answered FAQ; decisive closing CTA.
- Product proof must only show implemented capabilities: workspaces, multiple projects, Today planning, Kanban boards, task ownership and deadlines, checklists, attachments, comments, search, roles/access, invitations, private projects, and lightweight cycles.
- Testimonial areas must visibly say that approved customer quote/name/company content is pending. Never invent people, quotes, logos, or usage numbers.
- The exact supplied Neova horizontal logo must appear in every logo position. Do not replace it with initials, an invented icon, a generic mark, emoji, or text alone.

## Visual direction

- Premium monochrome productivity UI for teams of roughly 2–10 people: exceptionally simple, quiet, confident, and human.
- High-contrast black and warm white composition with generous negative space, crisp alignment, and one strong focal action.
- RTL-first composition. Primary sidebar is on the right; reading and scanning flow right-to-left.
- Avoid decorative gradients, glassmorphism, oversized cards, excessive pills, cartoon illustrations, and dashboard tile clutter.
- Establish hierarchy primarily through tonal layers: the warm-gray canvas must clearly separate from clean white cards and controls.
- Avoid visible card outlines. Use borders only for form fields, checkboxes, destructive boundaries, or an occasional row divider when spacing alone is insufficient.
- The page must not resemble an analytics dashboard. Prefer one focused work list and a small team pulse over metrics, charts, and card grids.
- Icons must be consistent 1.75px outline SVGs, never emoji or Unicode symbols.

## Color

- Canvas: `#F7F7F5`
- Primary surface: `#FFFFFF`
- Raised/selected surface: `#F0F0ED`
- Primary ink / brand: `#111111`
- Secondary ink: `#555552`
- Quiet text: `#8A8A84`
- Divider: `#E2E2DE`
- Inverse: `#FFFFFF`
- Success/complete: `#30302D` with `#ECECE8` tint
- Warning/danger: use black text plus label/icon shape; a single muted rust `#9A4A3A` is permitted only for genuinely blocked or destructive states.

The interface is overwhelmingly black, white, and warm gray. No blue, green, purple, gradients, or colorful project/status treatments.

## Typography

- Use only Estedad from the repository, with system sans-serif fallback.
- Page title: 28–32px, weight 800, line-height 1.45.
- Section title: 15–17px, weight 750–800.
- Body: 13–14px, weight 400–550, line-height 1.8.
- Metadata: 10–11px, weight 500–650.
- Numbers may be 24–30px but should remain restrained; use Persian digits in rendered Persian UI.
- Do not introduce serif, Latin display, or decorative typefaces.

## Spacing and geometry

- 4px base unit. Common gaps: 8, 12, 16, 24, 32px.
- Desktop shell: 208px right sidebar, 56px topbar, content max-width 1080px.
- Main page padding: 32px desktop, 18px tablet/mobile.
- Control heights: 36px compact, 42px primary.
- Radius: 8px controls, 12px panels, 16px only for major grouped surfaces. Pills only for status/count chips.
- Borders: 1px `#E5E9ED`.
- Shadows: overlays only, `0 18px 50px rgba(23,33,43,.12)`; ordinary panels are flat.

## Dashboard structure

- Persistent RTL sidebar with exact Neova logo, workspace switcher, four primary destinations, and compact project shortcuts.
- Slim topbar with page context, search, notifications, and account.
- Dashboard content begins with a concise greeting/date and one clear “وظیفه جدید” action.
- A single asymmetric overview row: meaningful work summary and progress, not a grid of equal KPI cards.
- Main work area prioritizes “تمرکز امروز” with scannable task rows and clear completion controls.
- Secondary column contains active projects and blockers/attention items, using simple lists and micro progress bars.
- Every datum should support a decision or next action. Avoid charts unless the relationship is materially clearer than text.

## Components

- Sidebar items: 38px height, icon + label, active state uses black ink, subtle warm-gray fill, and a thin black right indicator.
- Buttons: solid black primary, neutral outlined secondary, icon-only tertiary. No gradients.
- Task row: checkbox, title/project metadata, optional due/status, hover action; 52–60px high.
- Panel/card: white surface on the warm-gray canvas, no outline, 12px radius, 20–24px padding, and no shadow by default.
- Adjacent rows should usually be separated by 6–8px tonal gaps or alternating white/warm-gray backgrounds; use hairline dividers sparingly.
- Status chip: compact tonal background, 10px text, semantic color.
- Progress: 4px track with brand or success fill.
- Avatars: 28–32px circles, restrained overlap only for team context.

## Responsive behavior

- Below 768px hide desktop sidebar, retain a compact topbar, and use a fixed four-item bottom navigation.
- Stack overview and secondary columns into one stream.
- Preserve task-row actions with 44px touch targets; never require hover.
- Keep the primary create action reachable in the header or as a compact floating action, without covering content.

## Motion

- 150–180ms ease-out for hover, selected state, menu opening, and sidebar transitions.
- Small opacity/translate changes only. No springy or continuous animation.
- Respect `prefers-reduced-motion`.

## Brand invariant

Every logo position must render the supplied Neova Brand Asset exactly. Never replace it with initials, emoji, invented SVG marks, or text-only branding.
