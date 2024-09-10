<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Code;

use Exception;
use ArrayIterator;
use IteratorAggregate;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Traversable;

/**
 * @implements IteratorAggregate<string, string>
 */
final class Classes implements IteratorAggregate
{
    /**
     * @var array<string, string>
     */
    private array $classes = [];

    public function addClass(FullyQualifiedClassName|string $class, string $fileContent): void
    {
        $this->classes[(string)$class] = $fileContent;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->classes);
    }

    public function add(Classes $childClasses): void
    {
        foreach ($childClasses as $key => $class) {
            if (isset($this->classes[$key])) {
                throw new Exception('Class already exists');
            }

            $this->classes[$key] = $class;
        }
    }
}
