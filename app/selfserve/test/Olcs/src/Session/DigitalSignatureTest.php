<?php

namespace OlcsTest\Session;

use Mockery as m;

use Olcs\Session\DigitalSignature;

/**
 * Class DigitalSignatureTest
 */
class DigitalSignatureTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testHasApplicationId()
    {
        $sut = new DigitalSignature();
        $this->assertFalse($sut->hasApplicationId());
        $sut->setApplicationId(12);
        $this->assertTrue($sut->hasApplicationId());
    }

    public function testSetGetApplication()
    {
        $sut = new DigitalSignature();
        $this->assertSame(0, $sut->getApplicationId());
        $sut->setApplicationId('12');
        $this->assertSame(12, $sut->getApplicationId());
    }

    public function testHasContinuationDetailId()
    {
        $sut = new DigitalSignature();
        $this->assertFalse($sut->hasContinuationDetailId());
        $sut->setContinuationDetailId(12);
        $this->assertTrue($sut->hasContinuationDetailId());
    }

    public function testSetGetContinuationDetail()
    {
        $sut = new DigitalSignature();
        $this->assertSame(0, $sut->getContinuationDetailId());
        $sut->setContinuationDetailId('12');
        $this->assertSame(12, $sut->getContinuationDetailId());
    }
}
