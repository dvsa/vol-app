<?php

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractGrantControllerTestCase;
use CommonTest\Traits\MockDateTrait;

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class GrantControllerTest extends AbstractGrantControllerTestCase
{
    use MockDateTrait;

    protected $controllerClass = '\Olcs\Controller\Lva\Application\GrantController';

    public function testGrantActionGetValid()
    {
        $id = 69;
        $sections = ['foo','bar']; // stub this, it's tested elsewhere

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

        $this->mockService('Entity\Application', 'getTypeOfLicenceData')
            ->with($id)
            ->andReturn(['licenceType' => 'ltyp_sn']);

        $this->mockService('Processing\Application', 'sectionCompletionIsValid')
            ->with($id, m::type('array'))
            ->andReturn(true);

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockRender();

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

        $this->mockService('Entity\Application', 'getTypeOfLicenceData')
            ->with($id)
            ->andReturn(['licenceType' => 'ltyp_sr']);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(false);

        $this->mockService('Processing\Application', 'sectionCompletionIsValid')
            ->with($id, m::type('array'))
            ->andReturn(true);

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->expectGuidanceMessage('application-grant-error-tracking');

        $this->mockRender();

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }

    public function testGrantActionGetInvalidSectionCompletion()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Entity\Application', 'getTypeOfLicenceData')
            ->with($id)
            ->andReturn(['licenceType' => 'ltyp_sr']);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $requiredSections = $requiredSections = [
            'type_of_licence',
            'business_type',
            'business_details',
            'addresses',
            'people',
            'taxi_phv', // licence type is SR
        ];

        $this->mockService('Processing\Application', 'sectionCompletionIsValid')
            ->with($id, m::type('array'))
            ->andReturn(false);

        $this->mockService('Processing\Application', 'getIncompleteSections')
            ->with($id, $requiredSections)
            ->andReturn(['addresses', 'people']);

        $this->translatorMock
            ->shouldReceive('translate')
                ->with('lva.section.title.addresses')
                ->once()
                ->andReturn('Addresses')
            ->shouldReceive('translate')
                ->with('lva.section.title.people')
                ->once()
                ->andReturn('People')
            ->shouldReceive('translateReplace')
                ->with('application-grant-error-sections', ['Addresses, People'])
                ->once()
                ->andReturn('SECTION INCOMPLETE MESSAGE');

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->expectGuidanceMessage('SECTION INCOMPLETE MESSAGE');

        $this->mockRender();

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }


    public function testGrantActionGetInvalidFees()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Entity\Application', 'getTypeOfLicenceData')
            ->with($id)
            ->andReturn(['licenceType' => 'ltyp_sn']);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $this->mockService('Processing\Application', 'sectionCompletionIsValid')
            ->with($id, m::type('array'))
            ->andReturn(true);

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(false);

        $this->expectGuidanceMessage('application-grant-error-fees');

        $this->mockRender();

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testGrantActionPostCancelButton()
    {
        $id = 69;

        $this->setPost(['form-actions' => ['cancel' => 'foo']]);

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->mockService('Helper\FlashMessenger', 'addWarningMessage');

        $redirect = m::mock();
        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application', ['application' => $id])
            ->andReturn($redirect);

        $this->assertSame($redirect, $this->sut->grantAction());
    }

    public function testGrantActionWithPostConfirm()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $date = date('Y-m-d');
        $this->mockDate($date);

        $this->setPost(['form-actions' => ['submit' => '']]);

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Processing\Application', 'trackingIsValid')
            ->with($id, $sections)
            ->andReturn(true);

        $this->mockService('Entity\Application', 'getTypeOfLicenceData')
            ->with($id)
            ->andReturn(['licenceType' => 'ltyp_sn']);

        $this->mockService('Processing\Application', 'sectionCompletionIsValid')
            ->with($id, m::type('array'))
            ->andReturn(true);

        $this->mockService('Processing\Application', 'feeStatusIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Processing\Application', 'processGrantApplication')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Helper\FlashMessenger', 'addSuccessMessage')
            ->with('application-granted-successfully');

        $redirect = m::mock();
        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application', ['application' => $id])
            ->andReturn($redirect);

        $this->assertSame($redirect, $this->sut->grantAction());
    }

    /**
     * This shouldn't really happen unless someone crafts a POST request
     * or there is another update between rendering the confirmation and the
     * user clickiing 'Confirm'
     */
    public function testGrantActionWithPostInvalid()
    {
        $id = 69;

        $this->setPost(['form-actions' => ['submit' => '']]);
        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('validateGrantConditions')->once()->andReturn(['errors']);

        $this->mockService('Processing\Application', 'processGrantApplication')
            ->never();

        $this->mockService('Helper\FlashMessenger', 'addSuccessMessage')
            ->never();

        $this->expectGuidanceMessage('errors');

        $this->mockRender();

        $view = $this->sut->grantAction();
        $this->assertEquals('partials/grant', $view->getTemplate());
    }
}
