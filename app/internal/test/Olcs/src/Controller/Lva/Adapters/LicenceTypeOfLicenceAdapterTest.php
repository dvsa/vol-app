<?php

/**
 * Internal Licence Type Of Licence Adapter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters\LicenceTypeOfLicenceAdapter;

/**
 * Internal Licence Type Of Licence Adapter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceTypeOfLicenceAdapterTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new LicenceTypeOfLicenceAdapter();
    }

    public function testShouldDisableLicenceType()
    {
        $this->assertFalse($this->sut->shouldDisableLicenceType(1, null));
    }

    public function testDoesChangeRequireConfirmation()
    {
        $this->assertFalse($this->sut->doesChangeRequireConfirmation([], []));
    }

    public function testSetMessages()
    {
        $this->assertNull($this->sut->setMessages());
    }
}
