# Platform Research Notes

These notes capture product patterns observed from established field-service marketplace tools. Keep this file sanitized: do not store customer names, addresses, phone numbers, work-order IDs, screenshots, or proprietary scope text from a logged-in third-party account.

## Field Nation Review - July 10, 2026

### Release Modal

- Field Nation displayed a release modal for a schedule-update workflow change.
- The modal announced a new work-order-detail button for requesting schedule changes.
- It also explained that schedule-change requests were removed from the early/late check-in screen to encourage earlier planning.
- Useful lesson: workflow-change notices should be tied to the exact workflow they affect, include a short reason, and link to fuller discussion or documentation.
- TSE implication: when we change work-order behavior, prefer a small release notice pattern with "what changed", "why it changed", and "where to learn more".

### Assigned Work List

Good patterns:

- Dense table layout makes assigned work scannable.
- Core fields are visible without opening the detail page: title, type of work, buyer/company, location, pay terms, schedule, status, request count, and route count.
- Location links open map search directly.
- Schedule distinguishes hard-start work from more flexible timing.
- Request/message counts make pending activity visible.
- Filtering by work type and coverage area is prominent.

Weak patterns:

- The grid exposes sensitive operational details very quickly.
- Some action/status columns are not self-explanatory without platform experience.
- Survey and release overlays can compete with the operational task at hand.
- The table is dense enough that it can become difficult on smaller screens.

TSE implications:

- Keep a dense work-order list for serious users, but make each column readable and intentional.
- Add explicit schedule badges such as hard start, window, flexible, and time set.
- Add activity counters for messages, requests, route/visit count, evidence, and change requests.
- Use map links, but avoid exposing exact site data publicly.
- Make buyer/company and work type filterable first-class fields.

### Work-Order Detail Pages

Good patterns:

- Detail pages are organized around predictable sections: overview, work experience/type, qualifications, service description, tasks, custom fields, time log, deliverables, shipments, signatures, pay, schedule, location, contacts, and selection rule.
- Required qualifications are matched against provider profile records and shown as passed/missing.
- Task checklists are split by phase, such as prep, onsite, and post-work.
- Deliverables are separated into named upload buckets.
- Important actions are grouped near the top: print, request schedule update, report problem, running late, remove assignment, and submit for review.
- Print support exists directly from the work-order page.

Weak patterns:

- Large buyer instructions can become one long wall of text.
- Repeated warnings and all-caps policy text create visual fatigue.
- Deliverable categories can become too numerous without hierarchy.
- Buyer custom fields and provider custom fields are useful, but can feel bolted on if they are not explained.
- Some operational actions are high-impact and need careful confirmation language.
- Catch-all work orders can blur the actual scope by burying broad obligations deep inside long instruction blocks.
- Overbroad scope language can shift undefined onsite risk to the provider after assignment.
- Buyer/site contacts may be listed but unavailable during the work window, leaving the provider unable to confirm access, scope, escalation, or closeout.

TSE implications:

- Continue separating checklist items, evidence rules, appointment windows, terms, change requests, and print packets.
- Add reusable work-order templates with structured sections instead of asking buyers to paste huge instruction blocks.
- Add "required qualifications" matching between a job/work order and provider profile tags, equipment, certifications, screenings, and service categories.
- Add deliverable buckets with instructions, required/optional status, accepted file types, and examples.
- Add action states for running late, request schedule update, report problem, submit for review, and remove assignment, each with audit history.
- Keep print/PDF work packets as a first-class feature.
- Add scope guardrails: require a plain-language primary objective, explicit included work, explicit excluded work, required tools/equipment, maximum onsite expectations, and a change-request path for anything outside scope.
- Add a buyer certification step before posting/assigning work: the buyer must confirm that listed contacts and support channels will be available during the scheduled work window.
- Add contact verification fields: primary contact, backup contact, support channel, expected response time, escalation path, and "contact failed" evidence logging.
- Treat unsupported scope expansion and unreachable contacts as structured dispute reasons.

### Provider Profile

Good patterns:

- Strong profile header with name, location, provider ID, activity status, job count, client count, and share action.
- Profile tabs separate profile content, availability, and recommendations.
- About section supports narrative positioning.
- Provider-success area combines multiple signals: buyer satisfaction, timeliness, backouts, and category-specific feedback.
- Work summary converts completed jobs into service-category proof.
- Skillsets and equipment inventories are searchable and countable.
- Licenses/certifications, employment history, education, screenings, talent pools, and languages are distinct sections.
- Rating history is tied to prior work, categories, dates, and optional buyer comments.
- Availability has day/week/month calendar views and calendar sync.

Weak patterns:

- The provider-success score is opaque and can feel punitive.
- Privately collected buyer feedback affects visible profile outcomes without enough provider-side context.
- Skill and equipment taxonomies can become messy, duplicated, and inconsistently named.
- Empty sections, such as recommendations, can feel dead unless they guide the user toward next steps.
- Some profile data is too sensitive for public display by default.

TSE implications:

- Profile pages should become evidence portfolios, not just bios.
- Keep provider-controlled narrative content, galleries, certifications, tools, services, and imported marketplace history.
- Add a transparent rating model: visible category definitions, appeal/report flows, edit windows, and moderation tools.
- Treat imported ratings as "imported history" unless independently verified inside TSE.
- Build a normalized taxonomy for skillsets, equipment, certifications, service categories, and competency levels.
- Add availability blocks and optional calendar export before building full calendar sync.
- Add privacy controls for contact data, exact location, imported history, and sensitive documents.

### Success Score And Ratings

Observed pattern:

- Field Nation now emphasizes a provider success score alongside older all-time marketplace ratings.
- The visible score combines recent buyer feedback, timeliness, backouts, and comparative percentile language.
- Buyer satisfaction is based on a rolling assignment window and privately collected buyer feedback.
- The older review history still exists and is tied to completed work, dates, service categories, star ratings, and optional comments.

Good patterns:

- Separate timeliness and backout metrics are useful because they measure specific operational behaviors.
- Category feedback such as responsiveness, work quality, preparedness, communication, professionalism, and problem solving can be more informative than a single star number.
- Work-order-specific reviews are valuable evidence when they remain accessible.

Weak patterns:

- A single opaque success score can overshadow a long history of strong public reviews.
- Private buyer feedback can affect visible reputation without enough provider-side transparency.
- Percentile language can make experienced providers look weak even when their direct rating history is strong.
- Rolling-window metrics can erase long-term context.
- A score that affects marketplace access, ranking, or buyer confidence needs clear formulas, appeal paths, and evidence.

TSE implications:

- Keep simple five-star reviews as a primary human-readable signal.
- Add transparent sub-metrics, but do not collapse them into an opaque platform-controlled score.
- Separate native TSE reputation from imported marketplace history.
- Show long-term history and recent operational metrics side by side.
- Let providers respond to reviews and challenge disputed feedback.
- Avoid hidden private feedback that changes public reputation without reviewable context.
- If a composite badge is added later, make it explainable, auditable, and secondary to the underlying evidence.

### Available Work List

Observed pattern:

- Available work uses the same dense grid structure as assigned work.
- Filters include provider work types and coverage area radius.
- The list shows available count, title, type of work, company, location, pay type, pay amount, schedule, status, request count, and route count.
- Schedule values include hard starts, date ranges, and appointment windows.
- Pay values include hourly, fixed, and blended structures.
- Request counts reveal visible competition for a job.
- Route counts appear to signal routed/direct-distribution behavior.
- The top filter bar stays small: work type, coverage/radius, keyword search, reset, and grid/bulk controls.
- Work-type filtering uses a nested taxonomy rather than one flat tag list.
- Coverage filtering separates physical location/radius from remote work.

Good patterns:

- A dense list is efficient for experienced users scanning many opportunities.
- Work type, location, schedule, pay, and request count are the right first-pass fields.
- Date ranges and appointment windows are useful when they are clear.
- Search plus work-type and coverage filters are essential.
- Keeping only a few top-level filters visible prevents the list from turning into a control panel.
- Nested filter categories let experienced users narrow work without forcing every specialty into the main row.

Weak patterns:

- Low pay, long windows, and request counts appear next to each other without much provider-protection context.
- Past-due or awkward timing can still appear in the list.
- Blended pay can be hard to parse quickly.
- Request counts can encourage race-to-the-bottom behavior if buyers treat provider volume as leverage.
- The grid does not clearly flag risk, ambiguity, catch-all scope, contact uncertainty, or missing support requirements.
- Some controls are icon-only or unlabeled in the DOM, which hurts accessibility and makes the UI harder to learn.

TSE implications:

- Available jobs should show pay type, provider terms, schedule type, work type, rough location, buyer reliability, support/contact certification, scope clarity status, and request/quote count.
- Add risk badges for broad scope, missing contact backup, missing deliverables, missing required tools, compressed schedule, and unclear closeout requirements.
- Keep provider-controlled filters for distance, pay floor, work category, schedule type, buyer rating, scope clarity, and support availability.
- Do not use request volume to pressure providers into lower rates.
- Preserve a "hide buyer/name until eligible" option if needed, but make the tradeoff explicit.
- Build a compact filter bar first, then add advanced filters behind an expandable drawer or saved search.
- Model work categories as nested taxonomy records, with broad families and specific specialties.
- Treat remote work as a separate filter mode from radius-based onsite work.
- Ensure every filter and grid action has a visible label or accessible label.

### Observed Work-Type Taxonomy

The available-work filter exposed these broad families and specialties:

- Access & Alarms: access control, burglar alarm, fire alarm, general alarm.
- A/V & Digital Signage: audio visual, digital signage, satellite TV.
- Cameras: CCTV camera, IP camera.
- EV Equipment: EV charging stations.
- Fiber Cabling: fiber certification, fiber patch cabling, fiber runs, fiber structured cabling, fiber testing.
- Kiosk / ATM: ATM, kiosk, lockers.
- Low Voltage Cabling: low voltage certification, low voltage patch cabling, low voltage runs, low voltage structured cabling, low voltage testing.
- Office Equipment: copier, Mac device, printer, Windows device.
- Other: electrical, furniture, general tasks, other trades.
- Point of Sale: point of sale, self-checkout.
- Retail Services: advertising, fixtures, merchandising.
- Server & Networking: networking, satellite networking, server/storage, wireless networking.
- Telecom: POTS, VoIP-SIP.

TSE should use this as a reference point, not as a final taxonomy. Our taxonomy should be provider-centered, cleanly named, and expandable to licensed trades later.

### Roadmap Inputs

- Phase 5 should refine ratings around transparency, category definitions, appeal/report flows, imported-vs-native reputation, and moderation.
- Phase 6 should treat disputes as evidence timelines with reason-coded votes and clear audit trails, including scope expansion and unreachable-contact reasons.
- Phase 8 should plan mobile-safe work-order actions for check-in, running late, schedule updates, evidence uploads, support/contact failure logging, and submit-for-review.
- Phase 9 should import profile history into sanitized, provider-controlled sections rather than blindly copying third-party pages.
- Phase 10 should normalize competency tags and levels so buyers can distinguish smart-hands work from advanced troubleshooting, networking, cabling, POS, POTS, AV, and other specialized work.
