<?php

/**
 * Selector Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Type;

use Laminas\I18n\Translator\Translator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\Selector;

final class SelectorTest extends MockeryTestCase
{
    protected $sut;

    protected $table;

    #[\Override]
    protected function setUp(): void
    {
        $this->table = m::mock(\Common\Service\Table\TableBuilder::class);
        $this->table->shouldIgnoreMissing();

        $this->sut = new Selector($this->table);
    }

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
            '<input type="radio" name="table[id]" value="7" id="table[id][7]" />',
            $response
        );
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
            '<input type="radio" name="table[id]" value="7" disabled="disabled" id="table[id][7]" />',
            $response
        );
    }

    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
    public function testRenderWithoutFieldet(): void
    {
        $fieldset = null;
        $data = [
            'id' => 7
        ];
        $column = [];

        $this->table->shouldReceive('getFieldset')
            ->andReturn($fieldset);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<input type="radio" name="id" value="7" id="[id][7]" />',
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
            '<input type="radio" name="id" value="7" data-action="blap" id="[id][7]" />',
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
            '<input type="radio" name="id" value="7" data-action="blap" id="[id][7]" />',
            $response
        );
    }

    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
    public function testRenderWithDataIdxSet(): void
    {
        $fieldset = null;
        $data = [
            'fooBarId' => 7,
        ];
        $column = [
            'idIndex' => 'fooBarId'
        ];

        $this->table->shouldReceive('getFieldset')
            ->andReturn($fieldset);

        $response = $this->sut->render($data, $column);

        $this->assertEquals(
            '<input type="radio" name="id" value="7" id="[id][7]" />',
            $response
        );
    }

    /**
     * Test render with disabled callback
     */
    #[\PHPUnit\Framework\Attributes\Group('checkboxTest')]
    #[\PHPUnit\Framework\Attributes\DataProvider('disabledCallbackProvider')]
    public function testRenderWithDisabledCallback($row, $expected): void
    {
        $fieldset = 'table';
        $column = [
            'disabled-callback' => static fn($row) => $row['isExpiredForLicence']
        ];

        $this->table
            ->shouldReceive('getFieldset')
            ->andReturn($fieldset)
            ->once();

        $this->assertEquals($expected, $this->sut->render($row, $column));
    }

    /**
     * Test render with a single aria attribute defined as a string literal.
     */
    #[\PHPUnit\Framework\Attributes\Group('tableSelectorAriaSupport')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderWithAriaAttributeLiteralStringDefinitionSingle(): void
    {
        $column = [
            'aria-attributes' => [
                'label' => 'Some Aria Attribute'
            ]
        ];

        $this->assertStringContainsString(
            ' aria-label="Some Aria Attribute" ',
            (string) $this->sut->render(['id' => 7], $column)
        );
    }

    /**
     * Test render with a multiple aria attribute defined as string literals.
     */
    #[\PHPUnit\Framework\Attributes\Depends('renderWithAriaAttributeLiteralStringDefinitionSingle')]
    #[\PHPUnit\Framework\Attributes\Group('tableSelectorAriaSupport')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderWithAriaAttributeLiteralStringDefinitionMultiple(): void
    {
        $column = [
            'aria-attributes' => [
                'label' => 'Some Aria Attribute',
                'checked' => 'false',
                'test' => 'testing'
            ]
        ];

        $renderedResult = $this->sut->render(['id' => 7], $column);
        $this->assertStringContainsString(' aria-label="Some Aria Attribute" ', (string) $renderedResult);
        $this->assertStringContainsString(' aria-checked="false" ', (string) $renderedResult);
        $this->assertStringContainsString(' aria-test="testing" ', (string) $renderedResult);
    }

    /**
     * Test render with aria attribute value being a callback.
     */
    #[\PHPUnit\Framework\Attributes\Group('tableSelectorAriaSupport')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderWithAriaAttributeAsCallback(): void
    {
        $column = [
            'aria-attributes' => [
                'label' => static fn(): string => 'Test translated string'
            ]
        ];

        $this->assertStringContainsString(
            ' aria-label="Test translated string" ',
            (string) $this->sut->render(['id' => 7], $column)
        );
    }

    /**
     * Test render with aria attribute being a callback, translator is passed to callable.
     */
    #[\PHPUnit\Framework\Attributes\Depends('renderWithAriaAttributeAsCallback')]
    #[\PHPUnit\Framework\Attributes\Group('tableSelectorAriaSupport')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderWithAriaAttributeAsCallbackTranslatorIsPassedToCallable(): void
    {
        $translatorMock = m::mock(Translator::class);
        $this->table
            ->shouldReceive('getTranslator')
            ->andReturn($translatorMock);

        $column = [
            'aria-attributes' => [
                'label' => function ($data, $translator) use ($translatorMock): string {
                    $this->assertSame($translatorMock, $translator);
                    return 'Test string';
                }
            ]
        ];

        $this->sut->render(['id' => 7], $column);
    }

    /**
     * Test render with aria attribute being a callback, data is passed to callable.
     */
    #[\PHPUnit\Framework\Attributes\Depends('renderWithAriaAttributeAsCallback')]
    #[\PHPUnit\Framework\Attributes\Group('tableSelectorAriaSupport')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderWithAriaAttributeAsCallbackDataIsPassedToCallable(): void
    {
        $expectedData = ['id' => 7];

        $column = [
            'aria-attributes' => [
                'label' => function ($data) use ($expectedData): string {
                    $this->assertSame($expectedData, $data);
                    return 'Test string';
                }
            ]
        ];

        $this->sut->render($expectedData, $column);
    }

    /**
     * Test render with aria attribute contain HTML is escaped
     */
    #[\PHPUnit\Framework\Attributes\Group('tableSelectorAriaSupport')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderWithAriaAttributeHtmlIsEscaped(): void
    {
        $column = [
            'aria-attributes' => [
                'label' => 'Some <html>'
            ]
        ];

        $this->assertStringNotContainsString(
            '<html>',
            (string) $this->sut->render(['id' => 7], $column)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(array<int> | string)>>
     *
     * @psalm-return list{list{array{isExpiredForLicence: 1, id: 7}, '<input type="radio" name="table[id]" value="7" disabled="disabled" id="table[id][7]" />'}, list{array{isExpiredForLicence: 0, id: 7}, '<input type="radio" name="table[id]" value="7" id="table[id][7]" />'}}
     */
    public static function disabledCallbackProvider(): \Iterator
    {
        yield [
            ['isExpiredForLicence' => 1, 'id' => 7],
            '<input type="radio" name="table[id]" value="7" disabled="disabled" id="table[id][7]" />'
        ];
        yield [
            ['isExpiredForLicence' => 0, 'id' => 7],
            '<input type="radio" name="table[id]" value="7" id="table[id][7]" />'
        ];
    }
}
