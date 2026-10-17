<?php

declare(strict_types=1);

namespace OlcsTest\Form\View\Helper;

use Mockery as m;
use Olcs\Form\Element\EditorJs as EditorJsElement;
use Olcs\Form\View\Helper\EditorJs;
use Olcs\Service\EditorJs\HtmlConverter;
use PHPUnit\Framework\TestCase;

final class EditorJsTest extends TestCase
{
    public function testRendersConfigurablePlaceholderAndToolsProfile(): void
    {
        $element = new EditorJsElement(m::mock(HtmlConverter::class), 'content');
        $element->setAttributes([
            'id' => 'longTextContent',
            'required' => true,
            'data-placeholder' => 'Enter Long Text content...',
            'data-tools-profile' => 'govuk-long-text',
        ]);
        $element->setValue('{"blocks":[]}');

        $markup = (new EditorJs())($element);

        $this->assertStringContainsString('data-placeholder="Enter Long Text content..."', $markup);
        $this->assertStringContainsString('data-tools-profile="govuk-long-text"', $markup);
        $this->assertStringContainsString('data-required="required"', $markup);
        $this->assertStringContainsString('id="longTextContent"', $markup);
    }

    public function testDefaultsPreserveExistingSubmissionEditorBehaviour(): void
    {
        $element = new EditorJsElement(m::mock(HtmlConverter::class), 'comment');
        $element->setValue('{"blocks":[]}');

        $markup = (new EditorJs())($element);

        $this->assertStringContainsString('data-placeholder="Enter your submission comment..."', $markup);
        $this->assertStringContainsString('data-tools-profile="default"', $markup);
    }
}
