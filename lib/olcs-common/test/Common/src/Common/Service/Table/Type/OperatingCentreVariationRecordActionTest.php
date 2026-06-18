<?php

namespace CommonTest\Service\Table\Type;

use Common\Service\Table\TableBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\OperatingCentreVariationRecordAction;

/**
 * @covers Common\Service\Table\Type\OperatingCentreVariationRecordAction
 */
class OperatingCentreVariationRecordActionTest extends MockeryTestCase
{
    /** @var  OperatingCentreVariationRecordAction */
    protected $sut;

    /** @var  m\MockInterface */
    protected $table;

    #[\Override]
    protected function setUp(): void
    {
        $mockTranslator = m::mock(\Laminas\I18n\Translator\TranslatorInterface::class);

        $this->table = m::mock(TableBuilder::class);
        $this->table->expects('isInternalReadOnly')->andReturnFalse();
        $this->table->expects('getTranslator')->andReturn($mockTranslator);

        $this->sut = new OperatingCentreVariationRecordAction($this->table);
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
