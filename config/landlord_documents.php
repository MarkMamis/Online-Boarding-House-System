<?php

/**
 * Centralized configuration for landlord documents & requirements.
 *
 * Adding a new document type is a config-only change:
 *   1. Add an entry under 'types' with label + storage directory.
 *   2. No new database columns are required.
 */

return [
    // Supported document types. Key = stored value, label = human display.
    // Directory is the sub-folder inside landlords/{landlord_id}/documents/.
    'types' => [
        'business_permit' => [
            'label' => 'Business Permit',
            'directory' => 'business-permits',
        ],
        'safety_certificate' => [
            'label' => 'Safety Certificate',
            'directory' => 'safety-certificates',
        ],
    ],

    // Administrative review statuses (stored in verification_status).
    'verification_statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    // Document upload validation.
    'allowed_mimes' => 'pdf,jpg,jpeg,png',
    'max_size_kb' => 2048,

    // Expiration monitoring thresholds (days until expiration).
    //   > valid_days                  => valid
    //   warning_60 .. valid_days      => expiring soon (warning_60)
    //   warning_30 .. warning_60 - 1  => expiring soon (warning_30)
    //   critical_7 .. warning_30 - 1  => expiring soon (critical_7)
    //   0 .. critical_7 - 1           => expiring soon (critical_7)
    //   <= 0                          => expired
    'expiration' => [
        'valid_days' => 60,
        'warning_60' => 60,
        'warning_30' => 30,
        'critical_7' => 7,
    ],

    // Storage prefix for uploaded documents.
    'storage_prefix' => 'landlords/{landlord_id}/documents',
];
