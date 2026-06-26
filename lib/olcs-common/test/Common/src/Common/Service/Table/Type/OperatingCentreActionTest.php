<?php

namespace CommonTest\Service\Table\Type;

use Common\Service\Table\TableBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\OperatingCentreAction;

/**
 * OperatingCentreActionTest Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatingCentreActionTest extends MockeryTestCase
{
    protected $sut;

    protected $table;

    #[\Override]
    protected function setUp(): void
    {
        $this->table = m::mock(TableBuilder::class);
        $this->table->expects('isInternalReadOnly')->andReturnFalse();

        $this->sut = new OperatingCentreAction($this->table);
    }

    public function testRenderNoS4(): void
    {
        $this->table->shouldReceive('getFieldset')->with()->once()->andReturn(null);

        $data = ['id' => 1];
        $column = ['action' => 'FOO'];

        $this->assertStringNotContainsString('(Schedule 4/1)', $this->sut->render($data, $column));
    }

    public function testRenderWithS4(): void
    {
        $this->table->shouldReceive('getFieldset')->with()->once()->andReturn(null);

        $data = ['id' => 1, 's4' => 'FOO'];
        $column = ['action' => 'FOO'];

        $this->assertStringContainsString('(Schedule 4/1)', $this->sut->render($data, $column));
    }
}
