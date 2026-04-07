<?php

return [
    'global' => [
        'error' => [
            'not_found' => 'Data not found',
            'unauthorized' => 'You do not have permission to access this data',
            'server_error' => 'An error occurred on the server, please try again later',
            'validation' => 'The entered data is invalid, please check again',
            'created' => 'Failed to create data',
            'updated' => 'Failed to update data',
            'deleted' => 'Failed to delete data',
            'not_allowed' => 'This action is not allowed',
            'exists' => 'Data already exists',
        ],
        'success' => [
            'created' => 'Data successfully created',
            'updated' => 'Data successfully updated',
            'deleted' => 'Data successfully deleted',
        ],
    ],
    'user_unit' => [
        'error' => [
            'role_not_found' => 'Role not found',
            'role_not_added' => 'Role failed to be added',
            'role_not_deleted' => 'Role failed to be deleted',
            'user_not_found' => 'User not found',
        ],
        'success' => [
            'role_added' => 'Role successfully added',
            'role_deleted' => 'Role successfully deleted',
        ],
    ],
];
