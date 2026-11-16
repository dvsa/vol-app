<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\Types\Radio;
use Common\Form\View\Helper\FormRadio;
use Common\Form\View\Helper\FormRadioHorizontal;
use Common\Form\View\Helper\FormRadioVertical;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\View\Helper\Translate;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FormRadioTest extends TestCase
{
    public function testOptionsWithoutRevealsHaveUniqueNonEmptyIds(): void
    {
        $renderer = new PhpRenderer();
        $renderer->getHelperPluginManager()->setService('form_label', new \Laminas\Form\View\Helper\FormLabel());
        $helper = new FormRadio();
        $helper->setView($renderer);
        $radio = new Element\Radio('question');
        $radio->setAttribute('id', '');
        $radio->setValueOptions(['A' => 'First', 'B' => 'Second', 'C' => 'Third']);

        $document = new \DOMDocument();
        $document->loadHTML($helper->render($radio));
        $xpath = new \DOMXPath($document);
        $inputs = $xpath->query('//input[@type="radio"]');
        $this->assertCount(3, $inputs);
        $ids = [];
        foreach ($inputs as $input) {
            $id = $input->getAttribute('id');
            $ids[] = $id;
            $this->assertNotSame('', $id);
            $this->assertCount(1, $xpath->query('//label[@for="' . $id . '"]'));
            $this->assertFalse($input->hasAttribute('data-show-element'));
            $this->assertFalse($input->hasAttribute('data-aria-controls'));
        }
        $this->assertCount(3, array_unique($ids));
        $this->assertCount(0, $xpath->query('//div[contains(@class, "govuk-radios__conditional")]'));
    }

    #[DataProvider('radioLayoutsProvider')]
    public function testRendersOneGroupWithAssociatedOptionsAndReveals(
        string $layout,
        bool $volElement,
        bool $explicitIds
    ): void {
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplatePathStack([
            'script_paths' => [__DIR__ . '/../../../../../../../Common/view'],
        ]));
        $helpers = $renderer->getHelperPluginManager();
        $helpers->configure((new \Laminas\Form\ConfigProvider())->getViewHelperConfig());
        $radioHelper = new FormRadio();
        $radioHelper->setView($renderer);
        $helpers->setService('formRadio', $radioHelper);
        $helpers->setService('form_label', new \Laminas\Form\View\Helper\FormLabel());
        $translate = new Translate();
        $translate->setTranslator(new Translator());
        $helpers->setService('translate', $translate);

        $fieldset = new Fieldset('question');
        $fieldset->setLabel('The question');
        $radio = $volElement ? new Radio('yesNo') : new Element\Radio('yesNo');
        $radio->setAttribute('id', 'question[radio]');
        $radio->setAttribute('radios_wrapper_attributes', ['class' => 'custom-group', 'data-custom' => 'preserved']);
        $options = [
            'Y' => [
                'value' => 'Y',
                'label' => 'Yes',
                'hint' => 'First option hint',
                'disabled' => true,
                'attributes' => $explicitIds ? [
                    'id' => 'first-option',
                    'data-show-element' => '#old-target',
                    'data-aria-controls' => 'old-target',
                ] : [],
            ],
            'N' => [
                'value' => 'N',
                'label' => 'No',
                'attributes' => $explicitIds ? ['id' => 'second-option'] : [],
            ],
        ];
        foreach (['Y' => 'yesContent', 'N' => 'noContent'] as $key => $contentName) {
            if ($layout === 'horizontal' || $layout === 'vertical siblings') {
                $content = new Element\Text($layout === 'horizontal' ? $contentName : $key . 'Content');
                $content->setLabel('Follow-up for ' . $key);
                $fieldset->add($content);
            } else {
                $options[$key]['conditional_content'] = 'Details for <' . $key . '>';
            }
        }
        $radio->setValueOptions($options);
        $radio->setValue('N');
        $fieldset->add($radio);
        $fieldset->setOption('radio-element', 'yesNo');
        $originalOptions = $radio->getValueOptions();

        if ($layout === 'direct') {
            $markup = $radioHelper->render($radio);
        } else {
            $helper = $layout === 'horizontal' ? new FormRadioHorizontal() : new FormRadioVertical();
            $helper->setView($renderer);
            $markup = $helper->render($fieldset);
        }

        $document = new \DOMDocument();
        $document->loadHTML($markup);
        $xpath = new \DOMXPath($document);
        $groups = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " govuk-radios ")]');
        $this->assertCount(1, $groups);
        $group = $groups->item(0);
        $this->assertSame('preserved', $group->getAttribute('data-custom'));
        $this->assertStringContainsString('custom-group', $group->getAttribute('class'));
        if ($layout === 'horizontal') {
            $this->assertStringContainsString('govuk-radios--inline', $group->getAttribute('class'));
        }
        $inputs = $xpath->query('.//input[@type="radio"]', $group);
        $this->assertCount(2, $inputs);
        $ids = [];
        foreach ($inputs as $index => $input) {
            $id = $input->getAttribute('id');
            $ids[] = $id;
            $this->assertNotSame('', $id);
            $this->assertSame('yesNo', $input->getAttribute('name'));
            if ($explicitIds) {
                $this->assertSame($index === 0 ? 'first-option' : 'second-option', $id);
            }
            $this->assertCount(1, $xpath->query('//label[@for="' . $id . '"]'));
            $this->assertCount(1, $xpath->query('//input[@id="' . $id . '"]/following-sibling::label[@for="' . $id . '"]'));
            $this->assertSame('[id="' . $id . '_content"]', $input->getAttribute('data-show-element'));
            $this->assertSame($id . '_content', $input->getAttribute('data-aria-controls'));
            $reveals = $xpath->query('./div[@id="' . $id . '_content"]', $group);
            $this->assertCount(1, $reveals);
            $reveal = $reveals->item(0);
            $this->assertStringContainsString('govuk-radios__conditional', $reveal->getAttribute('class'));
            $this->assertStringNotContainsString('govuk-radios__input', $reveal->getAttribute('class'));
            if ($layout === 'horizontal') {
                $this->assertCount(2, $xpath->query('preceding-sibling::div[contains(@class, "govuk-radios__item")]', $reveal));
            } else {
                $this->assertSame($input->parentNode, $xpath->query('preceding-sibling::*[1]', $reveal)->item(0));
            }
            $this->assertSame($index === 0, $input->hasAttribute('disabled'));
            $this->assertSame($index === 1, $input->hasAttribute('checked'));
            if ($layout === 'direct' || $layout === 'vertical') {
                $this->assertSame('Details for <' . $input->getAttribute('value') . '>', trim($reveal->textContent));
            } else {
                $this->assertCount(1, $xpath->query('.//input[@type="text"]', $reveal));
            }
        }
        $this->assertCount(2, array_unique($ids));
        $this->assertSame($ids[0] . '-hint', $inputs->item(0)->getAttribute('aria-describedby'));
        $this->assertSame($originalOptions, $radio->getValueOptions());
    }

    public static function radioLayoutsProvider(): \Iterator
    {
        foreach (['direct', 'vertical', 'vertical siblings', 'horizontal'] as $layout) {
            foreach ([false, true] as $volElement) {
                foreach ([false, true] as $explicitIds) {
                    yield $layout . ($volElement ? ' VOL' : ' Laminas') . ($explicitIds ? ' explicit IDs' : ' generated IDs')
                        => [$layout, $volElement, $explicitIds];
                }
            }
        }
    }
}
