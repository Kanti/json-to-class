<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Transformer;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

final  class Transformer implements LoggerAwareInterface
{
    private ?LoggerInterface $logger = null;

    public function __construct(
        public readonly OnExtraProperties $onExtraProperties = OnExtraProperties::IGNORE,
        public readonly OnMissingProperties $onMissingProperties = OnMissingProperties::SET_DEFAULT,
    ) {
        $this->logger = new ConsoleLogger(new ConsoleOutput());
    }

    public static function get(?Transformer $converter): Transformer
    {
        return $converter ?? new Transformer();
    }

    public function for(array $data): TransformerInstance
    {
        return new TransformerInstance($this, $data);
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
