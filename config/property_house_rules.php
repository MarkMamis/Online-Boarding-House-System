<?php

return [
    'categories' => [
        'occupancy' => [
            'label' => 'Occupancy',
            'icon' => 'clock-history',
            'rules' => [
                'Overnight guests are prohibited without prior written consent of the landlord.',
                'Observe the curfew (10:00 PM - 5:00 AM) at all times.',
                'The tenant shall not sublet or transfer the room to any other person.',
            ],
        ],
        'maintenance_safety' => [
            'label' => 'Maintenance & Safety',
            'icon' => 'house-door',
            'rules' => [
                'Keep the room and common areas clean and orderly at all times.',
                'Report any damage or maintenance issue to the landlord within 24 hours.',
                'Do not alter, paint, or modify any part of the room without written consent.',
            ],
        ],
        'prohibited_activities' => [
            'label' => 'Prohibited Activities',
            'icon' => 'fire',
            'rules' => [
                'Noise disturbance after 10:00 PM (parties, loud music) is strictly prohibited.',
                'Cooking inside the room is not allowed unless a designated area is provided.',
            ],
        ],
    ],
];
