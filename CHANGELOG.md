# Changelog

All notable project changes should be recorded here before or with the matching Git commit.

This project uses Git as the primary rollback trail. The `.old` convention is reserved for risky manual rewrites, legacy file replacement, or generated artifact recovery where a second local copy is useful before editing.

## 2026-07-10

### Added

- Added a Phase 1 UI system pass with dark/light theme support, shared page/action/stat components, a product-focused home page, and a more useful dashboard.
- Added roadmap guardrails for open-source-friendly dependencies, public themes, and future license audits.
- Documented `pdo_sqlite` and `sqlite3` as required PHP extensions for SQLite-backed local tests and first-pass deployments.
- Documented the preferred local-to-GitHub-to-remote-update workflow and the ChristIT conversion from rsync test deploy to Git checkout.
- Created the Laravel Provider Exchange prototype with Breeze authentication, seeded demo users, and role support for admins, providers, buyers, and hybrid accounts.
- Added provider and buyer profile management with service areas, profile details, and external profile snapshot records.
- Added social posts, buyer job posts, provider quotes, quote revisions, quote decline flow, and buyer quote acceptance.
- Added work orders with buyer/provider participants, status transitions, deliverables, evidence attachments, completion notes, and work-order messages.
- Added mutual reviews, category review metrics, peer-review disputes, dispute comments, evidence files, and structured dispute votes.
- Added database notifications and a notification inbox with unread counts and mark-read actions.
- Added a universal polymorphic rating layer for provider profiles, buyer profiles, job posts, work orders, and disputes.
- Added search/filter forms for provider, buyer, and job directories.
- Added MIT license and first-pass open-source/platform guardrails.
- Added deployment documentation covering runtime requirements, ChristIT `/tse` deployment assumptions, and remote change tracking.
- Added optional route base-path config and a checked-in Nginx helper for the ChristIT `/tse` Laravel deployment.
- Added first-pass Ubuntu install and update scripts for GitHub-based deployments.

### Verified

- `php artisan test` passed with 35 tests and 116 assertions.
- `npm run build` passed with a production Vite build.
- `https://christit.com/tse/login` loads publicly.
- Seeded admin login succeeds on `https://christit.com/tse` and reaches `/tse/dashboard`.

### Notes

- Existing Git subjects are terse because commit message input in this environment repeatedly collapsed longer subjects to `Add`. This changelog is the durable human-readable record for those early commits.
