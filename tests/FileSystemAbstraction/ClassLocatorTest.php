<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\FileSystemAbstraction;

use Composer\Autoload\ClassLoader;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class ClassLocatorTest extends TestCase
{
    #[Test]
    #[TestDox('Expected ClassType, got Nette\PhpGenerator\TraitType for class Kanti\TraitA')]
    public function exception1(): void
    {
        $classLocator = $this->getClassLocator([
            'fake-src/TraitA.php' => <<<'EOF'
<?php

namespace Kanti;

trait TraitA {

}
EOF
,
        ]);
        $this->expectExceptionMessage('Expected ClassType, got Nette\PhpGenerator\TraitType for class Kanti\TraitA');
        $classLocator->getClass(F::classString('Kanti\TraitA'));
    }

    /**
     * @param array<string, string> $alreadyWrittenFiles
     */
    protected function getClassLocator(array $alreadyWrittenFiles): ClassLocator
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem($alreadyWrittenFiles),
        ]);
        return $container->get(ClassLocator::class);
    }
}
