# Neova theme

## Compact token summary

- Direction/language: RTL, Persian (`dir="rtl"`, `lang="fa"`).
- Primary typeface: Estedad, local font files under `public/assets/fonts/estedad/`; secondary available families include Vazirmatn, Dana, and IRANSans.
- Paper: `#FBFDFF` / `#FDFDFC`; surface: `#FFFFFF`; muted surface: `#F7F8F7` and pale blue `#EEF7FF`.
- Ink: `#102A43` (authenticated app override), legacy `#18212B`; muted text `#64788A`; quiet text `#94A3B8`.
- Brand blue: `#0069D9`; hover `#0052B3`; soft blue `#F4F9FE` / `#EEF7FF`.
- Lines: `#DCE8F2`, `#E8EBE9`, `#EDF2F6`.
- Status: red/coral `#B9523E` / `#DC5B5B`; green `#77C9A7`; amber uses pale warm surfaces.
- Radius: mostly 7–13px for product controls/cards; circular avatars; avoid excessive pills except compact status chips.
- Shadows: restrained cool shadows, typically `0 16px 40px rgba(16,42,67,.15)` for overlays; most page content is flat with 1px separators.
- Layout: 236px desktop RTL sidebar, 57px sticky topbar, max content widths 820–1280px; mobile breakpoint 767px with fixed 62px bottom navigation.
- Motion: 150–200ms color/opacity/width transitions; reduced motion respected on board interactions.

## Raw source tokens

From `resources/css/app.css`:

```css
:root {
    --neova-paper: #FDFDFC;
    --neova-surface: #FFFFFF;
    --neova-surface-muted: #F7F8F7;
    --neova-ink: #18212B;
    --neova-muted: #66717A;
    --neova-line: #E8EBE9;
    --neova-cobalt: #0069D9;
    --neova-cobalt-soft: #F4F9FE;
    --neova-coral: #0069D9;
    --neova-mint: #77C9A7;
}
.app-page { background:#fbfdff !important; color:#102a43; font-family:'Estedad',sans-serif; }
.app-navbar { border-color:#e0eaf3 !important; background:rgba(251,253,255,.96) !important; box-shadow:0 5px 18px rgba(16,42,67,.045) !important; }
```

From `resources/css/today.css`:

```css
.workspace-shell { --shell-sidebar:236px; display:grid; grid-template-columns:var(--shell-sidebar) minmax(0,1fr); direction:rtl; }
.workspace-sidebar { position:sticky; top:0; width:var(--shell-sidebar); height:100vh; border-left:1px solid #DCE8F2; background:#fff; padding:14px 12px; }
.workspace-topbar { position:sticky; top:0; height:57px; padding:0 18px; border-bottom:1px solid #DCE8F2; background:rgba(251,253,255,.95); backdrop-filter:blur(12px); }
.today-page { width:min(820px,calc(100% - 40px)); margin:0 auto; padding:54px 0 90px; color:var(--neova-ink); }
@media (max-width:767px) { .workspace-shell { display:block; } .workspace-sidebar { display:none; } .workspace-mobile-nav { display:grid; position:fixed; inset:auto 0 0; height:62px; grid-template-columns:repeat(4,1fr); } }
```

Build configuration: Vite + Laravel plugin + Tailwind CSS v4 through `@tailwindcss/vite`. No separate Tailwind config file exists.
