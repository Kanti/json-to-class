<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\CodeCreator;

use Kanti\JsonToClass\Tests\v2\_helper\PhpFilesDriver;
use Kanti\JsonToClass\Tests\v2\_helper\PhpFilesDto;
use Kanti\JsonToClass\v2\CodeCreator\CodeCreator;
use Kanti\JsonToClass\v2\Container\JsonToClassContainer;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Kanti\JsonToClass\v2\Schema\Schema;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class CodeCreatorTest extends TestCase
{
    use MatchesSnapshots;

    #[Test]
    #[TestDox('CodeCreator->createFiles')]
    #[DataProviderExternal(TypeCreatorTest::class, 'dataProvider')]
    public function createFiles(Schema $schema, ...$_): void
    {

        $container = new JsonToClassContainer();
        $codeCreator = $container->get(CodeCreator::class);
        $wrappedSchema = new Schema(properties: ['a' => $schema]);
        $actualFiles = $codeCreator->createFiles(NamedSchema::fromSchema(\Kanti\GeneratedTest\Data::class, $wrappedSchema));


        $actual = new PhpFilesDto($actualFiles, $this->dataName(), $this->providedData());
        $this->assertMatchesSnapshot($actual, new PhpFilesDriver());
    }
}
