<?php

namespace CommonTest\Service\Table\Type;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\VariationRecordAction;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator as Translator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Service\Table\Type\VariationRecordAction
 */
class VariationRecordActionTest extends MockeryTestCase
{
    /** @var  VariationRecordAction */
    protected $sut;

    /** @var  m\MockInterface */
    protected $table;

    /** @var  m\MockInterface */
    private $mockTranslator;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = m::mock(Translator::class);

        $this->table = m::mock(TableBuilder::class);
        $this->table->expects('isInternalReadOnly')->andReturnFalse();
        $this->table->expects('getTranslator')->withNoArgs()->andReturn($this->mockTranslator);

        $this->sut = new VariationRecordAction($this->table);
    }

    /**
     * @dataProvider provider
     */
    public function testRender($action, $prefix, $expected): void
    {
        if ($prefix !== null) {
            $this->mockTranslator
                ->shouldReceive('translate')
                ->once()
                ->with('common.table.status.' . $prefix)
                ->andReturn('TRSLTD_STATUS');
        }

        $this->table->shouldReceive('getFieldset')
            ->andReturn('table');

        $data = [
            'id' => 7,
            'link' => 'link-text',
            'action' => $action,
        ];
        $column = [
            'action' => 'foo',
            'name' => 'link',
        ];

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            $expected,
            $response
        );
    }

    /**
     * @return (null|string)[][]
     *
     * @psalm-return list{array{action: 'A', expectPrefix: 'new', expect: '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" >link-text</button>'}, array{action: 'U', expectPrefix: 'updated', expect: '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" >link-text</button>'}, array{action: 'C', expectPrefix: 'current', expect: '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" disabled="disabled">link-text</button>'}, array{action: 'D', expectPrefix: 'removed', expect: '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" disabled="disabled">link-text</button>'}, array{action: 'ABC', expectPrefix: null, expect: '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" >link-text</button>'}}
     */
    public function provider(): array
    {
        return [
            [
                'action' => 'A',
                'expectPrefix' => 'new',
                'expect' => '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" ' .
                    '>link-text</button>',
            ],
            [
                'action' => 'U',
                'expectPrefix' => 'updated',
                'expect' => '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" ' .
                    '>link-text</button>',
            ],
            [
                'action' => 'C',
                'expectPrefix' => 'current',
                'expect' => '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" ' .
                    'disabled="disabled">link-text</button>',
            ],
            [
                'action' => 'D',
                'expectPrefix' => 'removed',
                'expect' => '(TRSLTD_STATUS) <button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" ' .
                    'disabled="disabled">link-text</button>',
            ],
            [
                'action' => 'ABC',
                'expectPrefix' => null,
                'expect' => '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link " name="table[action][foo][7]" >link-text</button>',
            ],
        ];
    }
}
