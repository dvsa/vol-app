<?php

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use CommonTest\Traits\MockDateTrait;

/**
 * Grant Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class GrantControllerTest extends AbstractLvaControllerTestCase
{
    use MockDateTrait;

    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\GrantController');
    }

    public function testGrantActionGetValid()
    {
        $id = 69;
        $sections = ['foo','bar']; // stub this, it's tested elsewhere

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

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    public function testGrantActionGetInvalidSectionCompletion()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('SECTION INCOMPLETE MESSAGE')
            ->once()
            ->andReturn('SECTION FAIL');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('SECTION FAIL');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant');

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

        $this->mockService('Helper\Translation', 'translate')
            ->with('lva.section.title.addresses')
            ->once()
            ->andReturn('Addresses');
        $this->mockService('Helper\Translation', 'translate')
            ->with('lva.section.title.people')
            ->once()
            ->andReturn('People');
        $this->mockService('Helper\Translation', 'translateReplace')
            ->with('application-grant-error-sections', ['Addresses, People'])
            ->once()
            ->andReturn('SECTION INCOMPLETE MESSAGE');

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

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
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
     * user clicking 'Confirm'
     */
    public function testGrantActionWithPostInvalid()
    {
        $id = 69;

        $this->setPost(['form-actions' => ['submit' => '']]);
        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('validateGrantConditions')->once()->andReturn(['errors']);

        $this->mockService('Helper\Translation', 'translate')
            ->with('errors')
            ->once()
            ->andReturn('ERROR MESSAGE');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('get->get->setValue')
            ->with('ERROR MESSAGE');
        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant');

        $this->mockService('Processing\Application', 'processGrantApplication')
            ->never();

        $this->mockService('Helper\FlashMessenger', 'addSuccessMessage')
            ->never();

        $this->mockRender();

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }
}
