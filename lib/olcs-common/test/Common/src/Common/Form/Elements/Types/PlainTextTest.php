<?php

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\PlainText;
use Common\Form\Form;
use Common\Form\View\Helper\FormElement;
use Common\Form\View\Helper\FormPlainText;
use Mockery;
use Laminas\View\Renderer\PhpRenderer;

class PlainTextTest extends \PHPUnit\Framework\TestCase
{
    public const INITIAL_TEXT_PAYLOAD = 'TEST';

    public const UPDATED_TEXT_PAYLOAD = 'TEST 2';

    public const MALICIOUS_HTML_PAYLOAD = '<script>alert("TEST")</script>';

    /** @var PlainText */
    private $plainTextElement;

    /** @var Form */
    private $form;

    /** @var FormElement */
    private $helper;

    #[\Override]
    protected function setUp(): void
    {
        $formPlainText = new FormPlainText();

        /** @var PhpRenderer|Mockery\MockInterface $mockRenderer */
        $mockRenderer = Mockery::mock(PhpRenderer::class);
        $mockRenderer->shouldReceive('plugin')->with('form_plain_text')->andReturn($formPlainText);
        $mockRenderer->shouldReceive('translate')->andReturnUsing(
            static fn($arg) => $arg
        );
        $formPlainText->setView($mockRenderer);

        $this->helper = new FormElement();
        $this->helper->setView($mockRenderer);

        $this->plainTextElement = new PlainText('text');
        $this->plainTextElement->setAttribute('value', self::INITIAL_TEXT_PAYLOAD);

        $this->form = new Form();
        $this->form->add($this->plainTextElement);
    }

    public function testThatTextIsRendered(): void
    {
        $this->assertSame(self::INITIAL_TEXT_PAYLOAD, $this->render());
    }

    public function testThatTextIsRenderedWhenSetByAttribute(): void
    {
        $this->plainTextElement->setAttribute('value', self::UPDATED_TEXT_PAYLOAD);
        $this->assertSame(self::UPDATED_TEXT_PAYLOAD, $this->render());
    }

    public function testThatTextIsRenderedEscapedWhenSetByValue(): void
    {
        $this->plainTextElement->setValue(self::UPDATED_TEXT_PAYLOAD);
        $this->assertSame(self::UPDATED_TEXT_PAYLOAD, $this->render());
    }

    public function testThatTextCannotBeInjectedViaSetData(): void
    {
        $this->form->setData(['text' => self::MALICIOUS_HTML_PAYLOAD]);
        $this->assertSame(self::INITIAL_TEXT_PAYLOAD, $this->render());
    }

    public function testThatTextCannotBeInjectedViaPopulateValues(): void
    {
        $this->form->populateValues(['text' => self::MALICIOUS_HTML_PAYLOAD]);
        $this->assertSame(self::INITIAL_TEXT_PAYLOAD, $this->render());
    }

    private function render(): string
    {
        return $this->helper->render($this->plainTextElement);
    }
}
