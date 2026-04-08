<?php

return [
    'enabled' => env('SSO_ENABLED', false),
    'idp_name' => env('SAML_IDP_NAME'),
    'group_attribute' => env('SAML_GROUP_ATTRIBUTE', 'groups'),
    'allowed_group' => env('SAML_ALLOWED_GROUP', '/app-web/admin-role-web'),
];
