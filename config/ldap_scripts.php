<?php

return [
    'allowed' => [
        'sync-petra-affiliation' => [
            'label' => 'Sync Petra Affiliation',
            'path' => '/opt/petra_iam_big_project/ldap/scripts/sync_petra_affiliation_hierarchy.sh',
            'timeout' => 120,
        ],

        'sync-petra-affiliation-fast' => [
            'label' => 'Sync Petra Affiliation Fast',
            'path' => '/opt/petra_iam_big_project/ldap/scripts/sync_petra_affiliation_hierarchy_fast.sh',
            'timeout' => 120,
        ],

        'reconcile-app-groups' => [
            'label' => 'Reconcile App Groups',
            'path' => '/opt/petra_iam_big_project/ldap/scripts/reconcile_app_groups_every_10s.sh',
            'timeout' => 120,
        ],

        'run-affiliation-daemon-once' => [
            'label' => 'Run Affiliation Daemon Once',
            'path' => '/opt/petra_iam_big_project/ldap/scripts/petra_affiliation_daemon.sh',
            'timeout' => 120,
        ],
    ],
];
