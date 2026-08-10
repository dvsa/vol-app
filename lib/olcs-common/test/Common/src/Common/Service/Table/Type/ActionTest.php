<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Type;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\Type\Action;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Service\Table\Type\Action::class)]
final class ActionTest extends MockeryTestCase
{
    public const int ID = 9999;

    /** @var Action */
    protected $sut;

    /** @var  m\MockInterface */
    protected $table;

    #[\Override]
    protected function setUp(): void
    {
        $this->table = m::mock(TableBuilder::class);
        $this->sut = new Action($this->table);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestRender')]
    public function testRender($isFieldset, $data, $column, $content, $expect, $isInternalReadOnly): void
    {
        $this->table
            ->expects('isInternalReadOnly')
            ->withNoArgs()
            ->andReturn($isInternalReadOnly);
        $this->table->expects('getFieldset')
            ->withNoArgs()
            ->andReturn($isFieldset ? 'unit_Fieldset' : null);

        $data['id'] = self::ID;

        // value_format is a template with row data substituted in, so it goes through the escaping
        // variant — and only then. Declared inside the guard so the arguments can be the real ones
        // rather than a wildcard, and so the columns without a value_format assert that it is not
        // called at all. $data is stamped with the id first, because Mockery captures the expected
        // arguments by value when the expectation is declared.
        if (isset($column['value_format'])) {
            $this->table->expects('replaceContentEscapingValues')
                ->with($column['value_format'], $data)
                ->andReturn('unit_ValueFormat');
        }

        $this->assertEquals($expect, $this->sut->render($data, $column, $content));
    }

    public static function dpTestRender(): \Iterator
    {
        yield [
            'isFieldset' => true,
            'data' => [],
            'column' => [
                'action' => 'unit_Action',
                'class' => 'unit_Class',
                'action-attributes' => [
                    'attrA',
                    'attrB',
                ],
            ],
            'content' => 'unit_Content',
            'expect' =>
                '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit"' .
                ' class="action-button-link unit_Class" name="unit_Fieldset[action][unit_Action][' . self::ID . ']"' .
                ' attrA attrB>unit_Content</button>',
            "isInternalReadOnly" => false,
        ];
        yield [
            'isFieldset' => false,
            'data' => [],
            'column' => [
                'action' => 'unit_Action',
                'text' => 'unit_Text',
            ],
            'content' => null,
            'expect' =>
                '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit"' .
                ' class="action-button-link " name="action[unit_Action][' . self::ID . ']"' .
                ' >unit_Text</button>',
            "isInternalReadOnly" => false,
        ];
        yield [
            'isFieldset' => false,
            'data' => [
                'field' => 'unit_FldVal',
            ],
            'column' => [
                'action' => 'unit_Action',
                'name' => 'field',
            ],
            'content' => null,
            'expect' =>
                '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit"' .
                ' class="action-button-link " name="action[unit_Action][' . self::ID . ']"' .
                ' >unit_FldVal</button>',
            "isInternalReadOnly" => false,
        ];
        // A column naming a to-many field. Escape::html() rejects an array outright, so without a
        // guard this is a TypeError rather than the label "Array" sprintf() used to produce.
        yield [
            'isFieldset' => false,
            'data' => [
                'field' => [],
            ],
            'column' => [
                'action' => 'unit_Action',
                'name' => 'field',
            ],
            'content' => null,
            'expect' =>
                '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit"' .
                ' class="action-button-link " name="action[unit_Action][' . self::ID . ']"' .
                ' ></button>',
            "isInternalReadOnly" => false,
        ];
        yield [
            'isFieldset' => false,
            'data' => [
                'field' => 'unit_FldVal',
            ],
            'column' => [
                'action' => 'unit_Action',
                'value_format' => 'unit_ValueFormat',
            ],
            'content' => null,
            'expect' =>
                '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit"' .
                ' class="action-button-link " name="action[unit_Action][' . self::ID . ']"' .
                ' >unit_ValueFormat</button>',
            "isInternalReadOnly" => false,
        ];
        yield [
            'isFieldset' => false,
            'data' => [
                'field' => 'unit_FldVal',
            ],
            'column' => [
                'action' => 'unit_Action',
                'value_format' => 'unit_ValueFormat',
                'keepForReadOnly' => true,
            ],
            'content' => null,
            'expect' => 'unit_ValueFormat',
            "isInternalReadOnly" => true,
        ];
    }
}
