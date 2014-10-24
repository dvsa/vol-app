<?php

namespace OlcsTest\Controller\Traits;

use Mockery as m;
use Zend\View\HelperPluginManager as HelperPluginManager;
use Zend\ServiceManager\ServiceManager as ServiceLocator;
use Olcs\Service\Data\Licence as LicenceService;

/**
 * Class HearingAppealControllerTrait
 * @package OlcsTest\Controller\Traits
 */
class CaseControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialiseData()
    {
        $caseId = '1';
        $case = [
            'id' => '1',
            'licence' => [
                'id' => '21'
            ]
        ];

        $getRequestMock = $this->getMock('\stdClass', ['isXmlHttpRequest']);
        $getRequestMock->expects($this->once())->method('isXmlHttpRequest')->will($this->returnValue(false));

        $paramsMock = $this->getMock('\stdClass', ['fromRoute']);
        $paramsMock->expects($this->exactly(2))
                   ->method('fromRoute')
                   ->will(
                       $this->returnValueMap(
                           array(
                                array('case', $caseId),
                                array('licence', $case['licence']['id'], $case['licence']['id'])
                            )
                       )
                   );

        $sut = $this->getMockForTrait(
            '\Olcs\Controller\Traits\CaseControllerTrait',
            [],
            uniqid('mock_CaseControllerTrait_testInitialiseData'),
            false, // don't call constructor,
            false, // call clone
            true, // call clone
            ['getRequest', 'params', 'setupCase', 'getCase', 'setupLicence', 'setupMarkers']
        );
        $sut->expects($this->any())->method('getRequest')->will($this->returnValue($getRequestMock));
        $sut->expects($this->any())->method('params')->will($this->returnValue($paramsMock));

        $sut->expects($this->once())->method('setupCase');
        $sut->expects($this->once())->method('setupMarkers');

        $sut->expects($this->any())->method('getCase')->will($this->returnValue($case));
        $sut->expects($this->exactly(1))->method('setupLicence')->with($case['licence']['id']);

        $this->assertTrue($sut->initialiseData(new \Zend\Mvc\MvcEvent()));
    }

    public function testSetupLicence()
    {
        $licenceId = '1';
        $licence = [
            'id' => '1',
            'licNo' => '1234',
        ];

        $sut = $this->getMockForTrait(
            '\Olcs\Controller\Traits\CaseControllerTrait',
            [],
            uniqid('mock_CaseControllerTrait_testSetupLicence'),
            false, // don't call constructor,
            false, // call clone
            true, // call clone
            ['getViewHelperManager', 'url', 'getServiceLocator']
        );

        $url = $this->getMock('\stdClass', ['fromRoute']);
        $url->expects($this->once())->method('fromRoute')
            ->with('licence/cases', ['licence' => $licence['id']])
            ->will($this->returnValue('LICENCE'));

        // View Helper Magaer (Service Locator)
        $vh = new HelperPluginManager();

        $pl = $this->getMock('\Zend\View\Helper\Placeholder', ['getContainer', 'prepend']);
        $pl->expects($this->once())->method('getContainer')->with('pageTitle')->will($this->returnSelf());
        $pl->expects($this->once())->method('prepend');

        // Add placeholder to plugin manager.
        $vh->setService('placeholder', $pl);

        // Service locator.
        $sl = new ServiceLocator();

        $ls = $this->getMock('Olcs\Service\Data\Licence', ['setId', 'fetchLicenceData']);
        $ls->expects($this->once())->method('setId')->with($this->equalTo($licenceId));
        $ls->expects($this->once())->method('fetchLicenceData')
           ->with($this->equalTo($licenceId))->will($this->returnValue($licence));

        $sl->setService('Olcs\Service\Data\Licence', $ls);

        // view helper manager.
        $sut->expects($this->once())->method('getViewHelperManager')->will($this->returnValue($vh));

        $sut->expects($this->once())->method('getServiceLocator')->will($this->returnValue($sl));

        $sut->expects($this->once())->method('url')->will($this->returnValue($url));

        $this->assertNull($sut->setupLicence($licenceId));
    }

    public function testSetupCase()
    {
        $case = [
            'id' => '1',
            'name' => 'craig'
        ];

        $sut = $this->getMockForTrait(
            '\Olcs\Controller\Traits\CaseControllerTrait',
            [],
            uniqid('mock_CaseControllerTrait_testSetupCase'),
            false, // don't call constructor,
            false, // call clone
            true, // call clone
            ['getViewHelperManager', 'getCase']
        );

        // View Helper Magaer (Service Locator)
        $vh = new HelperPluginManager();

        $pl = $this->getMock('\Zend\View\Helper\Placeholder', ['getContainer', 'append', 'set']);
        //$pl->expects($this->at(0))->method('getContainer')->with('pageTitle')->will($this->returnSelf());
        //$pl->expects($this->at(1))->method('getContainer')->with('pageSubtitle')->will($this->returnSelf());
        //$pl->expects($this->at(2))->method('getContainer')->with('case')->will($this->returnSelf());

        $pl->expects($this->any())->method('getContainer')->will($this->returnSelf());
        $pl->expects($this->exactly(2))->method('append');
        $pl->expects($this->exactly(1))->method('set');

        $ht = $this->getMock('Zend\View\Helper\HeadTitle', ['prepend']);
        $ht->expects($this->once())->method('prepend');

        // Add placeholder to plugin manager.
        $vh->setService('placeholder', $pl);
        $vh->setService('headTitle', $ht);

        // view helper manager.
        $sut->expects($this->exactly(2))->method('getViewHelperManager')->will($this->returnValue($vh));
        $sut->expects($this->once())->method('getCase')->will($this->returnValue($case));

        $this->assertNull($sut->setupCase());
    }

    public function testGetCase()
    {
        $caseId = '1';

        $result = [
            'Results' => [
                0 => [
                    'id' => '1',
                    'name' => 'craig'
                ],
                1 => [
                    'id' => '2',
                    'name' => 'an-other'
                ],
            ]
        ];

        $mockBuilder = $this->getMockBuilder('Olcs\Controller\Traits\CaseControllerTrait');
        $mockBuilder->setMethods(['makeRestCall']);
        $mockBuilder->setMockClassName(uniqid('mock_CaseControllerTrait_'));
        $sut = $mockBuilder->getMockForTrait();

        $sut->expects($this->once())->method('makeRestCall')
            ->with('Cases', 'GET', ['id' => $caseId], $sut->getCaseInformationBundle())
            ->will($this->returnValue($result));

        $this->assertEquals($result, $sut->getCase($caseId));

        // Should return same again. Assert method called once should fail if called again.
        $this->assertEquals($result, $sut->getCase($caseId));

    }

    public function testSetupMarkers()
    {
        $case = [
            'id' => '1',
            'name' => 'shaun'
        ];

        $sut = $this->getMockForTrait(
            '\Olcs\Controller\Traits\CaseControllerTrait',
            [],
            uniqid('mock_CaseControllerTrait_testSetupMarkers'),
            false, // don't call constructor,
            false, // call clone
            true, // call clone
            ['getViewHelperManager', 'getServiceLocator']
        );

        $vh = new HelperPluginManager();
        $markers = [];

        $pl = $this->getMock('\Zend\View\Helper\Placeholder', ['getContainer', 'append', 'set']);

        $pl->expects($this->once())->method('set')->with($markers);
        $pl->expects($this->any())->method('getContainer')->will($this->returnSelf());

        $sl = $this->getMock('\Zend\Service\Manager', ['get']);
        $mpm = $this->getMock('Olcs\Service\Marker\MarkerPluginManager', ['get']);
        $cm = $this->getMock('Olcs\Service\Marker\CaseMarkers', ['generateMarkerTypes']);

        $cm->expects($this->any())->method('generateMarkerTypes')->with(
            ['appeal', 'stay']
        )->will($this->returnValue($markers));

        $mpm->expects($this->any())->method('get')->with('Olcs\Service\Marker\CaseMarkers')->will(
            $this->returnValue($cm)
        );

        $sl->expects($this->once())->method('get')->with('Olcs\Service\Marker\MarkerPluginManager')
            ->will($this->returnValue($mpm));

        $vh->setService('placeholder', $pl);

        $sut->expects($this->once())->method('getServiceLocator')->will($this->returnValue($sl));
        $sut->expects($this->once())->method('getViewHelperManager')->will($this->returnValue($vh));

        $this->assertNull($sut->setupMarkers($case));

    }
}
