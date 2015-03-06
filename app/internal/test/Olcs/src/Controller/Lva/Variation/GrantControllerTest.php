<?php

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class GrantControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Variation\GrantController');
    }

    public function testGrantActionGetValid()
    {
        $id = 69;
        $sections = ['foo','bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('confirm-grant-application');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->overview');

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

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    public function testGrantActionGetInvalidTracking()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('application-grant-error-tracking')
            ->once()
            ->andReturn('TRACKING FAIL');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('TRACKING FAIL');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant');

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

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    public function testGrantActionGetInvalidNothingChanged()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('variation-grant-error-no-change')
            ->once()
            ->andReturn('NOTHING CHANGED');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('NOTHING CHANGED');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant');

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

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    public function testGrantActionGetInvalidSectionsRequireAttention()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('SECTIONS REQUIRE ATTENTION')
            ->once()
            ->andReturn('SECTION FAIL');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('SECTION FAIL');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant');

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $this->mockService('Processing\VariationSection', 'setApplicationId')->with($id);
        $this->mockService('Processing\VariationSection', 'hasChanged')
            ->andReturn(true);
        $this->mockService('Processing\VariationSection', 'getSectionsRequiringAttention')
            ->with($id)
            ->andReturn(['financial_evidence', 'undertakings']);

        $this->mockService('Helper\Translation', 'translate')
            ->with('lva.section.title.financial_evidence')
            ->once()
            ->andReturn('Financial Evidence');
        $this->mockService('Helper\Translation', 'translate')
            ->with('lva.section.title.undertakings')
            ->once()
            ->andReturn('Review & Declarations');
        $this->mockService('Helper\Translation', 'translateReplace')
            ->with('variation-grant-error-sections', ['Financial Evidence, Review & Declarations'])
            ->once()
            ->andReturn('SECTIONS REQUIRE ATTENTION');

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    public function testGrantActionGetInvalidFees()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('application-grant-error-fees')
            ->once()
            ->andReturn('FEE FAIL');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('FEE FAIL');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant');

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

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }
}
