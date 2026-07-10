# Forward Phase Plan

This plan starts from the current Provider Exchange codebase and reconciles the previous Phase 5-10 plan with the Field Nation product research captured on July 10, 2026.

The goal is not to clone Field Nation. The goal is to keep the useful operational patterns while designing against the exact provider-hostile problems we observed: opaque scoring, catch-all scope language, unreachable buyer contacts, weak filtering safeguards, and work-order ambiguity.

## Current Code Baseline

### Already Implemented

- Laravel authentication, registration, email verification scaffolding, password flows, and role assignment.
- Roles for admin, provider, buyer, and hybrid provider/buyer users.
- Provider profiles with public/private section visibility, narrative bio, service area, skill summary, structured service rows, tools, tool inventory, certifications, certification records, insurance status, rate card, travel policy, availability notes, website, phone, public contact toggle, private notes, attachments, ratings, and external profile snapshots.
- Buyer profiles with public/private section visibility, company description, service categories, hiring regions, structured hiring policies, locations, vendor onboarding, payment terms, website, contact email, public contact toggle, private notes, attachments, and ratings.
- Public provider and buyer directories with basic keyword filters, text-field filters, public-contact filter, sort by newest/name/rating, result counts, filter badges, and card views.
- Community feed posts and comments.
- Buyer job posts with title, status, service category, location, start time, time window, scope, structured scope fields, requirements, closeout rules, support/contact certification, risk flags, payment terms, vendor onboarding, visibility, comments, attachments, quotes, ratings, and accepted work-order creation.
- Provider quote submission, quote revision history, quote decline flow, and buyer quote acceptance.
- Work orders created from accepted quotes with buyer/provider participants, status transitions, timestamps, agreed terms, checklist items, checklist completion state, evidence requirements, evidence rules, scope/contact snapshots, first-class change requests, contact/support events, completion notes, messages, attachments, print packet, reviews, disputes, and ratings.
- Work-order status flow: assigned, en route, onsite, in progress, completed, buyer approved, disputed, closed, cancelled.
- Mutual work-order reviews with five-star overall rating and category ratings for communication, scope accuracy, payment reliability, workmanship, and timeliness.
- Universal ratings for provider profiles, buyer profiles, job posts, work orders, and disputes.
- Disputes with summary, claim, evidence notes, comments, attachments, peer votes, vote recommendations, and ratings.
- Database notifications and notification inbox.
- Starter Sanctum API endpoints for providers, jobs, current user, work orders, and disputes.
- Attachment policy with configurable disk, root, MIME allowlist, max size, previews, file metadata, and deletion.
- Admin overview with counts and recent users/jobs/work orders/disputes.
- Local-to-GitHub-to-ChristIT deployment workflow and docs.

### Current Weak Spots In Code

- Job posting now has structured scope and support certification fields, but the creation UI should be refined into reusable templates and stronger validation before real marketplace use.
- Provider and buyer directories have better filters than the prototype baseline, but still need saved searches, stronger availability signals, and deeper reliability filters.
- Work-order lists still need a UX pass for dense assigned-work tables and mobile-safe responsive alternatives.
- Work-order detail now supports contact/support failure logging, first-class change requests, post-work provider tag verification, running-late records, and schedule-update requests; the remaining need is a cleaner general report-problem workflow.
- Disputes and votes now have reason codes, but quorum rules, visibility rules, and deeper evidence timelines still need a future pass.
- Reviews now have category definitions, reviewee responses, report flow, edit windows, moderation queues, and imported/native reputation separation; the remaining reputation work is mostly aggregate performance and UI polish.
- Universal ratings are flexible, but category names still need stronger governance where they are not backed by explicit review categories.
- External profile imports now support structured work-category history, imported endorsements, imported review excerpts, visibility controls, proof attachments, and verification status; the remaining risk is better privacy copy and verification workflow polish.
- Profile services, tools, certifications, hiring policies, and locations are JSON arrays entered through newline text boxes. That is acceptable for prototype speed, but not durable enough for serious filtering.
- Notification preferences now expose channel intent, categories, event gates, digest, and quiet hours, but email/push delivery providers are intentionally not enabled yet.
- API endpoints now cover mobile-safe work-order actions, but native app payloads should be refined once real app requirements are clearer.
- Admin now includes moderation reports and audit logs, but still needs deeper operational filters, safety actions, and queue/system health panels.

## Revised Phases From Here

## Phase 5 - Reputation, Reviews, And Rating Governance

Purpose:

Build a provider-safe reputation system before expanding more marketplace mechanics. Field Nation's current Provider Success Score shows what not to do: do not replace a long public review history with an opaque score that changes provider standing through private feedback and hidden math.

Current code this builds on:

- `Review` model and `ReviewController`.
- `Rating` model and `RatingController`.
- Profile and work-order rating summaries.
- Imported profile snapshots.
- Disputes and dispute votes.

Features:

- Keep five-star reviews as the primary readable reputation signal.
- Define explicit review categories and descriptions.
- Split categories by review direction:
  - Buyer reviewing provider: communication, preparedness, workmanship, timeliness, closeout quality, professionalism.
  - Provider reviewing buyer: scope accuracy, contact availability, payment reliability, schedule reasonableness, support responsiveness, closeout fairness.
- Add category-definition copy directly in the review UI.
- Add a review response from the reviewee.
- Add an edit window for reviews, probably short and explicit.
- Add review report/flag flow.
- Add admin moderation queue for reported reviews.
- Add visible imported-history label for imported marketplace ratings.
- Keep imported history separate from native TSE reviews.
- Add "long-term history" and "recent operational metrics" side by side, not collapsed into one hidden score.
- Add operational metrics as evidence panels:
  - completed work orders,
  - average rating,
  - review count,
  - on-time status count,
  - unresolved disputes,
  - buyer contact failure reports,
  - scope change reports.
- Add rules preventing self-rating and duplicate category spam beyond the existing unique constraint.
- Add tests for review response, report flow, edit window, moderation visibility, and imported-vs-native display.

Provider-protection rules:

- No hidden private feedback should change public reputation without visible context.
- No percentile ranking should be shown unless the formula and data set are transparent.
- No composite badge should outrank the underlying review evidence.
- Any composite badge must be secondary, explainable, and auditable.

Implementation status:

- Completed first implementation pass.
- Added centralized category definitions.
- Added buyer/provider-specific review dimensions.
- Added reviewee response flow.
- Added participant report flow.
- Added admin moderation queue and moderation status changes.
- Added review edit-window enforcement.
- Added imported-history labeling on provider profiles.
- Verified with WSL `php artisan test` and `npm run build`.

## Phase 6 - Scope Clarity And Work-Order Safeguards

Purpose:

Prevent catch-all work orders and make scope expansion visible, structured, and reviewable.

Current code this builds on:

- `JobPost` free-form fields.
- `WorkOrder` agreed terms, checklist, evidence rules, change requests, and print packet.
- Quote acceptance flow.
- Dispute flow.

Features:

- Expand job posts with structured scope fields:
  - primary objective,
  - included work,
  - excluded work,
  - maximum onsite expectations,
  - expected duration,
  - required tools,
  - required skills,
  - required certifications,
  - required safety gear,
  - deliverables,
  - closeout conditions,
  - buyer-provided equipment,
  - provider-provided equipment,
  - return-shipment expectations,
  - parking/access notes,
  - onsite restrictions.
- Keep long buyer instructions available, but make them supplemental.
- Add an anti-catch-all rule: supplemental instructions cannot override structured scope fields.
- Add "scope clarity" status on job cards and work orders.
- Add risk badges:
  - broad scope,
  - unclear closeout,
  - missing deliverables,
  - missing contact backup,
  - missing required tools,
  - compressed schedule,
  - long pasted instruction block,
  - return shipment required,
  - site access uncertain.
- Convert change requests from JSON blobs into first-class records with:
  - requester,
  - reason,
  - scope impact,
  - schedule impact,
  - pay/terms impact,
  - status,
  - acceptance/denial,
  - audit trail.
- Add "out-of-scope request" workflow.
- Add "support/contact failed" workflow.
- Tie scope and contact failures into disputes.
- Add tests that catch-all text cannot silently replace structured scope.

Provider-protection rules:

- A provider should be able to understand the job without reading a book.
- If a buyer wants extra work onsite, it should become a change request.
- Undefined scope should not become provider fault.
- Dispute reasons should distinguish poor provider performance from buyer-created ambiguity.

Implementation status:

- Completed first implementation pass.
- Added structured job scope fields and support/contact certification fields.
- Added computed scope clarity and risk flags on jobs and work orders.
- Snapshotted scope and contact commitments when a quote becomes a work order.
- Converted change requests to first-class records with reason and impact fields while preserving legacy JSON fallback.
- Added contact/support event records for contact failed, support unavailable, site contact unavailable, and contact reached.
- Added print packet visibility for scope safeguards, support contacts, change requests, and contact/support events.
- Added reason codes to disputes and peer votes.
- Verified with Windows `php artisan test`.

## Phase 7 - Contact Accountability And Support Availability

Purpose:

Make buyer/site support availability a formal obligation for scheduled work.

Current code this builds on:

- Buyer profile contact fields.
- Job post location and vendor onboarding fields.
- Work-order messages.
- Work-order status history.
- Disputes.

Features:

- Add job/work-order contact records:
  - primary onsite contact,
  - backup onsite contact,
  - buyer dispatch contact,
  - technical bridge contact,
  - escalation contact,
  - support channel,
  - expected response time,
  - support availability window,
  - phone/email/bridge type.
- Add buyer certification checkbox before publishing or assigning work:
  - listed contacts are accurate,
  - support will be available during the work window,
  - escalation path is valid,
  - site access instructions are current.
- Add provider action: "Contact failed."
- Add provider action: "Support unavailable."
- Add provider action: "Site contact unavailable."
- Add log fields:
  - attempted channel,
  - attempted at,
  - result,
  - notes,
  - evidence attachment.
- Add contact-failure count to work-order detail.
- Add contact support status to print packet.
- Add contact reliability to buyer profile metrics.
- Add contact failure as a review category and dispute reason.
- Add tests around contact certification and contact-failure logging.

Provider-protection rules:

- If support is required, support availability must be explicit.
- If contacts fail, that should be evidence, not a buried message.
- Buyer reliability should include support availability, not only payment.

Implementation status:

- Completed first implementation pass alongside Phase 6.
- Job creation now captures primary, backup, dispatch, bridge, escalation, support channel, response time, support window, and certification.
- Work orders preserve the accepted job contact snapshot.
- Providers can log contact/support events as evidence.
- Contact issue counts show on the work-order detail.
- Print packets include contact/support coverage and logged contact events.
- Dispute reason codes include unreachable contact and support unavailable.
- Verified with Windows `php artisan test`.

## Phase 8 - Available Work Board And Filtering System

Purpose:

Build the marketplace scanning surface. Field Nation's available-work grid is useful because it is dense and fast, but TSE should add provider-protection filters and risk signals.

Current code this builds on:

- `jobs.index` public list.
- `JobPostController` keyword/status filters.
- Provider/buyer directory filter components.
- Ratings and buyer profiles.

Features:

- Replace or supplement the current job card list with a dense available-work table.
- Columns:
  - title,
  - category,
  - buyer,
  - rough location,
  - onsite/remote,
  - pay type,
  - posted terms,
  - schedule type,
  - start/date window,
  - quote/request count,
  - buyer rating,
  - scope clarity,
  - support certified,
  - risk badges.
- Keep card/mobile view as a responsive alternative.
- Add compact top filter bar:
  - work category,
  - coverage/radius or remote mode,
  - keyword search,
  - reset,
  - saved search,
  - advanced filters.
- Add advanced filters:
  - pay floor,
  - fixed/hourly/blended,
  - schedule type,
  - hard start,
  - date range,
  - buyer rating,
  - buyer contact reliability,
  - payment terms,
  - scope clarity,
  - required certification,
  - required tools,
  - remote eligible,
  - travel distance,
  - exclude broad-scope jobs.
- Add saved searches.
- Add default provider preference filters.
- Add "hide risky jobs" provider preference.
- Add accessible labels for all icon/filter/table actions.
- Add tests for filters and query behavior.

Provider-protection rules:

- Request/quote count should be informational, not pressure.
- Jobs should not rank purely by buyer preference or low provider rate.
- Risk flags should be visible before a provider opens the work order.

Implementation status:

- Completed first implementation pass alongside Phase 9.
- Replaced the jobs list with a denser available-work board.
- Added filters for category, technician level, scope clarity, support certification, remote eligibility, and hide risky jobs.
- Added columns for buyer, location, category, specialty, technician level, schedule, work mode, pay type, terms summary, quote count, scope clarity, support certification, and risk badges.
- Verified with Windows `php artisan test`.

## Phase 9 - Taxonomy, Competency Tags, And Profile Evidence

Purpose:

Move from text boxes and arbitrary strings to a clean taxonomy that powers profiles, job matching, filters, work summaries, and competency levels.

Current code this builds on:

- Provider profile services/tools/certifications JSON.
- Buyer service categories and hiring policies.
- Job `service_category`, `required_skills`, and `required_tools`.
- Platform research taxonomy.
- Work summary concept from Field Nation profile.

Features:

- Create taxonomy tables:
  - work category families,
  - work specialties,
  - skill tags,
  - tool tags,
  - certification tags,
  - competency levels.
- Seed starter taxonomy based on observed categories:
  - Access & Alarms,
  - A/V & Digital Signage,
  - Cameras,
  - EV Equipment,
  - Fiber Cabling,
  - Kiosk / ATM,
  - Low Voltage Cabling,
  - Office Equipment,
  - Point of Sale,
  - Retail Services,
  - Server & Networking,
  - Telecom,
  - Other Trades.
- Add specialty examples:
  - access control,
  - burglar alarm,
  - fire alarm,
  - audio visual,
  - digital signage,
  - CCTV camera,
  - IP camera,
  - EV charging station,
  - fiber testing,
  - low voltage runs,
  - low voltage testing,
  - copier,
  - printer,
  - Mac device,
  - Windows device,
  - POS,
  - self-checkout,
  - networking,
  - wireless networking,
  - server/storage,
  - POTS,
  - VoIP-SIP.
- Add provider competency levels:
  - smart hands,
  - installer,
  - experienced installer,
  - troubleshooter,
  - advanced troubleshooter,
  - project lead,
  - specialist.
- Add evidence-backed tags:
  - self-declared,
  - imported history,
  - completed TSE work,
  - buyer endorsed,
  - certification verified,
  - admin verified.
- Add provider work summary by category based on completed native/imported work.
- Add searchable tool inventory using normalized tool tags.
- Add certification uploads tied to certification records.
- Add buyer hiring-category taxonomy.
- Add tests for taxonomy assignment, filtering, and display.

Provider-protection rules:

- Tags should distinguish smart-hands work from skilled technical work.
- Buyers should not be able to demand advanced work while labeling it smart hands.
- Certification proof should be controlled by the provider and privacy-aware.

Implementation status:

- Completed first implementation pass alongside Phase 8.
- Added `taxonomy_terms` for categories, specialties, skills, tools, and certifications.
- Added provider/tag pivot records with evidence sources.
- Added starter seed taxonomy for field-service categories and common traits/tools/certifications.
- Added explicit technician level definitions:
  - Level 1 smart hands,
  - Level 2 installer,
  - Level 3 troubleshooter,
  - Level 4 specialist,
  - Level 5 project lead.
- Added provider maximum technician level and self-declared tags in profile editing.
- Added buyer job technician-level selection and scope mismatch risk detection for smart-hands jobs that include troubleshooting or certification requirements.
- Added provider directory filters for technician level and taxonomy tag.
- Added buyer post-work tag endorsement/disagreement with buyer-endorsed evidence updates on provider profiles.

## Phase 10 - Import Tooling And Marketplace History

Purpose:

Let providers preserve their earned history from Field Nation, WorkMarket, and similar platforms without scraping private systems or publishing sensitive details by default.

Current code this builds on:

- `ExternalProfileImport` model.
- Provider profile import form.
- Attachment system.
- Imported profile display section.

Features:

- Upgrade import form from a basic snapshot to a guided manual import wizard.
- Import sections:
  - platform,
  - external ID,
  - profile URL,
  - rating,
  - review count,
  - completed jobs,
  - client count,
  - work categories,
  - endorsements,
  - success metrics,
  - review examples,
  - screenshots/proof attachments,
  - import visibility.
- Add imported category work summary.
- Add imported endorsements:
  - communication,
  - professionalism,
  - problem solving,
  - preparedness,
  - work quality,
  - responsiveness.
- Add imported operational metrics:
  - on-time count/rate,
  - backout count/rate,
  - completed jobs,
  - buyer/client count.
- Add privacy controls:
  - private only,
  - profile summary only,
  - public proof attachment,
  - public selected review.
- Add imported/native separation in profile UI.
- Add "unverified imported history" label unless confirmed.
- Add admin verification path for selected imports.
- Add tests for import storage, visibility, and display.

Provider-protection rules:

- Do not scrape authenticated platforms automatically unless a future legal/terms review explicitly allows it.
- Do not publish work-order IDs, addresses, buyer names, or private customer details by default.
- Imported history should help providers prove experience without exposing sensitive client data.

Implementation status:

- Completed guided manual import first pass.
- Added import visibility modes for private, summary-only, selected reviews, and public proof attachments.
- Added imported work categories, endorsements, operational metrics, selected review excerpts, and proof upload support.
- Added provider-attested/admin-verified/needs-more-proof verification status.
- Added admin imported-history verification queue.
- Kept imported history separate from native TSE reputation.
- Verified with Windows `php artisan test`.

## Phase 11 - Mobile-Safe Work-Order Actions And API Expansion

Purpose:

Prepare the system for mobile apps and onsite workflows without building native apps yet.

Current code this builds on:

- Sanctum API.
- Work-order status transitions.
- Attachments.
- Messages.
- Disputes.
- Notifications.

Features:

- Add versioned API resources.
- Add token scopes.
- Add API endpoints for:
  - assigned work orders,
  - available jobs,
  - work-order detail,
  - status transition,
  - checklist completion,
  - message send,
  - evidence upload,
  - running-late record,
  - schedule-update request,
  - contact-failure log,
  - support-unavailable log,
  - submit-for-review,
  - dispute open,
  - dispute evidence attach.
- Add geolocation check-in groundwork:
  - optional coordinates,
  - timestamp,
  - accuracy,
  - privacy warning,
  - audit trail.
- Add mobile-safe response payloads.
- Add tests for API authorization and participant boundaries.

Provider-protection rules:

- Location should be used for work-order proof only, not broad tracking.
- Every mobile action should leave an audit trail visible to the provider.
- Support/contact failures should be as easy to log as check-in.

Implementation status:

- Completed first implementation pass.
- Added `/api/v1` mobile-safe work-order endpoints behind Sanctum auth and API throttling.
- Added token ability gates for `jobs:read`, `work-orders:read`, `work-orders:write`, `work-orders:upload`, and `disputes:write`.
- Added participant authorization on work-order detail and every work-order action.
- Added mobile actions for status transition, checklist update, message send, evidence upload, contact/support event logging, running-late notice, schedule-update request, and dispute opening.
- Added `work_order_mobile_events` as an audit trail for mobile actions.
- Added optional latitude, longitude, accuracy, and occurred-at fields to mobile action records with explicit privacy copy in API payloads.
- Added baseline HTTP security headers and an explicit `api` rate limiter.
- Verified with Windows `php artisan test`.

## Phase 12 - Notification Preferences And Event Channels

Purpose:

Make alerts useful without becoming pushy.

Current code this builds on:

- `NotificationPreference` model.
- Laravel database notifications.
- Notification inbox.
- Existing notification sends from work-order transitions, change requests, reviews, and disputes.

Features:

- Add notification preference UI.
- Event categories:
  - new matching job,
  - quote received,
  - quote accepted/declined,
  - work-order status changed,
  - schedule update requested,
  - running late,
  - contact failed,
  - support unavailable,
  - change request,
  - message received,
  - evidence uploaded,
  - review received,
  - dispute opened,
  - dispute vote received,
  - moderation action.
- Channels:
  - in-app,
  - email,
  - future push.
- Add digest vs immediate settings.
- Add quiet hours.
- Add unsubscribe/preferences link for emails once email sending is added.
- Add tests for preference gating.

Provider-protection rules:

- Users must explicitly opt into email or push.
- Critical work-order events should remain visible in app even if email/push is off.
- No modal begging for notifications.

Implementation status:

- Completed first implementation pass alongside Phase 13A.
- Added notification preference editing on the notifications page.
- Added channel intent controls for in-app, email, and future push without enabling email/push delivery prematurely.
- Added category controls for matching jobs, quote updates, work-order activity, reviews/reputation, and disputes/moderation.
- Added event-level controls for specific notification types.
- Added digest preference and quiet-hours fields for future email/push delivery policy.
- Added notification gating in `ExchangeEventNotification` so existing send paths respect user preferences.
- Verified with Windows `php artisan test`.

## Phase 13 - Moderation, Admin Operations, And Audit Logs

Purpose:

Make the platform governable before it grows.

Current code this builds on:

- Admin overview.
- Ratings, reviews, disputes, attachments, users.
- Notification system.

Features:

- Add audit log table and model.
- Log:
  - role changes,
  - profile edits,
  - job publication,
  - quote acceptance,
  - work-order status transitions,
  - change requests,
  - contact failures,
  - dispute events,
  - review edits,
  - review reports,
  - moderation actions,
  - attachment deletion.
- Add admin moderation queues:
  - reported reviews,
  - reported profiles,
  - reported jobs,
  - reported attachments,
  - dispute review.
- Add content status fields where needed.
- Add admin filters.
- Add user safety actions:
  - warn,
  - temporarily restrict,
  - suspend,
  - restore.
- Add tests for admin-only access and audit creation.

Provider-protection rules:

- Moderation should be reviewable.
- A buyer should not be able to silently damage a provider's reputation.
- A provider should be able to see what changed and why.

Implementation status:

- Completed first implementation pass alongside Phase 12A.
- Added reusable `audit_logs` for admin and operational actions.
- Added audit writes for review reports/moderation, imported-history verification, attachment deletion, work-order transitions, change requests, contact events, quote acceptance, and mobile API actions.
- Added reusable `moderation_reports` for provider profiles, buyer profiles, jobs, and attachments.
- Added report forms to public profile, job, and attachment surfaces.
- Expanded the admin console with open moderation reports and a recent audit log panel.
- Added admin triage controls for moderation reports.
- Verified with Windows `php artisan test`.

## Phase 14 - Deployment, Scaling, And Reliability Hardening

Purpose:

Keep the app easy to deploy while preparing for real usage.

Current code this builds on:

- Existing deployment docs.
- Install/update scripts.
- Laravel queue/cache/session defaults.
- Attachment storage config.

Features:

- Document production queue worker setup.
- Document scheduler setup.
- Document PHP-FPM pool expectations.
- Document Nginx route/base-path assumptions.
- Add backup/export workflow.
- Add log rotation notes.
- Add cache/session/queue Redis migration plan.
- Add database indexes for:
  - job filters,
  - profile filters,
  - ratings,
  - reviews,
  - disputes,
  - work-order status,
  - notification lookups.
- Add cached rating/reputation aggregates.
- Add attachment lifecycle plan:
  - object storage,
  - malware scanning,
  - thumbnail generation,
  - quotas,
  - private file access.
- Add CI checks if GitHub Actions becomes useful.

Provider-protection rules:

- Evidence files must not disappear.
- Private attachments must stay private.
- Update/deployment failures should not corrupt active work orders.

Implementation status:

- Completed first implementation pass.
- Added high-use database indexes for job filters, profile filters, quotes, work-order status/timelines, messages, ratings, reviews, disputes, attachments, imported-history queues, and notification inbox lookups.
- Added `scripts/backup.sh` for SQLite, MySQL/MariaDB, and PostgreSQL backups with optional `storage/app` archive support.
- Added `scripts/health-check.sh` for deployment smoke checks.
- Added optional `TSE_BACKUP_BEFORE_UPDATE=1` pre-update backup support to `scripts/update.sh`.
- Expanded deployment docs for queue workers, scheduler, PHP-FPM pool expectations, Redis migration, backup/export workflow, health checks, log rotation, storage scaling, and database scaling.
- Deferred cached rating/reputation aggregates until real usage identifies slow pages and expensive queries.

## Phase 15 - UX Polish And Accessibility Pass

Purpose:

Make the app pleasant and understandable without hiding important operational detail.

Current code this builds on:

- Tailwind UI system.
- Light/dark theme.
- Shared components.
- Directory pages.
- Job and work-order pages.

Features:

- Add consistent dense-table and card-list patterns.
- Add responsive table alternatives for mobile.
- Add accessible labels to icon-only buttons.
- Add empty-state guidance for dead sections such as recommendations.
- Add inline help for structured scope and review categories.
- Add badges with consistent meanings.
- Add saved filter chips.
- Add profile completion checklist.
- Add buyer profile completion checklist.
- Add public/private visibility indicators.
- Add print/PDF polish for work-order packets.

Provider-protection rules:

- Important risk flags should be visible, not hidden in details.
- Verbose buyer instructions should be collapsible and structured.
- The system should guide buyers toward clear jobs instead of rewarding catch-all text dumps.

Implementation status:

- Completed first implementation pass.
- Added shared Blade patterns for empty states, form controls, and secondary actions.
- Refined the jobs board with labeled filters, active filter badges, a dense desktop table, and mobile card alternatives.
- Refined provider and buyer directory filters with shared control styling and reusable empty states.
- Refined the work-order list with a dense desktop table, mobile cards, checklist/status badges, and direct print access.
- Added accessibility state/controls to the mobile navigation menu button.
- Improved work-order print packets with a summary strip, generated timestamp, scope-safeguard notice, and print page-break handling.

## Recommended Immediate Sequence

1. Cached rating/reputation aggregates after slow pages are measured.
2. Future mobile/API expansion after native app requirements are clearer.
3. Future email/push delivery implementation after the sender and push providers are explicitly selected.
4. Deeper admin operations after real moderation and support workflows emerge.

## Key Design Commitments

- Keep reviews human-readable and five-star based.
- Never replace evidence with an opaque platform score.
- Treat buyers and providers as independent businesses.
- Do not set or recommend market rates.
- Let providers control rate cards and terms.
- Make scope explicit before assignment.
- Treat scope expansion as a change request, not a provider surprise.
- Require buyer support/contact certification for active work windows.
- Make contact failure and support failure evidence easy to record.
- Keep available-work filters powerful but visually compact.
- Use nested taxonomies for work categories and competencies.
- Separate imported marketplace history from native TSE reputation.
- Keep sensitive third-party data private by default.
