# Provider Exchange Roadmap

Provider Exchange is a Laravel application for a provider-centered field-service network. The project should stay open source, avoid rate-setting, avoid payment custody in the first version, and keep reputation mutual between buyers and providers.

## Current Foundation

- Laravel app with Breeze authentication.
- Role support for admin, provider, buyer, and hybrid users.
- Seeded demo users for local testing.
- Provider and buyer profile directories.
- Community feed posts.
- Buyer job posts and provider quote submission.
- Quote revisions, quote decline flow, and quote acceptance.
- Work-order creation from accepted quotes.
- Work-order status transitions, messages, attachments, deliverables, and completion notes.
- Structured job scope, scope clarity status, risk badges, and anti-catch-all supplemental instruction guardrails.
- Buyer contact/support certification, work-order contact snapshots, and provider contact/support failure logging.
- First-class work-order change requests with reason codes, impact fields, status lifecycle, and print-packet visibility.
- Dense available-work board with filters for category, technician level, scope clarity, support certification, remote eligibility, and risk suppression.
- Normalized starter taxonomy for work categories, specialties, skills, tools, certifications, and provider tag evidence sources.
- Explicit technician level ladder from smart hands through project lead.
- Guided imported-history records for Field Nation, WorkMarket, and similar platforms with privacy controls, selected review excerpts, metrics, proof attachments, and admin verification status.
- Post-work provider tag verification for completed work orders, including buyer-confirmed level evidence, confirmed/disputed declared tags, suggested tags, and buyer-endorsed profile tag evidence.
- Configurable attachment storage policy with upload root, disk, size cap, MIME allowlist, image previews, file metadata, and deletion.
- Mutual reviews with category metrics.
- Peer-review disputes with comments, evidence, structured recommendations, and public vote breakdowns.
- Universal community ratings for buyers, providers, job posts, work orders, and disputes.
- Database notification events and notification inbox.
- Notification preference UI with category controls, event-level gates, digest/quiet-hours settings, and stored email/push intent for future channels.
- Admin governance with moderation reports for profiles/jobs/attachments, expanded moderation queues, and reusable audit logs.
- Versioned Sanctum API surface for future app clients, including mobile-safe work-order actions, token ability checks, action audit records, and optional geolocation evidence.
- Baseline API/security hardening with rate limiting and HTTP security headers.
- MIT license and no-payment/no-rate-setting guardrails.

## Operating Principles

- Providers and buyers are independent businesses.
- The platform should not set, recommend, or enforce market rates.
- Providers should control rate cards and terms.
- Buyers should control posted offer terms.
- Payment remains direct between buyer and provider unless a compliant payment partner is deliberately added later.
- Reputation should be mutual, transparent, and appealable through community review.
- Dispute voting is a community reputation signal, not legal adjudication.
- Runtime dependencies, UI components, and themes should stay open-source friendly. Avoid proprietary theme kits, unclear asset licenses, or dependencies that would restrict commercial, nonprofit, or community scaling.

## Next Build Phases

1. Profile depth: richer provider galleries for vans, tools, job photos, certifications, insurance, and past work; buyer galleries for company identity, logos, headers, locations, and example work; equipment/tool pickers with reusable tags; certification upload/proof records with starter certification lists; service coverage maps; and imported review summaries.
2. Directory quality: saved searches, better filtering, profile badges, availability signals, and stronger buyer/provider discovery pages.
3. Asset and storage management: configurable upload roots, file policy, attachment previews, deletion, and update-path storage link handling.
4. Work-order depth: checklist templates, required evidence rules, appointment windows, onsite timestamps, change-request records, and print/PDF export for work-order packets.
5. Reputation refinement: rating category definitions, anti-abuse controls, edit windows, report flows, and moderation views.
6. Scope clarity and work-order safeguards: structured job scope, risk badges, anti-catch-all rules, first-class change requests, and reason-coded dispute paths.
7. Contact accountability and support availability: certified contacts, support windows, event logs for failed support/contact attempts, and contact reliability evidence.
8. API/mobile prep: token-scoped endpoints, versioned API resources, geolocation check-in groundwork, and mobile-safe work-order actions.
9. Import tooling: guided manual imports for Field Nation, WorkMarket, and similar profile/review history.
10. Post-work verification: buyer endorsement or disagreement with provider level/tags after completed work orders, earned evidence upgrades, and transparent tag history.
11. Admin operations: audit logs, queue health, system status, content moderation, and backup/export workflows.
12. Deployment hardening: production environment docs, queue worker setup, scheduler setup, storage policy, and server provisioning notes.
13. License and runtime audit: generate and review PHP and JavaScript dependency license reports before adding external theme kits, map any risky package to a replacement, and keep required local/runtime extensions such as `pdo_sqlite` documented for testability.

## Phase Checkpoint

Phases 1-15A have been implemented locally through UX/accessibility polish. The next active planning block is:

1. Cached reputation aggregates after real slow pages are measured.
2. Future email/push delivery and native-app API expansion once providers are selected.
3. Deeper operations/admin controls after real moderation and support workflows emerge.

Work-order safeguards to carry into the next implementation passes:

- Scope clarity: every job/work order should identify the primary objective, included work, excluded work, required tools/equipment, maximum onsite expectations, and the change-request path for out-of-scope work.
- Anti-catch-all rule: long pasted instruction blocks should not be allowed to override the structured scope fields or create undefined onsite obligations.
- Contact accountability: buyers should certify that primary contact, backup contact, and support/escalation channels will be reachable during the scheduled work window.
- Evidence path: providers should be able to record unreachable contacts, unsupported scope expansion, and missing buyer support as structured events that can feed disputes and reviews.

Reputation safeguards to carry into Phase 5:

- Keep five-star reviews as a primary readable signal.
- Add transparent sub-metrics for operational behavior without replacing reviews with an opaque platform-controlled score.
- Show long-term history and recent operational metrics side by side.
- Separate imported marketplace history from native TSE reputation.
- Require explainable formulas, provider response rights, report/appeal paths, and moderation audit trails for any composite reputation badge.

Phase 5 implementation completed:

- Review category definitions are centralized in `config/reputation.php`.
- Buyer-to-provider and provider-to-buyer reviews now use explicit category sets.
- Reviewees can publish a response.
- Participants can report reviews for moderation.
- Admins can moderate reported reviews from the admin dashboard.
- Review edits are limited by a configurable edit window.
- Imported marketplace history is labeled separately from native TSE reputation.

Phase 6/7 implementation completed:

- Job posts now include structured scope fields for primary objective, included work, excluded work, maximum onsite expectations, duration, requirements, closeout conditions, equipment expectations, return shipment, access notes, restrictions, and supplemental instructions.
- Supplemental instructions are explicitly treated as reference material that cannot override structured scope boundaries.
- Job cards, job detail, and work orders show scope clarity, support certification, and computed risk badges.
- Accepted quotes snapshot the job scope and contact/support commitments into the work order.
- Change requests are first-class records with reason code, scope impact, schedule impact, terms impact, status, responder, and resolution notes.
- Providers can log contact failed, support unavailable, site contact unavailable, or contact reached events with channel, time, result, and notes.
- Work-order print packets include scope safeguards, contact/support coverage, change requests, and contact/support events.
- Disputes and peer votes now support reason codes such as scope expansion, unreachable contact, support unavailable, payment issue, and insufficient evidence.

Phase 8/9 implementation completed:

- The available jobs page is now a dense marketplace board with compact filters for work category, technician level, scope clarity, support certification, remote eligibility, and hide-risky mode.
- Job rows expose buyer, location, category, specialty, requested technician level, schedule, pay type, posted terms summary, quote count, scope clarity, support certification, and risk badges.
- Technician levels are defined in `config/technician-levels.php`:
  - Level 1 smart hands,
  - Level 2 installer,
  - Level 3 troubleshooter,
  - Level 4 specialist,
  - Level 5 project lead.
- Buyer job creation now requires a technician level and work mode, and can select normalized category/specialty plus pay type and posted terms summary.
- The app now has normalized taxonomy terms for work categories, specialties, skills, tools, and certifications.
- Provider profiles can declare a maximum technician level and self-declared taxonomy tags.
- Provider directory filters can match technician level and taxonomy tags.
- Taxonomy tag pivots track an evidence source, currently `self_declared`, with room for buyer endorsed, completed work, certification verified, and admin verified evidence.

Guided imported-history implementation completed:

- Provider profile editing now includes a guided manual import wizard for Field Nation, WorkMarket, and similar platforms.
- Imports can store platform ID, profile URL, rating, review count, completed jobs, client count, on-time rate, backout rate, work categories, imported endorsement categories, selected review excerpts, notes, and proof attachments.
- Imports have visibility modes: private only, public summary only, public selected reviews, and public proof attachments.
- Public provider profiles only display imported history according to the selected visibility.
- Imported history keeps a separate verification status: unverified, provider attested, admin verified, or needs more proof.
- Admin dashboard includes an imported-history verification queue.
- Imported history remains separate from native TSE reputation.

Post-work provider tag verification completed:

- Buyers and admins can verify provider level/tag evidence after a work order is completed, buyer approved, or closed.
- Verification records store the provider's declared level, the observed level, a level verdict, confirmed tags, disputed tags, buyer-suggested tags, and notes.
- Confirmed declared tags upgrade their provider-profile evidence source to `buyer_endorsed`.
- Provider profiles now show recent completed-work competency evidence separately from imported history and five-star reviews.
- Providers cannot self-verify their tags from a work order.

Phase 11A mobile API and security hardening completed:

- `/api/v1` now exposes mobile-safe endpoints for available jobs, assigned work orders, work-order detail, status transitions, checklist updates, messages, evidence uploads, contact/support events, running-late notices, schedule-update requests, and dispute opening.
- API tokens can be scoped by ability: `jobs:read`, `work-orders:read`, `work-orders:write`, `work-orders:upload`, and `disputes:write`.
- Work-order API actions enforce buyer/provider/admin participant boundaries.
- Mobile actions write to `work_order_mobile_events` with event type, user, payload, optional coordinates, optional accuracy, and occurred-at timestamp.
- Geolocation is optional and documented in the API response as work-order evidence only.
- API routes are throttled through an explicit `api` rate limiter.
- Application responses include baseline security headers: content-type sniffing protection, same-origin frame policy, strict origin referrer policy, limited permissions policy, and HSTS on secure requests.

Phase 12A/13A notification and admin governance completed:

- Notification settings now live on the notifications page with in-app, email intent, push intent, digest, quiet-hours, category, and event-level controls.
- `ExchangeEventNotification` gates database notifications through user preferences while defaulting existing users to enabled.
- Email and push settings are stored as explicit intent, but no email/push delivery is enabled until those providers are selected.
- Users can report provider profiles, buyer profiles, jobs, and attachments for moderation.
- Admins can triage moderation reports from the admin console.
- Audit logs record selected operational and moderation events, including review moderation, imported-history verification, attachment deletion, work-order actions, quote acceptance, and mobile API actions.
- Admins can review recent audit activity from the admin console.

Phase 14A deployment/scaling hardening completed:

- Added database indexes for job-board filters, profile filters, quotes, work-order timelines, reviews, ratings, disputes, attachments, imported-history queues, and notification inbox lookups.
- Added `scripts/backup.sh` for SQLite, MySQL/MariaDB, and PostgreSQL database backups with optional `storage/app` archive support.
- Added `scripts/health-check.sh` for deployment smoke checks.
- Added optional `TSE_BACKUP_BEFORE_UPDATE=1` pre-update backups to `scripts/update.sh`.
- Documented queue workers, scheduler, PHP-FPM expectations, Redis migration, backups, health checks, log rotation, storage scaling, and database scaling.
- Kept cached reputation aggregates deferred until real traffic shows which pages need them.

Phase 15A UX/accessibility polish completed:

- Added shared form-control, secondary-action, and empty-state patterns.
- Improved the jobs board with a page header, labeled filters, active filter badges, desktop table, and mobile card alternative.
- Improved provider and buyer directory filters with shared control styling and reusable empty states.
- Improved work-order listing with a dense desktop table, mobile cards, checklist/status badges, and direct print access.
- Added accessible mobile-navigation button state and controls.
- Improved work-order print packets with a summary strip, generated timestamp, scope-safeguard notice, and print page-break handling.

Available-work safeguards to carry into directory and job-list phases:

- Available jobs should expose pay type, provider terms, schedule type, work type, rough location, buyer reliability, support/contact certification, scope clarity status, and request/quote count.
- Filters should support distance, pay floor, work category, schedule type, buyer rating, scope clarity, and support availability.
- Risk badges should identify broad scope, missing contact backup, missing deliverables, missing required tools, compressed schedules, and unclear closeout requirements.
- Keep the primary filter bar compact: work category, coverage/radius or remote mode, keyword search, reset, and saved/advanced filters.
- Model work categories as nested taxonomy records with broad families and specific specialties.
- Keep every filter and table action visibly labeled or accessible to screen readers. Shared Phase 15A patterns now cover the primary jobs, provider, buyer, and work-order listing pages.

The July 10, 2026 Field Nation product review is captured in `docs/PLATFORM_RESEARCH.md` and should inform Phases 5, 8, 9, and 10 without copying private third-party work-order data into this repository.

The reconciled forward plan from the current codebase is captured in `docs/FORWARD_PHASE_PLAN.md`. That document supersedes the older loose Phase 5-10 outline for next implementation sequencing while preserving the same product direction.

## Scaling Risks To Track

- Search and filtering will need proper database indexes first, then a dedicated search service if directory/job volume grows.
- Ratings and reputation pages should eventually use cached aggregates instead of recalculating averages and counts on every page view.
- Notifications should move from the database queue to Redis-backed queues before high-volume alerting or email delivery.
- Sessions, cache, and queues should move to Redis before running more than one web node.
- Uploaded evidence, profile images, and deliverable files should move to shared or object storage before multi-server deployment.
- Attachment records already track disk/path metadata, but production scaling still needs object-storage lifecycle rules, upload malware scanning, quota policy, and thumbnail generation.
- Work-order timelines, dispute activity, messages, and notification tables need careful indexes before large production use.
- Realtime chat, websocket presence, mobile push, and geolocation check-ins should be added as deliberate service layers instead of bolted onto normal page requests.
- Background imports, image processing, moderation summaries, and reputation recalculation should run through queue workers rather than web requests.
- Production deployments now have explicit PHP-FPM, Nginx, scheduler, queue worker, backup, health-check, and log rotation documentation. These should be revisited after real traffic establishes memory, queue, and slow-query patterns.

## Future/Deferred

- Escrow or payment custody.
- Automated rate recommendations.
- Native mobile apps.
- Real-time chat/websocket presence.
- Marketplace-wide dispute binding beyond voluntary community reputation.
- Automated scraping of third-party platforms where authentication, terms, or privacy boundaries are unclear.
- Automated competency promotion rules beyond transparent, reviewable tag and level signals.
- Work-order template libraries for buyers and providers, including reusable sample work orders that can seed future jobs.
- Broader licensed trade profiles, such as electricians, where state certification proof and trade-specific scopes can be added without hard-coding the platform around only IT technicians.
