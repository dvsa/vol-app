<?php

/**
 * Undertakings Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use CommonTest\Traits\MockDateTrait;

/**
 * Undertakings Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UndertakingsControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\UndertakingsController');
    }

    public function testGetIndexAction()
    {
        $form = m::mock(\Common\Form\Form::class);
        $mockFormService = m::mock();
        $mockFormServiceManager = m::mock();
        $this->sm->setService('FormServiceManager', $mockFormServiceManager);

        $mockFormServiceManager->shouldReceive('get')
            ->once()
            ->with('lva-application-undertakings')
            ->andReturn($mockFormService);

        $mockFormService
            ->shouldReceive('getForm')
            ->once()
            ->andReturn($form);

        $applicationId = '123';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $applicationData = [
            'licenceType' => ['id' => 'ltyp_sn'],
            'goodsOrPsv' => ['id' => 'lcat_gv'],
            'niFlag' => 'N',
            'declarationConfirmation' => 'N',
            'interimReason' => 'reason',
            'version' => 1,
            'id' => $applicationId,
        ];

        $this->sut->shouldReceive('getUndertakingsData')->andReturn($applicationData);

        $expectedFormData = [
            'declarationsAndUndertakings' => [
                'declarationConfirmation' => 'N',
                'version' => 1,
                'id' => $applicationId,
            ],
        ];

        $form->shouldReceive('setData')->once()->with($expectedFormData)->andReturnSelf();

        $this->mockRender();

        $mockTranslator = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslator);

        $mockTranslator
            ->shouldReceive('translate')
            ->with('view-full-application')
            ->andReturn('view-full-application')
            ->shouldReceive('translateReplace')
            ->with('undertakings_summary_download', ['URL', 'view-full-application'])
            ->andReturn('REVIEW LINK');

        $this->sut->shouldReceive('url->fromRoute')
            ->with('lva-application/review', [], [], true)
            ->andReturn('URL');

        $form->shouldReceive('get->get->setAttribute')
            ->with('value', 'REVIEW LINK');

        $this->sut->indexAction();

        $this->assertEquals('undertakings', $this->view);
    }
}
