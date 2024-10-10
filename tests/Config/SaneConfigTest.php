<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Config;

use Kanti\JsonToClass\Config\Enums\OnExtraProperties;
use Kanti\JsonToClass\Config\SaneConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class SaneConfigTest extends TestCase
{
    #[Test]
    #[TestDox('can override properties')]
    public function construct(): void
    {
        $saneConfig = new SaneConfig(
        );
        $this->assertEquals(OnExtraProperties::IGNORE, $saneConfig->onExtraProperties);
        $saneConfig = new SaneConfig(
            onExtraProperties: OnExtraProperties::THROW_EXCEPTION,
        );
        $this->assertEquals(OnExtraProperties::THROW_EXCEPTION, $saneConfig->onExtraProperties);
    }
}
