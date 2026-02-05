<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Text;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Text;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TextTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TextTest extends MockeryTestCase
{
    private $hintTranslateableTextRepresentation;

    private $labelTranslateableTextRepresentation;

    private $value;

    private $hintTranslateableText;

    private $labelTranslateableText;

    public function setUp(): void
    {
        $this->hintTranslateableTextRepresentation = ['hintTranslateableTextRepresentation'];

        $this->labelTranslateableTextRepresentation = ['labelTranslateableTextRepresentation'];

        $this->value = '123';

        $this->hintTranslateableText = m::mock(TranslateableText::class);
        $this->hintTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($this->hintTranslateableTextRepresentation);

        $this->labelTranslateableText = m::mock(TranslateableText::class);
        $this->labelTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($this->labelTranslateableTextRepresentation);
    }

    public function testGetRepresentation(): void
    {
        $text = new Text(
            $this->value,
            $this->labelTranslateableText,
            $this->hintTranslateableText
        );

        $expectedRepresentation = [
            'label' => $this->labelTranslateableTextRepresentation,
            'hint' => $this->hintTranslateableTextRepresentation,
            'value' => $this->value
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $text->getRepresentation()
        );
    }

    public function testGetRepresentationWithLabelOnly(): void
    {
        $text = new Text(
            $this->value,
            $this->labelTranslateableText,
            null
        );

        $expectedRepresentation = [
            'label' => $this->labelTranslateableTextRepresentation,
            'value' => $this->value
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $text->getRepresentation()
        );
    }

    public function testGetRepresentationWithHintOnly(): void
    {
        $text = new Text(
            $this->value,
            null,
            $this->hintTranslateableText
        );

        $expectedRepresentation = [
            'hint' => $this->hintTranslateableTextRepresentation,
            'value' => $this->value
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $text->getRepresentation()
        );
    }

    public function testGetHint(): void
    {
        $text = new Text(
            $this->value,
            $this->labelTranslateableText,
            $this->hintTranslateableText
        );

        $this->assertSame(
            $this->hintTranslateableText,
            $text->getHint()
        );
    }
}
