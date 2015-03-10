<?php

namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Bootstrap;
use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Class ConditionsUndertakingsControllerTest
 *
 * @package OlcsTest\Controller\Lva\Application
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class ConditionsUndertakingsControllerTest extends AbstractLvaControllerTestCase
{
    protected $sut;

    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\ConditionsUndertakingsController');
        $this->sut->setAdapter(m::mock('\Common\Controller\Lva\Interfaces\AdapterInterface'));
    }

    public function testIndexActionWithGet()
    {
        $this->setService(
            'Table', m::mock()
        );

        $mockForm = $this->createMockForm('Lva\ConditionsUndertakings');

        $mockForm->shouldReceive('get')
            ->with('table')
            ->andReturn('form');

        $this->sut->getAdapter()->shouldReceive('getTableData')
            ->with(7)
            ->andReturn(array('foo' => 'bar'))
            ->shouldReceive('alterTable')
            ->with(m::mock())
            ->shouldReceive('getTableName')
            ->andReturn('lva-conditions-undertakings')
            ->shouldReceive('attachMainScripts');

        $this->sut->shouldReceive('getForm')
            ->andReturn($mockForm);

        $this->mockRender();

        $this->sut->indexAction();
    }
}
