<?php

return [
    'roles' => [
        'admin' => [
            'owner',
            'team_admin',
        ],
    ],
    'abilities' => [
        'team' => [
            'view' => [
                'owner',
                'team_admin',
                'member',
            ],
            'update' => [
                'owner',
                'team_admin',
            ],
            'delete' => [
                'owner',
            ],
            'manage_members' => [
                'owner',
                'team_admin',
            ],
        ],
        'project' => [
            'view' => [
                'owner',
                'team_admin',
                'member',
            ],
            'create' => [
                'owner',
                'team_admin',
            ],
            'update' => [
                'owner',
                'team_admin',
            ],
            'delete' => [
                'owner',
            ],
        ],
        'admin' => [
            'access' => [
                'owner',
                'team_admin',
            ],
        ],
    ],
];
