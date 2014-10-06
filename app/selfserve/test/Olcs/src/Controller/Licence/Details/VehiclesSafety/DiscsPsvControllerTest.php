<?php

/**
 * Discs Psv Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Licence\Details\VehiclesSafety;

use PHPUnit_Framework_TestCase;

/**
 * Discs Psv Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DiscsPsvControllerTest extends PHPUnit_Framework_TestCase
{
    private $sut;

    protected function setUp()
    {
        $this->sut = $this->getMock(
            '\Olcs\Controller\Licence\Details\VehiclesSafety\DiscsPsvController',
            array('renderSection')
        );
    }

    /**
     * @group licence_details_controllers
     */
    public function testReplaceAction()
    {
        $this->sut->expects($this->once())
            ->method('renderSection');

        $this->sut->replaceAction();
    }

    /**
     * @group licence_details_controllers
     */
    public function testVoidAction()
    {
        $this->sut->expects($this->once())
            ->method('renderSection');

        $this->sut->voidAction();
    }
}
