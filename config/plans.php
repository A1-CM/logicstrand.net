<?php

return [
    'sandbox' => [
        'name' => 'Sandbox',
        'price' => 0,
        'document_limit' => 20,
        'daily_question_limit' => 30,
        'period' => '7 days',
        'eyebrow' => 'A place to begin',
        'description' => 'Follow an idea from source to answer, with room to explore.',
        'features' => ['7 days of workspace access', 'Up to 20 private documents', '30 questions per day', 'Inspectable source passages'],
        'action' => 'Start 7 day trial',
    ],
    'individual' => [
        'name' => 'Individual',
        'price' => 29,
        'document_limit' => 100,
        'daily_question_limit' => 100,
        'period' => 'month',
        'eyebrow' => 'For focused work',
        'description' => 'Keep the evidence close as your knowledge library grows.',
        'features' => ['One month of workspace access', 'Up to 100 private documents', '100 questions per day', 'Answer history and citations'],
        'action' => 'Choose Individual',
    ],
    'studio' => [
        'name' => 'Studio',
        'price' => 79,
        'document_limit' => 500,
        'daily_question_limit' => 300,
        'period' => 'month',
        'eyebrow' => 'For deeper exploration',
        'description' => 'A generous starting point for a growing body of work.',
        'features' => ['One month of workspace access', 'Up to 500 private documents', '300 questions per day', 'Answer history and citations'],
        'action' => 'Choose Studio',
    ],
];
