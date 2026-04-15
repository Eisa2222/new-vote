# SFPA Electronic Voting System

**Saudi Football Players Association — E-Voting Platform**

Laravel 11 + PHP 8.3 + MySQL. Modular monolith. Bilingual (AR/EN) with full RTL/LTR.
Super Admin panel + public voting pages. API-first with Blade views for admin.

## Setup

```bash
composer create-project laravel/laravel .   # scaffold Laravel 11
# then overlay this repository on top
composer require spatie/laravel-permission laravel/sanctum
php artisan migrate --seed
php artisan serve
```

## Architecture

Modular Laravel Architecture under `app/Modules/*`. Each module owns its Models,
Actions, Requests, Resources, Policies, Controllers, Enums, Events, Jobs, and Tests.
Thin controllers → Actions do the work → Resources shape output → Policies gate
access. No Repository Pattern. Services only where shared logic is real.

## Modules

| Module     | Purpose                                                     |
|------------|-------------------------------------------------------------|
| Shared     | Base classes, common enums, traits, HasTranslations helpers |
| Users      | Internal users, roles, permissions, activity log            |
| Clubs      | Clubs (name_ar, name_en, logo, status) + sports pivot       |
| Sports     | Sport types (football, basketball, ...)                     |
| Players    | Players per club+sport, position enum, photos               |
| Campaigns  | Voting campaigns, categories, candidates, lifecycle         |
| Voting     | Public voting submission, duplicate prevention, close rules |
| Results    | Result calculation, approval, hidden/announced states       |

## Key Design Choices

- **public_token** on campaigns = unguessable URL slug for public voting.
- **voter_identifier** = hash(ip + user_agent + campaign_id) by default; strategy
  is swappable (email OTP, SMS OTP, Nafath, ...) via `VoterIdentityStrategy`.
- **Team of the Season** validated by `TeamOfTheSeasonDistributionRule`:
  3 attack + 3 midfield + 4 defense + 1 goalkeeper = 11.
- **Results visibility** is independent of calculation — results may be
  `calculated` but still `hidden` until explicitly `announced`.
- **Auto-close** is handled by the `CloseExpiredCampaignsJob` cron + a vote-time
  check inside `SubmitVoteAction` (cap reached → close immediately).
