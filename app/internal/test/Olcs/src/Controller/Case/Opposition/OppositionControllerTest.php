<?php

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OppositionControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\Opposition\OppositionController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        parent::setUp();
    }

    public function testAlterForm()
    {
        $data = [
            'licence' => [
                'goodsOrPsv' => [
                    'id' => 'lcat_psv'
                ]
            ],
            'oooDate' => '2015-02-01',
            'oorDate' => '2015-02-01'
        ];

        $sut = m::mock('Olcs\Controller\Cases\Opposition\OppositionController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getCaseWithOppositionDates')->andReturn($data);

        $form = new \Zend\Form\Form();

        $fieldset = new \Zend\Form\Fieldset('fields');

        $appOcList = new \Zend\Form\Element\Select('applicationOperatingCentres');
        $fieldset->add($appOcList);

        $licOcList = new \Zend\Form\Element\Select('licenceOperatingCentres');
        $fieldset->add($licOcList);

        $oppositonType = new \Zend\Form\Element\Select('oppositionType');
        $oppositonType->setValueOptions(
            [
                'otf_eob' => 'Environmental objection',
                'otf_rep' => 'Representation',
                'otf_obj' => 'Objection'
            ]
        );
        $fieldset->add($oppositonType);

        $outOfRepresentationDate = new \Common\Form\Elements\Types\Html('outOfRepresentationDate');
        $fieldset->add($outOfRepresentationDate);

        $outOfObjectionDate = new \Common\Form\Elements\Types\Html('outOfObjectionDate');
        $fieldset->add($outOfObjectionDate);

        $form->add($fieldset);
        $alteredForm = $sut->alterFormForEdit($form, []);

        $newOptions = $alteredForm->get('fields')
            ->get('oppositionType')
            ->getValueOptions();

        $this->assertNotContains('otf_eob', array_keys($newOptions));
        $this->assertNotContains('otf_rep', array_keys($newOptions));

        $oorDateObj = new \DateTime($data['oorDate']);
        $this->assertStringMatchesFormat(
            'Out of representation ' . $oorDateObj->format('d/m/Y'),
            $outOfRepresentationDate->getLabel()
        );

        $oooDateObj = new \DateTime($data['oooDate']);
        $this->assertStringMatchesFormat(
            'Out of objection ' . $oooDateObj->format('d/m/Y'),
            $outOfObjectionDate->getLabel()
        );
    }

    /**
     * Tests the generate action
     *
     */
    public function testGenerateAction()
    {
        $caseId = 12;
        $oppositionId = 123;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('opposition')->andReturn($oppositionId);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->once()->with(
            'case_licence_docs_attachments/entity/generate',
            [
                'case' => $caseId,
                'entityType' => 'opposition',
                'entityId' => $oppositionId
            ]
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);
        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->generateAction());
    }
}
