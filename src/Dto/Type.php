<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

final readonly class Type
{
    /**
     * name can be empty string if the type is an empty array{}
     * @param string $name
     * @param int $depth
     */
    public function __construct(
        public string $name,
        public int $depth = 0,
    ) {
    }

    public static function from(string|array $type): self
    {
        $depth = 0;
        while (is_array($type)) {
            if (count($type) > 1) {
                throw new \InvalidArgumentException('Only one type is allowed');
            }
            $type = $type[0] ?? '';
            $depth++;
        }

        return new self($type, $depth);
    }
}
