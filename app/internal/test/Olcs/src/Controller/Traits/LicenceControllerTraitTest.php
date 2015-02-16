<?php

namespace OlcsTest\Controller\Traits;

use Mockery as m;
use Zend\View\HelperPluginManager as HelperPluginManager;
use Zend\ServiceManager\ServiceManager as ServiceLocator;
use Common\Service\Data\Licence as LicenceService;

/**
 * Class LicenceControllerTraitTest
 * @package OlcsTest\Controller\Traits
 */
class LicenceControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSetupMarkers()
    {
        $licence = [
            'id' => '1',
            'name' => 'shaun',
            'cases' => [
                0 => []
            ]
        ];

        $sut = $this->getMockForTrait(
            '\Olcs\Controller\Traits\LicenceControllerTrait',
            [],
            uniqid('mock_LicenceControllerTrait_testSetupMarkers'),
            false, // don't call constructor,
            false, // call clone
            true, // call clone
            ['getViewHelperManager', 'getServiceLocator']
        );

        $markers = ['foo' => 'bar'];

        $sl = $this->getMock('\Zend\Service\Manager', ['get']);
        $mpm = $this->getMock('Olcs\Service\Marker\MarkerPluginManager', ['get']);
        $cm = $this->getMock('Olcs\Service\Marker\LicenceMarkers', ['generateMarkerTypes']);

        $cm->expects($this->any())->method('generateMarkerTypes')->with(
            ['appeal', 'stay']
        )->will($this->returnValue($markers));

        $mpm->expects($this->any())->method('get')->with('Olcs\Service\Marker\LicenceMarkers')->will(
            $this->returnValue($cm)
        );

        $sl->expects($this->once())->method('get')->with('Olcs\Service\Marker\MarkerPluginManager')
            ->will($this->returnValue($mpm));

        $sut->expects($this->once())->method('getServiceLocator')->will($this->returnValue($sl));

        $this->assertEquals([0 => $markers], $sut->setupMarkers($licence));

    }
}
