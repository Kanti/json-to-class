<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Transformer;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use RuntimeException;

/**
 * @implements IteratorAggregate<string, mixed>
 */
final class TransformerInstance implements IteratorAggregate
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, true> $fields
     */
    public function __construct(
        private readonly Transformer $transformer,
        private array $data,
        private array $fields = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    private function doConvert(): array
    {
        $result = [];
        foreach (array_keys($this->fields) as $field) {
            if (array_key_exists($field, $this->data)) {
                $result[$field] = $this->data[$field];
                continue;
            }

            $message = 'Missing property `' . $field . '` in ' . json_encode($this->data, JSON_THROW_ON_ERROR);

            if ($this->transformer->onMissingProperties === OnMissingProperties::THROW_EXCEPTION) {
                throw new RuntimeException($message);
            }

            $this->transformer->getLogger()?->warning($message, ['field' => $message, 'data' => $this->data]);
        }

        foreach (array_keys($this->data) as $key) {
            if (!isset($this->fields[$key])) {
                $message = 'Extra property `' . $key . '` in ' . json_encode($this->data, JSON_THROW_ON_ERROR);
                if ($this->transformer->onExtraProperties === OnExtraProperties::THROW_EXCEPTION) {
                    throw new RuntimeException($message);
                }

                $this->transformer->getLogger()?->warning($message, ['field' => $message, 'data' => $this->data]);
            }
        }

        return $result;
    }

    /**
     * @param class-string $className
     */
    public function class(string $field, string $className, int $depth = 0): self
    {
        $this->fields[$field] = true;

        if (!isset($this->data[$field])) {
            // do nothing here. will check later if this field is required
            return $this;
        }

        if ($depth === 0) {
            $this->data[$field] = $className::from($this->data[$field], $this->transformer);
            return $this;
        }

        if ($depth === 1) {
            foreach ($this->data[$field] as $key => $value) {
                $this->data[$field][$key] = $className::from($value, $this->transformer);
            }

            return $this;
        }

        if ($depth === 2) {
            foreach ($this->data[$field] as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $this->data[$field][$key][$key2] = $className::from($value2, $this->transformer);
                }
            }

            return $this;
        }

        throw new Exception('Not implemented this depth of ' . $depth);
    }

    public function native(string $field): self
    {
        $this->fields[$field] = true;
        // do no conversion
        // TODO check depth? in some way?
        return $this;
    }

    /**
     * @return ArrayIterator<string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->doConvert());
    }
}
