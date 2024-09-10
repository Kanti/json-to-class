<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Parameter;

final class SchemaFromClassGenerator
{
    public function generate(FullyQualifiedClassName $class): SchemaElement
    {
        $classType = ClassType::from((string)$class);
        assert($classType instanceof ClassType);

        $schemaElement = new SchemaElement();

        $parameters = $classType->getMethod('__construct')->getParameters();
        foreach ($parameters as $parameter) {
            $schemaElement->properties[$parameter->getName()] = new SchemaElement();
        }

        dd($parameters, $schemaElement);
    }
}
