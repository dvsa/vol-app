<?php

/**
 * Financial Standing Crud Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace AdminTest\Service\Crud;

use OlcsTest\Bootstrap;
use Admin\Service\Crud\FinancialStandingCrudService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Financial Standing Crud Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialStandingCrudServiceTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        $this->sut = new FinancialStandingCrudService();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetList()
    {
        // Stubbed data
        $tableData = ['foo' => 'bar'];

        // Mocks
        $mockTable = m::mock();
        $this->sm->setService('Table', $mockTable);
        $mockFinancialStandingRate = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockFinancialStandingRate);

        // Expectations
        $mockTable->shouldReceive('prepareTable')
            ->with('admin-financial-standing', $tableData)
            ->andReturn('TABLE');

        $mockFinancialStandingRate->shouldReceive('getFullList')
            ->andReturn($tableData);

        $this->assertEquals('TABLE', $this->sut->getList());
    }

    public function testIsFormValidWithInvalidForm()
    {
        // Params
        $form = m::mock('\Zend\Form\Form');
        $id = null;

        // Expecations
        $form->shouldReceive('isValid')
            ->andReturn(false);

        $this->assertFalse($this->sut->isFormValid($form, $id));
    }

    public function testIsFormValidWithCollision()
    {
        // Params
        $form = m::mock('\Zend\Form\Form');
        $id = null;
        $mockFormData = [
            'details' => [
                'goodsOrPsv' => 'goods',
                'licenceType' => 'standard',
                'effectiveFrom' => 'foo',
                'foo' => 'bar'
            ]
        ];
        $expectedQuery = [
            'goodsOrPsv' => 'goods',
            'licenceType' => 'standard',
            'effectiveFrom' => 'foo'
        ];
        $stubbedResults = [
            'Results' => [
                [
                    'id' => 123
                ]
            ]
        ];

        // Mocks
        $mockEntity = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockEntity);
        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expecations
        $form->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($mockFormData);

        $mockEntity->shouldReceive('getList')
            ->with($expectedQuery)
            ->andReturn($stubbedResults);

        $mockFlashMessenger->shouldReceive('addErrorMessage')
            ->with('financial-standing-already-exists-validation');

        $this->assertFalse($this->sut->isFormValid($form, $id));
    }

    public function testIsFormValidWithValid()
    {
        // Params
        $form = m::mock('\Zend\Form\Form');
        $id = null;
        $mockFormData = [
            'details' => [
                'goodsOrPsv' => 'goods',
                'licenceType' => 'standard',
                'effectiveFrom' => 'foo',
                'foo' => 'bar'
            ]
        ];
        $expectedQuery = [
            'goodsOrPsv' => 'goods',
            'licenceType' => 'standard',
            'effectiveFrom' => 'foo'
        ];
        $stubbedResults = [
            'Results' => [
            ]
        ];

        // Mocks
        $mockEntity = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockEntity);

        // Expecations
        $form->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($mockFormData);

        $mockEntity->shouldReceive('getList')
            ->with($expectedQuery)
            ->andReturn($stubbedResults);

        $this->assertTrue($this->sut->isFormValid($form, $id));
    }

    public function testIsFormValidWithValidWithId()
    {
        // Params
        $form = m::mock('\Zend\Form\Form');
        $id = 123;
        $mockFormData = [
            'details' => [
                'goodsOrPsv' => 'goods',
                'licenceType' => 'standard',
                'effectiveFrom' => 'foo',
                'foo' => 'bar'
            ]
        ];
        $expectedQuery = [
            'goodsOrPsv' => 'goods',
            'licenceType' => 'standard',
            'effectiveFrom' => 'foo'
        ];
        $stubbedResults = [
            'Results' => [
                [
                    'id' => 123
                ]
            ]
        ];

        // Mocks
        $mockEntity = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockEntity);

        // Expecations
        $form->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($mockFormData);

        $mockEntity->shouldReceive('getList')
            ->with($expectedQuery)
            ->andReturn($stubbedResults);

        $this->assertTrue($this->sut->isFormValid($form, $id));
    }

    public function testProcessSaveWithAdd()
    {
        // Params
        $data = [
            'details' => [
                'foo' => 'bar',
                'version' => 0
            ]
        ];
        $id = null;
        $expectedSave = ['foo' => 'bar'];

        // Mocks
        $mockRedirect = m::mock();
        $mockEntity = m::mock();
        $mockFlashMessenger = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockEntity);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $mockEntity->shouldReceive('save')
            ->with($expectedSave);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('record-saved-successfully');

        $response = $this->sut->processSave($data, $id);
        $this->assertInstanceOf('\Common\Util\Redirect', $response);

        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, [], [], false);

        $response->process($mockRedirect);
    }

    public function testProcessSaveWithEdit()
    {
        // Params
        $data = [
            'details' => [
                'foo' => 'bar',
                'version' => 1
            ]
        ];
        $id = 123;
        $expectedSave = ['id' => 123, 'foo' => 'bar', 'version' => 1];

        // Mocks
        $mockRedirect = m::mock();
        $mockEntity = m::mock();
        $mockFlashMessenger = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockEntity);
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        // Expectations
        $mockEntity->shouldReceive('save')
            ->with($expectedSave);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('record-saved-successfully');

        $response = $this->sut->processSave($data, $id);
        $this->assertInstanceOf('\Common\Util\Redirect', $response);

        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, [], [], false);

        $response->process($mockRedirect);
    }

    public function testGetRecordData()
    {
        $id = null;

        $this->assertNull($this->sut->getRecordData($id));
    }

    public function testGetRecordDataWithId()
    {
        // Params
        $id = 123;
        $stubbedData = [
            'foo' => 'bar'
        ];
        $expected = [
            'details' => [
                'foo' => 'bar'
            ]
        ];

        // Mocks
        $mockEntity = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockEntity);
        $mockData = m::mock();
        $this->sm->setService('Helper\Data', $mockData);

        // Expectations
        $mockEntity->shouldReceive('getRecordById')
            ->with(123)
            ->andReturn($stubbedData);

        $mockData->shouldReceive('replaceIds')
            ->with($stubbedData)
            ->andReturn($stubbedData);

        $this->assertEquals($expected, $this->sut->getRecordData($id));
    }

    public function testGetForm()
    {
        $mockForm = m::mock();

        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        // Expectations
        $mockFormHelper->shouldReceive('createForm')
            ->with('FinancialStandingRate')
            ->andReturn($mockForm);

        $this->assertSame($mockForm, $this->sut->getForm());
    }

    public function testDelete()
    {
        $ids = [123];

        $mockEntityService = m::mock();
        $this->sm->setService('Entity\FinancialStandingRate', $mockEntityService);
        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('record-deleted');

        $mockEntityService->shouldReceive('delete')
            ->with(123);

        $this->sut->processDelete($ids);
    }
}
