<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\CodeCreator\PhpFileUpdater;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Helpers\SH;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PhpFileUpdaterTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    /**
     * @param class-string $rootClassName
     * @param class-string $className
     */
    public function updateFile(
        Schema $schema,
        string $phpFile,
        string $expected,
        string $rootClassName = Data::class,
        string $className = Data::class,
    ): void {
        $container = new JsonToClassContainer();
        $updater = $container->get(PhpFileUpdater::class);

        $file = new PhpFile();
        if ($phpFile) {
            $file = PhpFile::fromCode($phpFile);
        }

        $namedSchema = NamedSchema::fromSchema(SH::classString($className), $schema);
        $actual = $updater->updateFile($rootClassName, $namedSchema, $file);
        $this->assertEquals($expected, $actual);
    }

    public static function dataProvider(): Generator
    {
        $schema = new Schema(
            properties: [
                'expand' => new Schema(listElement: new Schema(basicTypes: ['string' => true])),
                'fields' => new Schema(properties: []),
                'id' => new Schema(basicTypes: ['string' => true]),
                'key' => new Schema(canBeMissing: true, basicTypes: ['string' => true]),
                'self' => new Schema(basicTypes: ['string' => true]),
            ],
        );
        $result = <<<'EOF'
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\Fields;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

#[RootClass]
final readonly class Data
{
    /**
     * @param list<string> $expand
     */
    public function __construct(
        #[Types(['string'])]
        public array $expand,
        public Fields $fields,
        public string $id,
        public string $self,
        public ?string $key = null,
    ) {
    }
}

EOF;
        yield 'empty' => [
            'schema' => $schema,
            'phpFile' => '',
            'expected' => $result,
        ];
        $result = <<<'EOF'
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data_\Fields;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

#[RootClass(Data::class)]
final readonly class Data_
{
    /**
     * @param list<string> $expand
     */
    public function __construct(
        #[Types(['string'])]
        public array $expand,
        public Fields $fields,
        public string $id,
        public string $self,
        public ?string $key = null,
    ) {
    }
}

EOF;
        yield 'empty className !== $rootName' => [
            'schema' => $schema,
            'phpFile' => '',
            'expected' => $result,
            'className' => Data::class . '_',
        ];
        yield 'RootClass was present before' => [
            'schema' => $schema,
            'phpFile' => <<<'EOF'
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class Data_ {

}

EOF
,
            'expected' => $result,
            'className' => Data::class . '_',
        ];
        yield 'nearly empty file' => [
            'schema' => $schema,
            'className' => Data::class . '\SubClass',
            'phpFile' => <<<'EOF'
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data\SubClass\Fields;

#[\CustomAttribute]
class SubClass
{
    /**
     * @param Fields $fields
     * @param string $expand
     * @param string $id this is not a UUid
     * @param string $self
     * @param string $thisShouldNotBeThereAnymore
     * @param string $thisWillNotBeTouched
     */
    public function __construct(
        public Fields $fields,
        public string $id,
        public string $expand,
        public string $self,
        public string $thisShouldNotBeThereAnymore,
    ) {
    }

    public function getSpecialField(): int {
        return $this->fields->specialField;
    }
}

EOF
,
            'expected' => <<<'EOF'
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\GeneratedTest\Data\SubClass\Fields;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

#[\CustomAttribute]
#[RootClass(Data::class)]
class SubClass
{
    /**
     * @param list<string> $expand
     * @param string $id this is not a UUid
     * @param string $thisWillNotBeTouched
     */
    public function __construct(
        public Fields $fields,
        public string $id,
        #[Types(['string'])]
        public array $expand,
        public string $self,
        public ?string $key = null,
    ) {
    }

    public function getSpecialField(): int
    {
        return $this->fields->specialField;
    }
}

EOF
,
        ];
    }
}
