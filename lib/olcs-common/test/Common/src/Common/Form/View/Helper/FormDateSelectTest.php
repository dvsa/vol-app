<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormDateSelect;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Element\Text;
use Laminas\Form\View\Helper\FormInput;
use Laminas\I18n\Translator\Translator;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

final class FormDateSelectTest extends MockeryTestCase
{
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $formInput = m::mock(FormInput::class)->makePartial();

        $translator = m::mock(Translator::class);
        $translator->shouldReceive('translate')->andReturnUsing(
            static fn($key) => 'translated-' . $key
        );

        $container = m::mock(ContainerInterface::class);

        $helpers = new HelperPluginManager($container);
        $helpers->setService('forminput', $formInput);

        /** @var PhpRenderer $view */
        $view = m::mock(PhpRenderer::class)->makePartial();
        $view->setHelperPluginManager($helpers);

        $this->sut = new FormDateSelect();
        $this->sut->setView($view);
        $this->sut->setTranslator($translator);
    }

    public function testRender(): void
    {
        $element = new DateSelect('date');
        $element->setAttribute('id', 'dob');
        $element->setValue(['day' => '5', 'month' => '6', 'year' => '1990']);
        $markup = $this->sut->render($element);

        $document = new \DOMDocument();
        $document->loadHTML($markup);
        $xpath = new \DOMXPath($document);

        $this->assertSame(1, $xpath->query('//div[@class="govuk-date-input"]')->length);
        foreach (['day' => [2, '5'], 'month' => [2, '6'], 'year' => [4, '1990']] as $part => [$width, $value]) {
            $item = '//div[@class="govuk-date-input"]/div[@class="govuk-date-input__item"]';
            $input = $item . '/div[@class="govuk-form-group"]/input[@id="dob_' . $part . '"]';
            $this->assertSame(1, $xpath->query($input)->length);
            $this->assertSame('text', $xpath->evaluate('string(' . $input . '/@type)'));
            $this->assertSame('numeric', $xpath->evaluate('string(' . $input . '/@inputmode)'));
            $this->assertSame($part, $xpath->evaluate('string(' . $input . '/@name)'));
            $this->assertSame($value, $xpath->evaluate('string(' . $input . '/@value)'));
            $this->assertSame((string) $width, $xpath->evaluate('string(' . $input . '/@maxlength)'));
            $this->assertSame(
                'govuk-input govuk-date-input__input govuk-input--width-' . $width,
                $xpath->evaluate('string(' . $input . '/@class)')
            );
            $this->assertSame(
                1,
                $xpath->query($item . '/div/label[@class="govuk-label govuk-date-input__label"][@for="dob_' . $part . '"]')->length
            );
        }
    }

    public function testRenderWrongElement(): void
    {
        $this->expectException(\Laminas\Form\Exception\InvalidArgumentException::class);

        $element = new Text('date');

        $this->sut->render($element);
    }

    public function testRenderElementWithNoName(): void
    {
        $this->expectException(\Laminas\Form\Exception\DomainException::class);

        $element = new DateSelect(null);

        $this->sut->render($element);
    }
}
