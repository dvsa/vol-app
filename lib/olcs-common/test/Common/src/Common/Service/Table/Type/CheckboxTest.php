<?php

/**
 * Checkbox Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

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
class CheckboxTest extends MockeryTestCase
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
     *
     * @group checkboxTest
     */
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
            '<input type="checkbox" name="table[id][]" value="7" disabled="disabled" id="table[id][7]" />',
            $response
        );
    }

    /**
     * Test render
     *
     * @group checkboxTest
     */
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
            '<input type="checkbox" name="table[id][]" value="7" id="table[id][7]" />',
            $response
        );
    }

    /**
     * Test render
     *
     * @group checkboxTest
     */
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
            '<input type="checkbox" name="table[id][]" value="7" data-action="foo" id="table[id][7]" />',
            $response
        );
    }
}
