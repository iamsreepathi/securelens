<?php

return [
    'modules' => [
        'auth' => [
            'owned_capabilities' => [
                'user registration',
                'login and session management',
                'password reset lifecycle',
                'email verification lifecycle',
                'multi-factor authentication',
            ],
            'service_contracts' => [
                'Auth\UserRegistrationService',
                'Auth\SessionAuthenticationService',
                'Auth\PasswordResetService',
                'Auth\EmailVerificationService',
                'Auth\TwoFactorChallengeService',
            ],
            'allowed_dependencies' => [],
        ],
        'billing' => [
            'owned_capabilities' => [
                'plan catalog and subscriptions',
                'checkout and payment orchestration',
                'invoice and billing history',
                'entitlement state synchronization',
            ],
            'service_contracts' => [
                'Billing\SubscriptionLifecycleService',
                'Billing\CheckoutOrchestrationService',
                'Billing\InvoiceLedgerService',
                'Billing\EntitlementSyncService',
            ],
            'allowed_dependencies' => [
                'auth',
                'teams',
            ],
        ],
        'teams' => [
            'owned_capabilities' => [
                'team lifecycle management',
                'membership and role assignment',
                'team-level authorization policy mapping',
            ],
            'service_contracts' => [
                'Teams\TeamManagementService',
                'Teams\MembershipService',
                'Teams\RoleAssignmentService',
            ],
            'allowed_dependencies' => [
                'auth',
            ],
        ],
        'projects' => [
            'owned_capabilities' => [
                'project lifecycle management',
                'project ownership and assignment',
                'project metadata governance',
            ],
            'service_contracts' => [
                'Projects\ProjectManagementService',
                'Projects\ProjectAssignmentService',
                'Projects\ProjectMetadataService',
            ],
            'allowed_dependencies' => [
                'auth',
                'teams',
            ],
        ],
        'ingestion' => [
            'owned_capabilities' => [
                'webhook authentication and validation',
                'payload normalization',
                'idempotent vulnerability ingestion',
                'ingestion failure handling and replay',
            ],
            'service_contracts' => [
                'Ingestion\WebhookAuthenticationService',
                'Ingestion\PayloadNormalizationService',
                'Ingestion\IdempotentIngestionService',
                'Ingestion\FailureHandlingService',
            ],
            'allowed_dependencies' => [
                'projects',
                'notifications',
            ],
        ],
        'notifications' => [
            'owned_capabilities' => [
                'event-to-alert translation',
                'notification routing and fan-out',
                'delivery preference management',
                'notification audit trail',
            ],
            'service_contracts' => [
                'Notifications\AlertDispatchService',
                'Notifications\RoutingService',
                'Notifications\PreferenceService',
                'Notifications\DeliveryAuditService',
            ],
            'allowed_dependencies' => [
                'auth',
                'teams',
            ],
        ],
        'admin' => [
            'owned_capabilities' => [
                'operational dashboards',
                'tenant and platform controls',
                'admin audit and compliance views',
            ],
            'service_contracts' => [
                'Admin\OperationalDashboardService',
                'Admin\PlatformControlService',
                'Admin\AuditVisibilityService',
            ],
            'allowed_dependencies' => [
                'auth',
                'billing',
                'teams',
                'projects',
                'ingestion',
                'notifications',
            ],
        ],
    ],
];
