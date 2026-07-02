<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\UniqidGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class UniqidGeneratorTest extends MockeryTestCase
{
    public function testGetLastId(): void
    {
        $sut = new UniqidGenerator();
        $id = $sut->generateId();
        $this->assertSame($id, $sut->getLastId());
    }

    public function testGenerateId(): void
    {
        $sut = new UniqidGenerator();
        $id = trim($sut->generateId());
        $this->assertTrue(is_string($id) && ($id !== '' && $id !== '0'));
        $secondId = trim($sut->generateId());
        $this->assertTrue(is_string($secondId) && ($secondId !== '' && $secondId !== '0'));
        $thirdId = trim($sut->generateId());
        $this->assertTrue(is_string($thirdId) && ($thirdId !== '' && $thirdId !== '0'));
        $this->assertNotEquals($id, $secondId);
        $this->assertNotEquals($id, $thirdId);
        $this->assertNotEquals($secondId, $thirdId);
    }
}
