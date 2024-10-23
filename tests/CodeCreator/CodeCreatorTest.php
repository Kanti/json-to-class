<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\CodeCreator\CodeCreator;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class CodeCreatorTest extends TestCase
{
    #[Test]
    #[TestDox('Basic types not supported at this level')]
    public function exception1(): void
    {
        $container = new JsonToClassContainer();
        $codeCreator = $container->get(CodeCreator::class);
        $namedSchema =  $container->get(SchemaToNamedSchemaConverter::class)->convert(Data::class, (new Schema(basicTypes: ['string' => true])), null);
        $this->expectExceptionMessage('Basic types not supported at this level {');
        $codeCreator->createFiles($namedSchema);
    }
}
