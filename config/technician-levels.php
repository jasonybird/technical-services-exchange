<?php

return [
    1 => [
        'name' => 'Level 1 - Smart hands',
        'short_name' => 'Smart hands',
        'description' => 'Follows clear remote direction, performs basic onsite tasks, takes photos, swaps labeled equipment, and reports what they see. Scope must be tightly defined and should not require independent diagnosis.',
        'scope_rule' => 'Use for simple, clearly documented tasks with step-by-step instructions and available support.',
    ],
    2 => [
        'name' => 'Level 2 - Installer',
        'short_name' => 'Installer',
        'description' => 'Installs or replaces equipment independently, follows a defined plan, verifies operation, and completes normal closeout. Scope should identify the equipment, expected result, and test path.',
        'scope_rule' => 'Use for planned installs, swaps, turn-ups, and closeouts where the desired end state is known.',
    ],
    3 => [
        'name' => 'Level 3 - Troubleshooter',
        'short_name' => 'Troubleshooter',
        'description' => 'Works semi-independently when the exact fault is unknown, gathers evidence, isolates likely causes, and attempts practical remediation within an agreed boundary.',
        'scope_rule' => 'Use when the problem is not fully known, but the buyer can define systems involved, support path, and escalation limits.',
    ],
    4 => [
        'name' => 'Level 4 - Specialist',
        'short_name' => 'Specialist',
        'description' => 'Handles advanced or vendor-specific systems, complex integrations, certification-heavy tasks, or work where mistakes carry higher operational risk.',
        'scope_rule' => 'Use when specialty knowledge, verified credentials, or deeper system ownership is required.',
    ],
    5 => [
        'name' => 'Level 5 - Project lead',
        'short_name' => 'Project lead',
        'description' => 'Coordinates multi-tech, multi-site, or high-ambiguity projects, manages onsite decisions, documents changes, and handles buyer/provider coordination.',
        'scope_rule' => 'Use for leadership, field coordination, cutovers, escalations, or projects where onsite judgment is central.',
    ],
];
