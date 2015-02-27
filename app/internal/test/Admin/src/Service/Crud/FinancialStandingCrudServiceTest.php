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
}
