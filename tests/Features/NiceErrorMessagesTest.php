<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Features;

use Composer\Autoload\ClassLoader;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Config\Enums\ShouldCreateClasses;
use Kanti\JsonToClass\Config\Enums\ShouldCreateDevelopmentClasses;
use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Converter\Converter;
use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Mapper\Exception\MissingDataException;
use Kanti\JsonToClass\Mapper\Exception\NoPossibleTypesException;
use Kanti\JsonToClass\Mapper\Exception\TypesDoNotMatchException;
use Kanti\JsonToClass\Mapper\PossibleConvertTargets;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

use function json_encode;
use function print_r;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class NiceErrorMessagesTest extends TestCase
{
    use MatchesSnapshots;

    #[Test]
    #[RunInSeparateProcess]
    public function wrongTypeGiven(): void
    {
        $converter = $this->getConverter();
        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::YES,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );

        // create Production classes:
        $dto = $converter->jsonDecode(Data::class, '{"a": 1}', $config);
        $this->assertEquals('{"a":1}', json_encode($dto, JSON_THROW_ON_ERROR));

        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::NO,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );
        // use Production classes:
        $dto = $converter->jsonDecode(Data::class, '{"a": {"b": "string"}}', $config);
        $line = __LINE__ - 1;
        /*
         * information that should be visible in the exception message:
         * - there was the data originally converted (file, line, column, function, class, etc.)
         * - the possible types that were allowed
         * - the type that was given
         * - the data that was given
         * - the position in the original data (path)
         */
        try {
            $x = $dto->a;
            $this->fail('Exception should be thrown but returned: ' . print_r($x, true));
        } catch (TypesDoNotMatchException $typesDoNotMatchException) {
            $this->assertEquals(new PossibleConvertTargets([new Type('int')]), $typesDoNotMatchException->possibleTypes);
            $this->assertEquals(new Type('object'), $typesDoNotMatchException->sourceType);
            $this->assertEquals('$.a', $typesDoNotMatchException->path);
            $this->assertEquals((object)['b' => 'string'], $typesDoNotMatchException->data);
            $this->assertStringContainsString(__FILE__ . '(' . $line . ')', $typesDoNotMatchException->getTraceAsString());
            $this->assertEquals('Types do not match. Possible types: int. Source type: object at $.a', $typesDoNotMatchException->getMessage());
        }
    }

    private function getConverter(): Converter
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem([]),
        ]);

        /** @var Converter $converter */
        $converter = $container->get(Converter::class);
        return $converter;
    }

    #[Test]
    #[RunInSeparateProcess]
    public function wrongTypeGiven2(): void
    {
        $converter = $this->getConverter();
        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::YES,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );

        // create Production classes:
        $dto = $converter->jsonDecode(Data::class, '{"a": "this is a nice string 🚂"}', $config);
        $this->assertEquals('{"a":"this is a nice string 🚂"}', json_encode($dto, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::NO,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );
        // use Production classes:
        $dto = $converter->jsonDecode(Data::class, '{"a": [[[{"b": "string"}]]]}', $config);
        $line = __LINE__ - 1;
        /*
         * information that should be visible in the exception message:
         * - there was the data originally converted (file, line, column, function, class, etc.)
         * - the possible types that were allowed
         * - the type that was given
         * - the data that was given
         * - the position in the original data (path)
         */
        try {
            $x = $dto->a;
            $this->fail('Exception should be thrown but returned: ' . print_r($x, true));
        } catch (TypesDoNotMatchException $typesDoNotMatchException) {
            $this->assertEquals(new PossibleConvertTargets([new Type('string')]), $typesDoNotMatchException->possibleTypes);
            $this->assertEquals(new Type('list', 1), $typesDoNotMatchException->sourceType);
            $this->assertEquals('$.a', $typesDoNotMatchException->path);
            $this->assertEquals([[[(object)['b' => 'string']]]], $typesDoNotMatchException->data);
            $this->assertStringContainsString(__FILE__ . '(' . $line . ')', $typesDoNotMatchException->getTraceAsString());
            $this->assertEquals('Types do not match. Possible types: string. Source type: list[] at $.a', $typesDoNotMatchException->getMessage());
        }
    }

    #[Test]
    #[RunInSeparateProcess]
    public function wrongTypeGiven3(): void
    {
        $converter = $this->getConverter();
        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::YES,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );

        // create Production classes:
        $dto = $converter->jsonDecode(Data::class, '{"a": []}', $config);
        $this->assertEquals('{"a":[]}', json_encode($dto, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::NO,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );
        // use Production classes:
        $dto = $converter->jsonDecode(Data::class, '{"a": [1]}', $config);
        $line = __LINE__ - 1;
        /*
         * information that should be visible in the exception message:
         * - there was the data originally converted (file, line, column, function, class, etc.)
         * - the possible types that were allowed
         * - the type that was given
         * - the data that was given
         * - the position in the original data (path)
         */
        try {
            $x = $dto->a;
            $this->fail('Exception should be thrown but returned: ' . print_r($x, true));
        } catch (NoPossibleTypesException $noPossibleTypesException) {
            $this->assertEquals(new Type('int', 0), $noPossibleTypesException->sourceType);
            $this->assertEquals('$.a.0', $noPossibleTypesException->path);
            $this->assertEquals(1, $noPossibleTypesException->data);
            $this->assertStringContainsString(__FILE__ . '(' . $line . ')', $noPossibleTypesException->getTraceAsString());
            $this->assertEquals('No possible types. Source type: int at $.a.0', $noPossibleTypesException->getMessage());
        }
    }

    #[Test]
    #[RunInSeparateProcess]
    public function missingData(): void
    {
        $converter = $this->getConverter();
        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::YES,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );

        // create Production classes:
        $dto = $converter->jsonDecodeList(Data::class, '[{"a": {"b": 1}}, {"a": {"b": 2.1}}]', $config);
        $this->assertEquals('{"a":{"b":1}}', json_encode($dto[0], JSON_THROW_ON_ERROR));
        $this->assertEquals('{"a":{"b":2.1}}', json_encode($dto[1], JSON_THROW_ON_ERROR));

        $config = new SaneConfig(
            shouldCreateClasses: ShouldCreateClasses::NO,
            shouldCreateDevelopmentClasses: ShouldCreateDevelopmentClasses::NO,
        );
        // use Production classes:
        $dto = $converter->jsonDecode(Data::class, '{"a":{}}', $config);
        $line = __LINE__ - 1;
        /*
         * information that should be visible in the exception message:
         * - there was the data originally converted (file, line, column, function, class, etc.)
         * - the possible types that were allowed
         * - the type that was given
         * - the data that was given
         * - the position in the original data (path)
         */
        $a = $dto->a;
        try {
            $x = $a->b;
            $this->fail('Exception should be thrown but returned: ' . print_r($x, true));
        } catch (MissingDataException $missingDataException) {
            $this->assertEquals(new PossibleConvertTargets([new Type('int'), new Type('float')]), $missingDataException->possibleTypes);
            $this->assertEquals('$.a.b', $missingDataException->path);
            $this->assertStringContainsString(__FILE__ . '(' . $line . ')', $missingDataException->getTraceAsString());
            $this->assertEquals('Missing data. Possible types: int|float at $.a.b', $missingDataException->getMessage());
        }
    }
}
