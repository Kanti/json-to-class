<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\Config;

use Kanti\JsonToClass\v2\Config\Dto\OnExtraProperties;
use Kanti\JsonToClass\v2\Config\SaneConfig;
use Kanti\JsonToClass\v2\Config\StrictConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class StrictConfigTest extends TestCase
{
    #[Test]
    #[TestDox('can override properties')]
    public function construct(): void
    {
        $saneConfig = new StrictConfig(
        );
        $this->assertEquals(OnExtraProperties::THROW_EXCEPTION, $saneConfig->onExtraProperties);
        $saneConfig = new StrictConfig(
            onExtraProperties: OnExtraProperties::IGNORE,
        );
        $this->assertEquals(OnExtraProperties::IGNORE, $saneConfig->onExtraProperties);
    }
}
