<?php

/**
 * Selector Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Type;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\CustomSelector;

/**
 * Custom Selector Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CustomSelectorTest extends MockeryTestCase
{
    protected $sut;

    protected $table;

    #[\Override]
    protected function setUp(): void
    {
        $this->table = m::mock(\Common\Service\Table\TableBuilder::class);

        $this->sut = new CustomSelector($this->table);
    }

    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
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

    public function testRenderWithAccessibleLabel(): void
    {
        $this->table->shouldReceive('getFieldset')->andReturn(null);
        $output = $this->sut->render(
            ['userId' => 7, 'loginId' => 'A & B'],
            [
                'data-field' => 'userId',
                'selectionId' => 'unique-control',
                'label-callback' => static fn($row) => 'Select user ' . $row['loginId'],
            ]
        );
        self::assertStringContainsString('name="id" value="7" id="unique-control"', $output);
        self::assertStringContainsString('for="unique-control">Select user A &amp; B</label>', $output);
    }

    /**
     * Test render with disabled attribute
     */
    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
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

    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
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

    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
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
     */
    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
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
