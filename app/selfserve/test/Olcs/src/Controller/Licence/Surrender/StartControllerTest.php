<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Surrender;

use PHPUnit\Framework\TestCase;

final class StartControllerTest extends TestCase
{
    public function testDirectGetIsRedirectedWhenSurrenderIsNotAllowed(): void
    {
        $sut = $this->createSut();
        $sut->setLicenceData(['isLicenceSurrenderAllowed' => false]);

        $this->assertSame('lva-licence', $sut->checkConditionalDisplay());
    }

    public function testDirectGetIsDisplayedWhenSurrenderIsAllowed(): void
    {
        $sut = $this->createSut();
        $sut->setLicenceData(['isLicenceSurrenderAllowed' => true]);

        $this->assertNull($sut->checkConditionalDisplay());
    }

    private function createSut(): TestStartController
    {
        $reflection = new \ReflectionClass(TestStartController::class);

        return $reflection->newInstanceWithoutConstructor();
    }
}
