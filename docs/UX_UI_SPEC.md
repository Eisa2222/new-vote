# SFPA Voting — UX/UI Specification

## Design System

**Type scale**: 12 / 14 / 16 / 20 / 24 / 32. Font stack: Tajawal/Cairo (AR),
Inter (EN). Line-height 1.5 body, 1.2 headings.

**Colors**
- Primary:   `emerald-600` (#059669) — buttons, active, selected
- Secondary: `slate-800`   — text, nav sidebar
- Neutral:   `slate-50` bg, `slate-200` borders
- Success:   `emerald-500`
- Warning:   `amber-500`
- Danger:    `rose-600`
- Info:      `blue-500`

**Components**: buttons (primary/ghost/danger), inputs (with inline errors),
tables (sticky header, zebra rows, hover), cards (rounded-2xl, shadow-md),
modal (centered, backdrop), badges (status chips), tabs (underline),
breadcrumbs (chevron), toast (top-right, auto-dismiss 4s), stepper
(horizontal, numbered).

**RTL/LTR**: `dir` set on `<html>` by `SetLocale` middleware based on locale.
All spacing uses `gap-*` + `text-start/text-end` so flipping direction requires
no extra CSS.

---

## Screens

### 1. Login
- Card centered, logo top, AR/EN toggle in corner.
- Email + password, remember-me, "Forgot?" link (future).
- Primary button full-width.
- Error toast on invalid credentials.

### 2. Admin Dashboard
- Sidebar (fixed, 256px): logo + nav + locale switch.
- 4 KPI cards: Clubs / Players / Campaigns / Votes.
- Activity feed (last 20 `activity_log` rows).
- Quick actions: "New Campaign", "New Player".

### 3. Users & Permissions
- Table: avatar · name · email · roles · status.
- Drawer (slide-in from inline side) for edit: role checkboxes, toggle status.
- Invite modal for new user.
- Empty state with illustration.

### 4. Clubs Management
- Index: filter bar (search · status · sport), table with logo avatars.
- Create/Edit form: 2-col bilingual name, logo dropzone with preview,
  multi-select sports as chips, status radio, sticky save bar.
- Delete: confirm modal "Type club name to confirm".

### 5. Players Management
- Index: filters (club · sport · position · status · q), grid view toggle.
- Player card: photo, name, club, position badge, captain star.
- Form: photo uploader (crop preview), bilingual names, club+sport+position
  selects, jersey number, captain toggle, status.

### 6. Campaigns Management
- Index: cards with status badge, date range, vote count, public-link copy.
- Create: 3-step stepper
    1. **Info** — bilingual title/description, type, dates, max voters.
    2. **Categories** — add categories; for TOTS, preset 4 rows locked.
    3. **Candidates** — pick players (or clubs for team awards) per category
       with multi-select search.
- Detail: overview tab + live vote count + actions (publish/close/archive).

### 7. Categories & Candidates Setup
- Tabbed inside campaign: each category a collapsible panel.
- Candidate picker: autocomplete, drag-to-reorder, bulk import from club.

### 8. Public Voting Page
- Mobile-first single column. Hero with campaign title + countdown.
- Each category is its own card, with "Pick exactly N" helper.
- Candidates as big tap targets (photo + name + club), selected state
  shows emerald ring.
- Sticky footer with "Submit" button, disabled until all categories satisfy
  their required picks.
- After submit → `/vote/{token}/thanks` with confetti-less, calm success card.

### 9. Team of the Season Visual
- Football pitch illustration.
- 4 rows: GK (1) bottom, DEF (4), MID (3), ATT (3).
- Each winner tile = photo + name + club logo.
- Toggle AR/EN at top; direction auto-mirrors (pitch orientation fixed).

### 10. Results Page
- Tabs per category. Ranked table with `is_winner` highlighted row.
- Visibility chip: `hidden / approved / announced`.
- Action bar (permission-gated):
  `Recalculate` · `Approve` · `Hide` · `Announce`.
- Export: CSV / PDF (future).

---

## Flows

### Admin creates a Team of the Season campaign
```
Campaigns → + New Campaign
  Step 1: Info (type = team_of_the_season, start/end dates)
  Step 2: Categories — blueprint locked (3 ATT, 3 MID, 4 DEF, 1 GK)
  Step 3: Candidates — add players per category
→ Save as Draft → Review → Publish
  → system auto-transitions Published → Active at start_at
  → Active → Closed at end_at OR when max_voters reached
→ Calculate Results → Approve → Announce
```

### Public user votes
```
Open https://.../vote/{token}
  → see categories
  → select required picks per category
  → submit
  → /thanks
(duplicate blocked by voter_identifier unique index)
```
