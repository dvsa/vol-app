<?php

/**
 * Return To Address Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\View\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Helper\ReturnToAddress;

/**
 * Return To Address Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReturnToAddressTest extends MockeryTestCase
{
    public function testInvokeNi()
    {
        $sut = new ReturnToAddress();

        $response = $sut(true, ', ');

        $this->assertEquals(
            'Department for Infrastructure, The Central Licensing Office, PO Box 180, Leeds, LS9 1BU',
            $response
        );
    }

    public function testInvokeGb()
    {
        $sut = new ReturnToAddress();

        $response = $sut(false, ', ');

        $this->assertEquals(
            'Office of the Traffic Commissioner, The Central Licensing Office, Hillcrest House, '
            . '386 Harehills Lane, Leeds, LS9 6NF',
            $response
        );
    }

    public function testRenderNi()
    {
        $sut = new ReturnToAddress();

        $response = $sut->render(true, '<br />');

        $this->assertEquals(
            'Department for Infrastructure<br />The Central Licensing Office<br />PO Box 180<br />Leeds<br />LS9 1BU',
            $response
        );
    }

    public function testRenderGb()
    {
        $sut = new ReturnToAddress();

        $response = $sut->render(false, '<br />');

        $this->assertEquals(
            'Office of the Traffic Commissioner<br />The Central Licensing Office<br />Hillcrest House<br />'
            . '386 Harehills Lane<br />Leeds<br />LS9 6NF',
            $response
        );
    }
    public function testStaticNi()
    {
        $response = ReturnToAddress::getAddress(true, ', ');

        $this->assertEquals(
            'Department for Infrastructure, The Central Licensing Office, PO Box 180, Leeds, LS9 1BU',
            $response
        );
    }

    public function testStaticGb()
    {
        $response = ReturnToAddress::getAddress(false, ', ');

        $this->assertEquals(
            'Office of the Traffic Commissioner, The Central Licensing Office, Hillcrest House, '
            . '386 Harehills Lane, Leeds, LS9 6NF',
            $response
        );
    }
}
