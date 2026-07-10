# Provider Exchange

Provider Exchange is an early Laravel prototype for a provider-centered field service network and work-order exchange.

The code is intended to be open source under the MIT License.

The product goal is not to clone Field Nation. The first version focuses on:

- provider and buyer profiles;
- community posts;
- buyer job posts;
- provider quotes;
- work-order assignment and status tracking;
- mutual reviews;
- peer-review dispute records;
- external profile/rating snapshots from Field Nation, WorkMarket, and similar platforms.

## Guardrails

- The platform does not hold money in v1.
- The platform does not set or recommend market rates.
- Providers control their own rate cards and terms.
- Buyers control their own posted offer terms.
- Payment remains direct between buyer and provider unless a compliant payment partner is added later.
- Reputation is mutual: providers review buyers and buyers review providers.
- Dispute records are reputational/peer-review records, not legal adjudication.

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve --host=0.0.0.0 --port=8123
```

## Demo Accounts

All seeded demo accounts use password `password`.

- `admin@example.com`
- `provider@example.com`
- `buyer@example.com`
- `hybrid@example.com`

## Test Commands

```bash
php artisan test
npm run build
```

## Current Roadmap Status

1. Foundation: Laravel app, auth, roles, seeded demo users.
2. Profiles: provider and buyer profiles.
3. Social layer: community feed posts.
4. Job posts: buyer job-post creation and listing.
5. Quote flow: provider quotes and buyer acceptance.
6. Work orders: assignment, role-aware status transitions, deliverables, messages, evidence files, completion notes.
7. Reviews: mutual buyer/provider reviews with category metrics.
8. Disputes: peer-review dispute records with comments, evidence files, and structured votes.
9. API prep: Sanctum installed with starter API routes and authenticated work-order/dispute feeds.
10. Hardening: tests, seed data, setup docs, database notifications, admin overview, and no-payment/no-rate-setting guardrails.

## Implemented Hardening Pass

- Polymorphic attachments for provider profiles, buyer profiles, feed posts, jobs, work orders, disputes, and imported external profiles.
- Polymorphic comments for feed posts, jobs, and disputes.
- Work-order messages between buyer and provider.
- Quote revision history and quote decline flow.
- Role-aware work-order transition rules.
- Category review metrics for communication, scope accuracy, payment reliability, workmanship, and timeliness.
- Dispute peer votes with provider/buyer/split/insufficient-evidence recommendations.
- Database notifications for quote, work-order, review, and dispute events.
- Admin overview page for users, jobs, work orders, disputes, and attachment counts.

## External Profile Imports

Provider profiles support manual external snapshots. For Field Nation, the first version records:

- platform name;
- external provider ID;
- profile URL if available;
- rating;
- review count;
- completed jobs;
- notes or copied review summaries.

Public search did not expose a Field Nation profile for provider ID `172-630`, so any automated Field Nation import should be treated as a later authenticated/export-assisted workflow rather than a public scrape assumption.
