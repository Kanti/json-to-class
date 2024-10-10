<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\CodeCreator\TypeCreator;
use Kanti\JsonToClass\Helpers\SH;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Tests\Writer\FileWriterTest;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

use function array_column;
use function array_keys;
use function class_exists;
use function Safe\class_alias;

class DevelopmentCodeCreatorTest extends TestCase
{
    #[Test]
    #[RunInSeparateProcess]
    #[TestDox('Class Data already exists and is not a DataTrait')]
    public function exception1(): void
    {
        $developmentCodeCreator = new DevelopmentCodeCreator(new TypeCreator());
        class_alias(self::class, Data::class);
        $namedSchema = NamedSchema::fromSchema(Data::class, new Schema());

        $this->expectExceptionMessage('Class ' . Data::class . ' already exists and is not a DataTrait');
        $developmentCodeCreator->createDevelopmentClasses($namedSchema);
    }

    #[Test]
    public function createDevelopmentClasses(): void
    {
        $triedLoadingClasses = FileWriterTest::triedLoadingClasses();

        $this->assertFalse(DevelopmentCodeCreator::isDevelopmentDto(SH::classString('FakeClass')), 'Class should not be a DataTrait');
        $this->assertArrayNotHasKey('FakeClass', $triedLoadingClasses, 'Class should not have been autoloaded');

        $schema = new Schema(
            listElement: new Schema(
                properties: [
                    'int' => new Schema(basicTypes: ['int' => true]),
                ],
            ),
            properties: [
                'expand' => new Schema(listElement: new Schema(basicTypes: ['string' => true])),
                'fields' => new Schema(properties: [
                    'int' => new Schema(basicTypes: ['int' => true]),
                ]),
                'id' => new Schema(basicTypes: ['string' => true]),
                'key' => new Schema(basicTypes: ['string' => true]),
                'self' => new Schema(basicTypes: ['string' => true]),
            ],
        );
        $namedSchema = NamedSchema::fromSchema(Data::class, $schema);
        $developmentCodeCreator = new DevelopmentCodeCreator(new TypeCreator());
        $this->assertFalse(class_exists($namedSchema->className, false), sprintf('Class %s should not exist', $namedSchema->className));

        $developmentCodeCreator->createDevelopmentClasses($namedSchema);

        $this->assertTrue(class_exists($namedSchema->className, false), sprintf('Class %s should now exist', $namedSchema->className));
        $this->assertTrue(DevelopmentCodeCreator::isDevelopmentDto($namedSchema->className), 'Class should be a DataTrait');
        $this->assertTrue(DevelopmentCodeCreator::isDevelopmentDto($namedSchema->listElement->className), 'Class should be a DataTrait');
        $this->assertTrue(DevelopmentCodeCreator::isDevelopmentDto($namedSchema->properties['fields']->className), 'Class should be a DataTrait');
        $this->assertEquals(array_keys($schema->properties), array_column($namedSchema->className::getClassParameters(), 'name'), 'Class parameters should match');

        // can be called again:
        $developmentCodeCreator->createDevelopmentClasses($namedSchema);
        $this->assertArrayNotHasKey($namedSchema->className, $triedLoadingClasses, 'Class should not have been autoloaded');
    }
}
