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
        $this->markTestSkipped();

        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\GrantController');
    }

    /**
     * @group applicationGrantControllerTest
     */
    public function testGrantActionGetValid()
    {
        $id = 69;
        $sections = ['foo','bar']; // stub this, it's tested elsewhere

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('setData')
            ->with([])
            ->once()
            ->shouldReceive('get')
            ->with('messages')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('message')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn('confirm-grant-application')
                    ->once()
                    ->shouldReceive('setValue')
                    ->andReturn('confirm-grant-application')
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

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

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->request
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(true);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->grantAction());
    }

    /**
     * @group applicationGrantControllerTest
     */
    public function testGrantActionGetInvalidTracking()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $mockRequest = m::mock();
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $this->mockService('Helper\Translation', 'translate')
            ->with('application-grant-error-tracking')
            ->once()
            ->andReturn('TRACKING FAIL');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('setData')
            ->with([])
            ->shouldReceive('get')
            ->with('messages')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('message')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValue')
                    ->andReturn('TRACKING FAIL')
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

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

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Helper\Form', 'createForm')
            ->with('Grant')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-details')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-confirm')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant')
            ->once()
            ->shouldReceive('setFormActionFromRequest');

        $this->mockRender();

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    /**
     * @group applicationGrantControllerTest
     */
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
        $mockForm->shouldReceive('setData')
            ->with([])
            ->shouldReceive('get->get->setValue')
            ->with('SECTION FAIL');

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

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

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Helper\Form', 'createForm')
            ->with('Grant')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-details')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-confirm')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant')
            ->once()
            ->shouldReceive('setFormActionFromRequest');

        $this->mockRender();

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    /**
     * @group applicationGrantControllerTest
     */
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
        $mockForm->shouldReceive('setData')
            ->with([])
            ->shouldReceive('get->get->setValue')
            ->with('FEE FAIL');

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

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

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Helper\Form', 'createForm')
            ->with('Grant')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-details')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-confirm')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant')
            ->once()
            ->shouldReceive('setFormActionFromRequest');

        $this->mockRender();

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    public function testGrantActionGetInvalidEnforcementArea()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('application-grant-error-enforcement-area')
            ->once()
            ->andReturn('ENFORCEMENT AREA FAIL');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('setData')
            ->with([])
            ->shouldReceive('get->get->setValue')
            ->with('ENFORCEMENT AREA FAIL');

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request);
        $this->getMockFormHelper()->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant');

        $this->mockService('Entity\Application', 'getTypeOfLicenceData')
            ->with($id)
            ->andReturn(['licenceType' => 'ltyp_sr']);

        $this->mockService('Processing\Application', 'trackingIsValid')->andReturn(true);

        $this->mockService('Processing\Application', 'sectionCompletionIsValid')->andReturn(true);

        $this->mockService('Processing\Application', 'feeStatusIsValid')->andReturn(true);

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->once()
            ->with($id)
            ->andReturn(false);

        $this->mockService('Helper\Form', 'createForm')
            ->with('Grant')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-details')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'inspection-request-confirm')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->grant')
            ->once()
            ->shouldReceive('setFormActionFromRequest');

        $this->mockRender();

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    /**
     * @group applicationGrantControllerTest
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

    /**
     * @group applicationGrantControllerTest
     */
    public function testGrantActionWithPostConfirmNoErrors()
    {
        $id = 69;
        $sections = ['foo', 'bar'];

        $date = date('Y-m-d');
        $this->mockDate($date);

        $post = [
            'form-actions' => ['submit' => ''],
            'inspection-request-confirm' => ['createInspectionRequest' => 'Y'],
            'inspection-request-grant-details' => ['dueDate' => '3']
        ];

        $this->setPost($post);

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

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Helper\FlashMessenger', 'addSuccessMessage')
            ->with('application-granted-successfully');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('setData')
            ->with($post);

        $this->mockService('Helper\Form', 'createForm')
            ->with('Grant')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest');

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

        $this->mockService('BusinessServiceManager', 'get')
            ->with('InspectionRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->with(
                    [
                        'data' => $post,
                        'applicationId' => $id,
                        'type' => 'applicationFromGrant'
                    ]
                )
                ->getMock()
            );

        $redirect = m::mock();
        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application', ['application' => $id])
            ->andReturn($redirect);

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertSame($redirect, $this->sut->grantAction());
    }

    /**
     * This shouldn't really happen unless someone crafts a POST request
     * or there is another update between rendering the confirmation and the
     * user clicking 'Confirm'
     * @group applicationGrantControllerTest
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
        $mockForm->shouldReceive('setData')
            ->with(['form-actions' => ['submit' => '']])
            ->shouldReceive('get->get->setValue')
            ->with('ERROR MESSAGE');

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

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

    /**
     * @group applicationGrantControllerTest
     */
    public function testGrantActionNoInspectionRequestSelected()
    {
        $id = 69;
        $sections = ['foo', 'bar'];
        $this->setPost(['form-actions' => ['submit' => 'foo']]);

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('application-grant-please-confirm-inspection-request')
            ->once()
            ->andReturn('IR NOT SELECTED');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('setData')
            ->with(['form-actions' => ['submit' => 'foo']])
            ->shouldReceive('get->get->setValue')
            ->with('IR NOT SELECTED');

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

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
            ->andReturn(true);

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Helper\Form', 'createForm')
            ->with('Grant')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest');

        $this->mockRender();

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    /**
     * @group applicationGrantControllerTest
     */
    public function testGrantActionNoDueDateSelected()
    {
        $id = 69;
        $sections = ['foo', 'bar'];
        $this->setPost(
            [
                'form-actions' => ['submit' => 'foo'],
                'inspection-request-confirm' => ['createInspectionRequest' => 'Y']
            ]
        );

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $this->mockService('Helper\Translation', 'translate')
            ->with('application-grant-provide-due-date')
            ->once()
            ->andReturn('IR NOT SELECTED');

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('setData')
            ->with(
                [
                    'form-actions' => ['submit' => 'foo'],
                    'inspection-request-confirm' => ['createInspectionRequest' => 'Y']
                ]
            )
            ->shouldReceive('get->get->setValue')
            ->with('IR NOT SELECTED');

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

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
            ->andReturn(true);

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->mockService('Helper\Form', 'createForm')
            ->with('Grant')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest');

        $this->mockRender();

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertEquals('grant_application', $this->sut->grantAction());
    }

    public function testGrantActionGetValidNoMessage()
    {
        $id = 69;
        $sections = ['foo','bar']; // stub this, it's tested elsewhere

        $this->sut->shouldReceive('params')->with('application')->andReturn($id);

        $this->sut->shouldReceive('getAccessibleSections')->andReturn($sections);

        $mockForm = $this->createMockForm('Grant');
        $mockForm->shouldReceive('setData')
            ->with([])
            ->once()
            ->shouldReceive('get')
            ->with('messages')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('message')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValue')
                    ->andReturn('')
                    ->once()
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $this->mockService('Script', 'loadFiles')
            ->with(['forms/confirm-grant']);

        $this->getMockFormHelper()->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $this->request)
            ->shouldReceive('remove')
            ->with($mockForm, 'messages')
            ->once();

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

        $this->mockService('Processing\Application', 'enforcementAreaIsValid')
            ->with($id)
            ->andReturn(true);

        $this->request
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(true);

        $this->sut->shouldReceive('shouldValidateEnforcementArea')->andReturn(true);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->grantAction());
    }

    /**
     * @dataProvider dataProviderShouldValidateEnforcementArea
     */
    public function testShouldValidateEnforcementArea($expected, $goodsOrPsv, $licenceType)
    {
        $this->mockService('Entity\Application', 'getTypeOfLicenceData')
            ->with(1066)
            ->andReturn(['licenceType' => $licenceType, 'goodsOrPsv' => $goodsOrPsv]);

        $this->assertSame($expected, $this->sut->shouldValidateEnforcementArea(1066));
    }

    public function dataProviderShouldValidateEnforcementArea()
    {
        return [
            [true, 'lcat_gv', 'ltyp_sn'],
            [true, 'lcat_gv', 'ltyp_sr'],
            [true, 'lcat_gv', 'ltyp_si'],
            [true, 'lcat_gv', 'ltyp_r'],
            [true, 'lcat_gv', 'xxxx'],
            [true, 'lcat_psv', 'ltyp_sn'],
            [true, 'lcat_psv', 'ltyp_si'],
            [false, 'lcat_psv', 'ltyp_sr'],
            [true, 'lcat_psv', 'ltyp_r'],
            [true, 'lcat_psv', 'xxxxx'],
            [true, 'lcat_psv', 'xxxxx'],
        ];
    }
}
