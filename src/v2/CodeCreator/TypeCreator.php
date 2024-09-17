<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\CodeCreator;

use Kanti\JsonToClass\v2\Attribute\Types;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;

final class TypeCreator
{
    public function getPhpType(NamedSchema $property, PhpNamespace $namespace): string
    {
        $types = [];
        foreach (array_keys($property->basicTypes) as $type) {
            $types[] = (string)$type;
        }

        if ($property->properties !== null) {
            $namespace->addUse($property->className, Helpers::extractShortName($property->className));
            $types[] = $property->className;
        }

        if ($property->listElement) {
            $types[] = 'array';
        }

        $types = $this->sortTypes($types);

        return implode('|', $types);
    }

    private function sortTypes(array $types): array
    {
        usort($types, function (string|array $a, string|array $b): int {
            $ranking = [
                // list => 0,
                // class => 1,
                'string' => 2,
                'float' => 3,
                'int' => 4,
                'bool' => 5,
                'null' => 6,
            ];

            // if not in list it must be a class Classes come first
            $aInt = 0;
            if (!$this->isListType($a)) {
                $aInt = $ranking[$a] ?? 1;
            }

            $bInt = 0;
            if (!$this->isListType($b)) {
                $bInt = $ranking[$b] ?? 1;
            }

            return $aInt <=> $bInt;
        });
        return $types;
    }

    private function isListType(string|array $value): bool
    {
        if (is_array($value)) {
            return true;
        }

        if ($value === 'array{}') {
            return true;
        }

        if (str_starts_with($value, 'list<')) {
            return true;
        }

        return str_starts_with($value, '[');
    }

    public function getDocBlockType(NamedSchema $property, PhpNamespace $namespace): ?string
    {
        if (!$property->listElement) {
            // if the first level does not have a list element, we don't need a doc block for this property
            return null;
        }

        $types = $this->getDocBlockTypes($property, $namespace);
        return implode('|', $types);
    }

    private function getDocBlockTypes(NamedSchema $property, PhpNamespace $namespace): array
    {
        $types = [];
        foreach (array_keys($property->basicTypes) as $type) {
            $types[] = (string)$type;
        }

        if ($property->properties !== null) {
            $namespace->addUse($property->className, Helpers::extractShortName($property->className));
            $types[] = $namespace->simplifyName($property->className);
        }

        if ($property->listElement) {
            $childType = $this->getDocBlockTypes($property->listElement, $namespace);
            if ($childType) {
                $types[] = 'list<' . implode('|', $childType) . '>';
            } else {
                $types[] = 'array{}';
            }
        }

        return $this->sortTypes($types);
    }

    public function getAttribute(NamedSchema $property, PhpNamespace $namespace): ?Attribute
    {
        if (!$property->listElement) {
            return null;
        }

        $types = $this->getAttributeTypes($property, $namespace);
        $namespace->addUse(Types::class, Helpers::extractShortName(Types::class));
        return new Attribute(Types::class, $types);
    }

    /**
     * @return list<string>
     */
    private function getAttributeTypes(NamedSchema $property, PhpNamespace $namespace): array
    {
        $types = [];
        foreach (array_keys($property->basicTypes) as $type) {
            $types[] = (string)$type;
        }

        if ($property->properties !== null) {
            $namespace->addUse($property->className, Helpers::extractShortName($property->className));
            $types[] = new Literal($namespace->simplifyName($property->className) . '::class');
        }

        if ($property->listElement) {
            $childTypes = $this->getAttributeTypes($property->listElement, $namespace);
            foreach ($childTypes as $childType) {
                $types[] = [$childType];
            }

            if (!$childTypes) {
                $types[] = [];
            }
        }

        return $this->sortTypes($types);
    }
}
