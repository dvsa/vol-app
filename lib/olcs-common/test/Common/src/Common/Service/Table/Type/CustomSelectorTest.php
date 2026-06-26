<?php

/**
 * Selector Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Type;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\CustomSelector;

/**
 * Custom Selector Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CustomSelectorTest extends MockeryTestCase
{
    protected $sut;

    protected $table;

    #[\Override]
    protected function setUp(): void
    {
        $this->table = m::mock(\Common\Service\Table\TableBuilder::class);

        $this->sut = new CustomSelector($this->table);
    }

    /**
     * @group checkboxTest
     */
    public function testRender(): void
    {
        $fieldset = 'table';
        $data = [
            'id' => 7,
            'someDataKey' => 'someDataValue'
        ];
        $column = ['name' => 'someName', 'data-field' => 'someDataKey'];

        $this->table->shouldReceive('getFieldset')
            ->andReturn($fieldset);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<input type="radio" name="table[someName]" value="someDataValue"  />',
            $response
        );
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
            'id' => 7,
            'someDataKey' => 'someDataValue'
        ];
        $column = [
            'name' => 'someName',
            'disableIfRowIsDisabled' => true,
            'data-field' => 'someDataKey'
        ];

        $this->table
            ->shouldReceive('getFieldset')
            ->andReturn($fieldset)
            ->shouldReceive('isRowDisabled')
            ->with($data)
            ->andReturn(true);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<input type="radio" name="table[someName]" value="someDataValue" disabled="disabled" />',
            $response
        );
    }

    /**
     * @group checkboxTest
     */
    public function testRenderWithoutFieldet(): void
    {
        $fieldset = null;
        $data = [
            'id' => 7,
            'name' => 'someName',
            'someDataKey' => 'someDataValue'
        ];
        $column = [
            'name' => 'someName',
            'data-field' => 'someDataKey'
        ];

        $this->table->shouldReceive('getFieldset')
            ->andReturn($fieldset);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<input type="radio" name="someName" value="someDataValue"  />',
            $response
        );
    }

    /**
     * @group checkboxTest
     */
    public function testRenderWithDataAttributes(): void
    {
        $fieldset = null;
        $data = [
            'id' => 7,
            'action' => 'blap'
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
            '<input type="radio" name="id" value="7" data-action="blap" />',
            $response
        );
    }

    /**
     * Test render with data attribute when column is an array
     *
     * @group checkboxTest
     */
    public function testRenderWithDataAttributesArray(): void
    {
        $fieldset = null;
        $data = [
            'id' => 7,
            'action' => ['id' => 'blap']
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
            '<input type="radio" name="id" value="7" data-action="blap" />',
            $response
        );
    }
}
