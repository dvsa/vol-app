<?php

namespace OlcsTest\Controller\Traits;

/**
 * Tests Bus Controller Trait
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->trait = $this->getMockForTrait(
            '\Olcs\Controller\Traits\BusControllerTrait', array(), '', true, true, true, array(
                'getView',
                'makeRestCall',
                'getFromRoute'
            )
        );
    }

    /**
     * Tests Bus Controller Trait
     * @author Ian Lindsay <ian@hemera-business-services.co.uk>
     */
    public function testGetViewWithBusReg()
    {
        $this->trait->expects($this->once())
            ->method('getView');

        $this->trait->expects($this->once())
            ->method('getFromRoute')
            ->with('busReg')
            ->will($this->returnValue(1));

        $this->trait->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->sampleRestResult()));

        $this->trait->getViewWithBusReg();
    }

    /**
     * Gets a sample rest result
     *
     * @return array
     */
    private function sampleRestResult()
    {
        return [
            'Results' => [
                0 => [
                    'licence' => [
                        'organisation' => [
                            'name' => 'Organisation name'
                        ]
                    ],
                    'status' => [
                        'description' => 'Bus reg status'
                    ],
                    'routeSeq' => '123456',
                    'regNo' => '1332432'
                ]
            ]
        ];
    }
}
