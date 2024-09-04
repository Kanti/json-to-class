<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Code;

use ArrayIterator;
use IteratorAggregate;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Nette\PhpGenerator\PhpFile;
use Traversable;

final class Classes implements IteratorAggregate
{

    /**
     * @var array<string, array{class: FullyQualifiedClassName, phpFile: PhpFile}>
     */
    private array $classes = [];

    public function addClass(FullyQualifiedClassName $class, PhpFile $file): void
    {
        $this->classes[(string)$class] = [
            'class' => $class,
            'phpFile' => $file,
        ];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->classes);
    }

    public function add(Classes $childClasses): void
    {
        foreach ($childClasses as $key => $class) {
            if (isset($this->classes[$key])) {
                throw new \Exception('Class already exists');
            }
            $this->classes[$key] = $class;
        }
    }
}
