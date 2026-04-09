<?php

return [
    'enabled' => filter_var(env('PETRA_AUTH_ENABLED', true), FILTER_VALIDATE_BOOL),
    'allowed_group' => env('PETRA_ALLOWED_GROUP', 'app-web/admin-role-web'),
    'group_claim' => env('PETRA_GROUP_CLAIM', 'groups'),
    'forbidden_redirect' => env('PETRA_FORBIDDEN_REDIRECT', '/forbidden'),
    'network_required_redirect' => env('PETRA_NETWORK_REQUIRED_REDIRECT', '/petra-network-required'),
];
