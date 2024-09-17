<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends Exception implements ContainerExceptionInterface
{
}
