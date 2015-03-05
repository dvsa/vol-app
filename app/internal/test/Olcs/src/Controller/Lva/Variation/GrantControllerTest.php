<?php

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractGrantControllerTestCase;

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class GrantControllerTest extends AbstractGrantControllerTestCase
{
    protected $controllerClass = '\Olcs\Controller\Lva\Variation\GrantController';

    public function setUp()
    {
        parent::setUp();
        $this->mockRender();
    }

    public function testGrantActionGetValid()
    {
        $id = 69;
        $sections = ['foo','bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $mockForm = $this->createMockForm('GenericConfirmation');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('confirm-grant-application');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $this->mockService('Processing\VariationSection', 'setApplicationId')->with($id);
        $this->mockService('Processing\VariationSection', 'hasChanged')
            ->andReturn(true);
        $this->mockService('Processing\VariationSection', 'getSectionsRequiringAttention')
            ->andReturn([]);

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
        $this->assertEquals($mockForm, $view->getVariable('form'));
    }

    public function testGrantActionGetInvalidTracking()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(false);

        $this->mockService('Processing\VariationSection', 'setApplicationId')->with($id);
        $this->mockService('Processing\VariationSection', 'hasChanged')
            ->andReturn(true);
        $this->mockService('Processing\VariationSection', 'getSectionsRequiringAttention')
            ->andReturn([]);

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->expectGuidanceMessage('application-grant-error-tracking');

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }

    public function testGrantActionGetInvalidNothingChanged()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $this->mockService('Processing\VariationSection', 'setApplicationId')->with($id);
        $this->mockService('Processing\VariationSection', 'hasChanged')
            ->andReturn(false);
        $this->mockService('Processing\VariationSection', 'getSectionsRequiringAttention')
            ->never();

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->expectGuidanceMessage('variation-grant-error-no-change');

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }

    public function testGrantActionGetInvalidSectionsRequireAttention()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $this->mockService('Processing\VariationSection', 'setApplicationId')->with($id);
        $this->mockService('Processing\VariationSection', 'hasChanged')
            ->andReturn(true);
        $this->mockService('Processing\VariationSection', 'getSectionsRequiringAttention')
            ->with($id)
            ->andReturn(['financial_evidence', 'undertakings']);

        $this->translatorMock
            ->shouldReceive('translate')
                ->with('lva.section.title.financial_evidence')
                ->once()
                ->andReturn('Financial Evidence')
            ->shouldReceive('translate')
                ->with('lva.section.title.undertakings')
                ->once()
                ->andReturn('Review & Declarations')
            ->shouldReceive('translateReplace')
                ->with('variation-grant-error-sections', ['Financial Evidence, Review & Declarations'])
                ->once()
                ->andReturn('SECTIONS REQUIRE ATTENTION');

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->expectGuidanceMessage('SECTIONS REQUIRE ATTENTION');

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }

    public function testGrantActionGetInvalidFees()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $this->mockService('Processing\VariationSection', 'setApplicationId')->with($id);
        $this->mockService('Processing\VariationSection', 'hasChanged')
            ->andReturn(true);
        $this->mockService('Processing\VariationSection', 'getSectionsRequiringAttention')
            ->with($id)
            ->andReturn([]);

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(false);

        $this->expectGuidanceMessage('application-grant-error-fees');

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }
}
