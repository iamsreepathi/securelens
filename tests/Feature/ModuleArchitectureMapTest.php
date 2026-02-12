<?php

test('architecture module map contains all required modules', function () {
    $modules = config('architecture.modules');

    expect($modules)->toBeArray();
    expect(array_keys($modules))->toEqual([
        'auth',
        'billing',
        'teams',
        'projects',
        'ingestion',
        'notifications',
        'admin',
    ]);
});

test('every module defines explicit service contracts and dependency boundaries', function () {
    $modules = config('architecture.modules');

    foreach ($modules as $moduleName => $moduleDefinition) {
        expect($moduleDefinition)->toHaveKeys([
            'owned_capabilities',
            'service_contracts',
            'allowed_dependencies',
        ]);

        expect($moduleDefinition['owned_capabilities'])->toBeArray()->not->toBeEmpty();
        expect($moduleDefinition['service_contracts'])->toBeArray()->not->toBeEmpty();
        expect($moduleDefinition['allowed_dependencies'])->toBeArray();

        foreach ($moduleDefinition['service_contracts'] as $serviceContract) {
            expect($serviceContract)
                ->toBeString()
                ->toStartWith(ucfirst($moduleName).'\\');
        }
    }
});

test('owned capabilities are non-overlapping across modules', function () {
    $modules = config('architecture.modules');
    $capabilityOwners = [];

    foreach ($modules as $moduleName => $moduleDefinition) {
        foreach ($moduleDefinition['owned_capabilities'] as $ownedCapability) {
            expect($capabilityOwners)->not->toHaveKey($ownedCapability);
            $capabilityOwners[$ownedCapability] = $moduleName;
        }
    }
});

test('module dependencies only reference known modules and never self-reference', function () {
    $modules = config('architecture.modules');
    $knownModules = array_keys($modules);

    foreach ($modules as $moduleName => $moduleDefinition) {
        foreach ($moduleDefinition['allowed_dependencies'] as $dependencyName) {
            expect($knownModules)->toContain($dependencyName);
            expect($dependencyName)->not->toBe($moduleName);
        }
    }
});
