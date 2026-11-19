<?php

/**
 * Checkbox Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Type;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\Checkbox;

/**
 * Checkbox Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CheckboxTest extends MockeryTestCase
{
    protected $sut;

    protected $table;

    /**
     * Set up
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->table = m::mock(\Common\Service\Table\TableBuilder::class);
        $this->sut = new Checkbox($this->table);
    }

    /**
     * Test render with disabled attribute
     */
    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
    public function testRenderWithDisabledAttribute(): void
    {
        $fieldset = 'table';
        $data = [
            'id' => 7
        ];
        $column = [
            'disableIfRowIsDisabled' => true
        ];

        $this->table
            ->shouldReceive('getFieldset')
            ->andReturn($fieldset)
            ->shouldReceive('isRowDisabled')
            ->with($data)
            ->andReturn(true);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<div class="govuk-checkboxes govuk-checkboxes--small" data-module="govuk-checkboxes">'
            . '<div class="govuk-checkboxes__item"><input type="checkbox" name="table[id][]" value="7" '
            . 'class="govuk-checkboxes__input" disabled="disabled" id="table[id][7]" />'
            . '<label class="govuk-label govuk-checkboxes__label" for="table&#x5B;id&#x5D;&#x5B;7&#x5D;">'
            . '<span class="govuk-visually-hidden">Select row</span></label></div></div>',
            $response
        );
    }

    /**
     * Test render
     */
    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
    public function testRender(): void
    {
        $fieldset = 'table';
        $data = [
            'id' => 7
        ];
        $column = [];

        $this->table->shouldReceive('getFieldset')
            ->andReturn($fieldset);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<div class="govuk-checkboxes govuk-checkboxes--small" data-module="govuk-checkboxes">'
            . '<div class="govuk-checkboxes__item"><input type="checkbox" name="table[id][]" value="7" '
            . 'class="govuk-checkboxes__input" id="table[id][7]" />'
            . '<label class="govuk-label govuk-checkboxes__label" for="table&#x5B;id&#x5D;&#x5B;7&#x5D;">'
            . '<span class="govuk-visually-hidden">Select row</span></label></div></div>',
            $response
        );
    }

    /**
     * Test render
     */
    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
    public function testRenderWithAttributes(): void
    {
        $fieldset = 'table';
        $data = [
            'id' => 7,
            'action' => 'foo'
        ];
        $column = [
            'data-attributes' => [
                'action'
            ]
        ];

        $this->table->shouldReceive('getFieldset')
            ->andReturn($fieldset);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<div class="govuk-checkboxes govuk-checkboxes--small" data-module="govuk-checkboxes">'
            . '<div class="govuk-checkboxes__item"><input type="checkbox" name="table[id][]" value="7" '
            . 'class="govuk-checkboxes__input" data-action="foo" id="table[id][7]" />'
            . '<label class="govuk-label govuk-checkboxes__label" for="table&#x5B;id&#x5D;&#x5B;7&#x5D;">'
            . '<span class="govuk-visually-hidden">Select row</span></label></div></div>',
            $response
        );
    }

    public function testRenderWithRowSpecificLabel(): void
    {
        $this->table->shouldReceive('getFieldset')->andReturn(null);

        $response = $this->sut->render(
            ['id' => 7, 'printerName' => 'Office & <Warehouse>'],
            ['label-callback' => static fn($row) => 'Select printer ' . $row['printerName']]
        );

        $this->assertStringContainsString('name="id[]" value="7"', $response);
        $this->assertStringContainsString('id="[id][7]"', $response);
        $this->assertStringContainsString('for="&#x5B;id&#x5D;&#x5B;7&#x5D;"', $response);
        $this->assertStringContainsString(
            '<span class="govuk-visually-hidden">Select printer Office &amp; &lt;Warehouse&gt;</span>',
            $response
        );
    }

    public function testLabelTargetsIdContainingSpecialCharacters(): void
    {
        $this->table->shouldReceive('getFieldset')->andReturn('table');

        $response = $this->sut->render(['id' => 'A&B"'], []);
        $document = new \DOMDocument();
        $document->loadHTML($response);
        $input = $document->getElementsByTagName('input')->item(0);
        $label = $document->getElementsByTagName('label')->item(0);

        self::assertSame('table[id][A&B"]', $input->getAttribute('id'));
        self::assertSame($input->getAttribute('id'), $label->getAttribute('for'));
    }
}
