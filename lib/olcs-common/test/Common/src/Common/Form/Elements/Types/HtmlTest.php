<?php

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\Html;
use Common\Form\Form;
use Common\Form\View\Helper\FormElement;
use Mockery;
use Laminas\View\Renderer\PhpRenderer;

class HtmlTest extends \PHPUnit\Framework\TestCase
{
    public const INITIAL_HTML_PAYLOAD = '<em>TEST</em>';

    public const UPDATED_HTML_PAYLOAD = '<em>TEST 2</em>';

    public const MALICIOUS_HTML_PAYLOAD = '<script>alert("TEST")</script>';

    /** @var Html */
    private $htmlElement;

    /** @var Form */
    private $form;

    /** @var FormElement */
    private $helper;

    #[\Override]
    protected function setUp(): void
    {
        $this->helper = new FormElement();
        /** @var PhpRenderer|Mockery\MockInterface $mockRenderer */
        $mockRenderer = Mockery::mock(PhpRenderer::class);
        $this->helper->setView($mockRenderer);

        $this->htmlElement = new Html('html');
        $this->htmlElement->setAttribute('value', self::INITIAL_HTML_PAYLOAD);

        $this->form = new Form();
        $this->form->add($this->htmlElement);
    }

    public function testThatHtmlIsRendered(): void
    {
        $this->assertSame(self::INITIAL_HTML_PAYLOAD, $this->render());
    }

    public function testThatHtmlIsRenderedWhenSetByAttribute(): void
    {
        $this->htmlElement->setAttribute('value', self::UPDATED_HTML_PAYLOAD);
        $this->assertSame(self::UPDATED_HTML_PAYLOAD, $this->render());
    }

    public function testThatHtmlIsRenderedEscapedWhenSetByValue(): void
    {
        $this->htmlElement->setValue(self::UPDATED_HTML_PAYLOAD);
        $this->assertSame(self::UPDATED_HTML_PAYLOAD, $this->render());
    }

    public function testThatHtmlCannotBeInjectedViaSetData(): void
    {
        $this->form->setData(['html' => self::MALICIOUS_HTML_PAYLOAD]);
        $this->assertSame(self::INITIAL_HTML_PAYLOAD, $this->render());
    }

    public function testThatHtmlCannotBeInjectedViaPopulateValues(): void
    {
        $this->form->populateValues(['html' => self::MALICIOUS_HTML_PAYLOAD]);
        $this->assertSame(self::INITIAL_HTML_PAYLOAD, $this->render());
    }

    private function render(): string
    {
        return $this->helper->render($this->htmlElement);
    }
}
