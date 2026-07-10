<?php

return [
    'review_edit_window_hours' => env('TSE_REVIEW_EDIT_WINDOW_HOURS', 48),

    'definitions' => [
        'buyer_to_provider' => [
            'overall' => 'Overall experience with the provider on this work order.',
            'communication' => 'Clear, timely, professional updates before, during, and after the job.',
            'preparedness' => 'Arrived ready with the tools, access details, and context reasonably required by the posted scope.',
            'workmanship' => 'Quality and correctness of the technical work performed.',
            'timeliness' => 'Arrival, progress, and closeout timing compared with the agreed schedule.',
            'closeout_quality' => 'Photos, notes, test results, and deliverables were complete and useful.',
            'professionalism' => 'Conduct with the buyer, site contact, end client, and platform participants.',
        ],
        'provider_to_buyer' => [
            'overall' => 'Overall experience with the buyer on this work order.',
            'communication' => 'Clear, timely, professional communication from buyer or dispatch.',
            'scope_accuracy' => 'The actual onsite request matched the posted and agreed scope.',
            'payment_reliability' => 'Payment terms, approvals, and closeout expectations were clear and fair.',
            'contact_availability' => 'Listed contacts and escalation paths were reachable during the work window.',
            'schedule_reasonableness' => 'Schedule, appointment window, duration, and urgency were reasonable for the work.',
            'support_responsiveness' => 'Buyer-side technical or dispatch support responded when needed.',
            'closeout_fairness' => 'Approval, evidence review, and closeout requirements were handled fairly.',
        ],
    ],
];
