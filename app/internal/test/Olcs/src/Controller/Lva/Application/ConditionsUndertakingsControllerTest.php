<?php

namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Bootstrap;
use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

class ConditionsUndertakingsControllerTest extends AbstractLvaControllerTestCase
{
    protected $sut;
    protected $sm;
    protected $adapter;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->adapter = m::mock('\Common\Controller\Lva\Interfaces\AdapterInterface');

        $this->sut = m::mock('\Common\Controller\Lva\AbstractConditionsUndertakingsController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);
        $this->sut->setAdapter($this->adapter);
    }

    public function testIndexActionWithGet()
    {
        // Data
        $stubbedTableData = [
            'foo' => 'bar'
        ];

        // Mocks
        $request = m::mock();
        $mockForm = m::mock('\Zend\Form\Form');
        $mockTableFieldset = m::mock();
        $mockTable = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockTableBuilder = m::mock();
        $this->sm->setService('Table', $mockTableBuilder);
        $mockScript = m::mock();
        $this->sm->setService('Script', $mockScript);

        // Expectations
        $mockScript->shouldReceive('loadFile')
            ->with('lva-crud');

        $this->sut->shouldReceive('getRenderVariables')
            ->andReturn(array('title' => null));

        $this->sut->shouldReceive('getRequest')
            ->andReturn($request)
            ->shouldReceive('getIdentifier')
            ->andReturn(7)
            ->shouldReceive('alterFormForLva')
            ->with($mockForm)
            ->shouldReceive('render')
            ->with('conditions_undertakings', $mockForm, array('title' => null))
            ->andReturn('RENDER');

        $request->shouldReceive('isPost')
            ->andReturn(false);

        $mockForm->shouldReceive('get')
            ->with('table')
            ->andReturn($mockTableFieldset);

        $mockTableBuilder->shouldReceive('prepareTable')
            ->with('lva-conditions-undertakings', $stubbedTableData)
            ->andReturn($mockTable);

        $this->adapter->shouldReceive('getTableData')
            ->with(7)
            ->andReturn($stubbedTableData)
            ->shouldReceive('alterTable')
            ->with($mockTable)
            ->shouldReceive('getTableName')
            ->andReturn('lva-conditions-undertakings')
            ->shouldReceive('attachMainScripts');

        $mockFormHelper->shouldReceive('createForm')
            ->with('Lva\ConditionsUndertakings')
            ->andReturn($mockForm)
            ->shouldReceive('populateFormTable')
            ->with($mockTableFieldset, $mockTable);

        $this->assertEquals('RENDER', $this->sut->indexAction());
    }
}
