<?php

declare(strict_types=1);

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Olcs\Controller\Cases\Opposition;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\OlcsTest\Controller\ControllerPluginManagerHelper;
use Dvsa\OlcsTest\Controller\ControllerRouteMatchHelper;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class OppositionControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    protected $sut;

    public function setUp(): void
    {

        $translationHelper = m::mock(TranslationHelperService::class);
        $formHelper = m::mock(FormHelperService::class);
        $flashMessengerHelper =  m::mock(FlashMessengerHelperService::class);
        $navigation = m::mock(Navigation::class);

        $this->sut = new \Olcs\Controller\Cases\Opposition\OppositionController($translationHelper, $formHelper, $flashMessengerHelper, $navigation);

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $routeMatchHelper = new ControllerRouteMatchHelper();
        parent::setUp();
    }

    public function testAlterForm(): void
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

        $sut = m::mock(\Olcs\Controller\Cases\Opposition\OppositionController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $sut->shouldReceive('getCaseWithOppositionDates')->andReturn($data);

        $form = new \Laminas\Form\Form();

        $fieldset = new \Laminas\Form\Fieldset('fields');

        $appOcList = new \Laminas\Form\Element\Select('applicationOperatingCentres');
        $fieldset->add($appOcList);

        $licOcList = new \Laminas\Form\Element\Select('licenceOperatingCentres');
        $fieldset->add($licOcList);

        $oppositonType = new \Laminas\Form\Element\Select('oppositionType');
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
     */
    public function testGenerateAction(): void
    {
        $caseId = 12;
        $oppositionId = 123;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );
        $mockParams = $mockPluginManager->get('params');
        $mockParams->shouldReceive('fromRoute')->with('opposition')->andReturn($oppositionId);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRedirect = $mockPluginManager->get('redirect');
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
