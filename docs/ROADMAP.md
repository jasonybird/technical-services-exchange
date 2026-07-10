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
- Configurable attachment storage policy with upload root, disk, size cap, MIME allowlist, image previews, file metadata, and deletion.
- Mutual reviews with category metrics.
- Peer-review disputes with comments, evidence, structured recommendations, and public vote breakdowns.
- Universal community ratings for buyers, providers, job posts, work orders, and disputes.
- Database notification events and notification inbox.
- Starter Sanctum API surface for future app clients.
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
6. Dispute workflow: better evidence timelines, reason-coded votes, quorum/visibility rules, and admin/community moderation tools.
7. Notifications: channel preferences, email templates, and event-specific subscription controls.
8. API/mobile prep: token-scoped endpoints, versioned API resources, geolocation check-in groundwork, and mobile-safe work-order actions.
9. Import tooling: guided manual imports for Field Nation, WorkMarket, and similar profile/review history.
10. Competency tags and levels: provider/buyer tag taxonomies, smart-hands entry lane, specialty tags such as network, POTS, POS, AV, cabling, installer, and troubleshooter, plus earned level badges based on completed work and reputation signals.
11. Admin operations: audit logs, queue health, system status, content moderation, and backup/export workflows.
12. Deployment hardening: production environment docs, queue worker setup, scheduler setup, storage policy, and server provisioning notes.
13. License and runtime audit: generate and review PHP and JavaScript dependency license reports before adding external theme kits, map any risky package to a replacement, and keep required local/runtime extensions such as `pdo_sqlite` documented for testability.

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
- Production deployments need explicit PHP-FPM pool, Nginx, scheduler, queue worker, backup, and log rotation documentation.

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
