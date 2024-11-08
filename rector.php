<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use PLUS\GrumPHPConfig\RectorSettings;
use Rector\Config\RectorConfig;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory('./var/cache/rector');

    $rectorConfig->paths(
        array_filter(explode("\n", (string)shell_exec("git ls-files | xargs ls -d 2>/dev/null | grep -E '\.(php)$'")))
    );

    // define sets of rules
    $rectorConfig->sets(
        [
            ...RectorSettings::sets(true),
            ...RectorSettings::setsTypo3(false),
        ]
    );

    // remove some rules
    // ignore some files
    $rectorConfig->skip(
        [
            ...RectorSettings::skip(),
            ...RectorSettings::skipTypo3(),

            UseIdenticalOverEqualWithSameTypeRector::class => [
                // this is a breaking change: == with objects is not the same as ===
                __DIR__ . '/src/ClassCreator/ClassCreator.php',
            ],

            RestoreDefaultNullToNullableTypePropertyRector::class => [
                // do not add = null to properties
                __DIR__ . '/tests/Converter/__fixture__/DtoAllowsDynamicProperties.php',
            ],
            RemoveUnusedConstructorParamRector::class => [
                // do not add = null to properties
                __DIR__ . '/tests/Container/JsonToClassContainerTest.php',
            ],
            RemoveEmptyClassMethodRector::class => [
                // do not add = null to properties
                __DIR__ . '/tests/Container/JsonToClassContainerTest.php',
            ],
            /**
             * rector should not touch these files
             */
            //__DIR__ . '/src/Example',
            //__DIR__ . '/src/Example.php',
        ]
    );
};
