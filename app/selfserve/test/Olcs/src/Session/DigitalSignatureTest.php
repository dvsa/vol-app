<?php

namespace OlcsTest\Session;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

use Olcs\Session\DigitalSignature;

/**
 * Class DigitalSignatureTest
 */
class DigitalSignatureTest extends TestCase
{
    public function testHasApplicationId()
    {
        $sut = new DigitalSignature();

        $this->assertFalse($sut->hasApplicationId());
        $sut->setApplicationId(12);
        $this->assertTrue($sut->hasApplicationId());
    }

    public function testSetGetApplicationId()
    {
        $sut = new DigitalSignature();
        $sut->setApplicationId('0');

        $this->assertSame(0, $sut->getApplicationId());
        $sut->setApplicationId('12');
        $this->assertSame(12, $sut->getApplicationId());
    }

    public function testHasContinuationDetailId()
    {
        $sut = new DigitalSignature();
        $sut->setContinuationDetailId(null);

        $this->assertFalse($sut->hasContinuationDetailId());
        $sut->setContinuationDetailId(12);
        $this->assertTrue($sut->hasContinuationDetailId());
    }

    public function testSetGetContinuationDetailId()
    {
        $sut = new DigitalSignature();
        $sut->setContinuationDetailId('0');

        $this->assertSame(0, $sut->getContinuationDetailId());
        $sut->setContinuationDetailId('12');
        $this->assertSame(12, $sut->getContinuationDetailId());
    }

    public function testSetGetTransportManagerApplicationId()
    {
        $sut = new DigitalSignature();
        $sut->setTransportManagerApplicationId('0');

        $this->assertSame(0, $sut->getTransportManagerApplicationId());
        $sut->setTransportManagerApplicationId('12');
        $this->assertSame(12, $sut->getTransportManagerApplicationId());
    }

    public function testHasTransportManagerApplicationId()
    {
        $sut = new DigitalSignature();
        $sut->setTransportManagerApplicationId('0');

        $this->assertEquals(0, $sut->getTransportManagerApplicationId());
        $sut->setTransportManagerApplicationId(7);
        $this->assertTrue($sut->hasTransportManagerApplicationId());
    }

    public function testSetGetRole()
    {
        $sut = new DigitalSignature();

        $sut->setRole('__TEST__');
        $this->assertEquals('__TEST__', $sut->getRole());
    }

    public function testGetSetLva()
    {
        $sut = new DigitalSignature();

        $sut->setLva('application');
        $this->assertEquals('application', $sut->getLva());
    }

    public function testHasLva()
    {
        $sut = new DigitalSignature();
        $sut->setLva(null);

        $this->assertEquals(0, $sut->getLva());
        $sut->setLva('__TEST__');
        $this->assertTrue($sut->hasLva());
    }

    public function testRole()
    {
        $sut = new DigitalSignature();
        $sut->setRole(null);

        $this->assertEquals(null, $sut->getRole());
        $sut->setRole('__TEST__');
        $this->assertTrue($sut->hasRole());
    }

    public function testSetGetApplication()
    {
        $sut = new DigitalSignature();
        $sut->setApplication('0');

        $this->assertSame(0, $sut->getApplicationId());
        $sut->setApplication('12');
        $this->assertSame(12, $sut->getApplicationId());
    }

    public function testSetGetContinuationDetail()
    {
        $sut = new DigitalSignature();
        $sut->setContinuationDetailId('0');

        $this->assertSame(0, $sut->getContinuationDetailId());
        $sut->setContinuationDetail('12');
        $this->assertSame(12, $sut->getContinuationDetailId());
    }

    public function testSetGetSurrenderId()
    {
        $sut = new DigitalSignature();

        $this->assertSame(0, $sut->getLicenceId());
        $sut->setLicenceId('12');
        $this->assertSame(12, $sut->getLicenceId());
    }

    public function testHasSurrenderId()
    {
        $sut = new DigitalSignature();
        $sut->setLicenceId('0');

        $this->assertEquals(0, $sut->getLicenceId());
        $sut->setLicenceId(7);
        $this->assertTrue($sut->hasLicenceId());
    }
}
