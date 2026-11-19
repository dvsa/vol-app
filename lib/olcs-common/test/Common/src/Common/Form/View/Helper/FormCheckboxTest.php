<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\InputFilters\Checkbox;
use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Elements\Types\CheckboxAdvanced;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\View\Helper\FormCheckbox;
use Common\Form\View\Helper\FormCollection;
use Common\Form\View\Helper\FormElement;
use Common\Form\View\Helper\FormElementErrors;
use Common\Form\View\Helper\FormErrors;
use Common\Form\View\Helper\FormRow;
use Laminas\Form\Element\Checkbox as LaminasCheckbox;
use Laminas\Form\Element\MultiCheckbox;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\View\Helper\FormCheckbox as LaminasFormCheckbox;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FormCheckboxTest extends TestCase
{
    private function renderer(): PhpRenderer
    {
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplatePathStack([
            'script_paths' => [__DIR__ . '/../../../../../../../Common/view'],
        ]));
        $config = require __DIR__ . '/../../../../../../../Common/config/module.config.php';
        $helpers = $renderer->getHelperPluginManager();
        $helpers->configure((new \Laminas\Form\ConfigProvider())->getViewHelperConfig());
        $helpers->configure([
            'invokables' => $config['view_helpers']['invokables'],
            'aliases' => $config['view_helpers']['aliases'],
            'factories' => [
                FormCheckbox::class => $config['view_helpers']['factories'][FormCheckbox::class],
            ],
        ]);
        $translator = new Translator();
        $translate = new Translate();
        $translate->setTranslator($translator);
        $helpers->setService('translate', $translate);
        foreach (
            [
                FormElement::class => new FormElement(),
                FormElementErrors::class => new FormElementErrors(new FormElementMessageFormatter($translator), $translator),
                FormRow::class => new FormRow(),
                FormCollection::class => new FormCollection(),
                \Common\View\Helper\EscapeHtml::class => new \Laminas\View\Helper\EscapeHtml(),
            ] as $name => $helper
        ) {
            $helper->setView($renderer);
            $helpers->setService($name, $helper);
        }

        return $renderer;
    }

    public static function checkboxRenderers(): array
    {
        $cases = [];
        foreach ([LaminasCheckbox::class, Checkbox::class, SingleCheckbox::class, CheckboxAdvanced::class] as $class) {
            foreach (['formCheckbox', 'FormCheckbox', 'formcheckbox', 'form_checkbox', 'formElement', 'formRow'] as $entry) {
                $cases[$class . ' via ' . $entry] = [$class, $entry];
            }
        }
        $cases['advanced collection helper'] = [CheckboxAdvanced::class, 'formCollection'];

        return $cases;
    }

    #[DataProvider('checkboxRenderers')]
    public function testStandardCheckboxRendering(string $class, string $entry): void
    {
        $element = new $class('lgv-declaration-confirmation');
        $element->setAttribute('id', 'lgv-declaration-confirmation');
        $element->setLabel('Confirm the declaration');
        $element->setLabelAttributes([
            'class' => 'govuk-label govuk-checkboxes__label',
        ]);
        $renderer = $this->renderer();
        $markup = '<fieldset><legend>LGV declaration</legend>' .
            $renderer->plugin($entry)($element) . '</fieldset>';
        $xpath = $this->xpath($markup);

        self::assertSame(1, $xpath->query('//fieldset')->length);
        self::assertSame(1, $xpath->query('//div[@class="govuk-form-group"]')->length);
        self::assertSame(
            1,
            $xpath->query(
                '//input[@id="lgv-declaration-confirmation" and @class="govuk-checkboxes__input"]' .
                '/following-sibling::*[1][self::label and @for="lgv-declaration-confirmation"]'
            )->length
        );
        self::assertSame('govuk-label govuk-checkboxes__label', $xpath->evaluate('string(//label/@class)'));
        self::assertSame(
            $element instanceof SingleCheckbox ? 1 : 0,
            $xpath->query('//div[contains(@class, "govuk-checkboxes--small")]')->length
        );
    }

    #[DataProvider('checkboxRenderers')]
    public function testErrorAppearsOnceOutsideCheckboxItem(string $class, string $entry): void
    {
        $element = new $class('confirmation');
        $element->setLabel('Confirm the declaration');
        $element->setMessages(['This confirmation is required']);
        $xpath = $this->xpath($this->renderer()->plugin($entry)($element));

        $hasErrors = in_array($entry, ['formElement', 'formRow', 'formCollection'], true);
        self::assertSame(
            in_array($entry, ['formRow', 'formCollection'], true) ? 1 : 0,
            $xpath->query('//div[@class="validation-wrapper"]')->length
        );
        self::assertSame(0, $xpath->query('//*[contains(@class, "govuk-form-group--error")]')->length);
        self::assertSame($hasErrors ? 1 : 0, $xpath->query('//p[@id="confirmation-error"]')->length);
        self::assertSame(
            $hasErrors ? 1 : 0,
            $xpath->query('//p[@id="confirmation-error"]/following-sibling::div[@class="govuk-form-group"]')->length
        );
        self::assertSame(0, $xpath->query('//div[@class="govuk-checkboxes__item"]/p')->length);
        self::assertSame(
            $hasErrors ? 1 : 0,
            $xpath->query('//input[@type="checkbox" and @aria-describedby="confirmation-error"]')->length
        );
    }

    public function testPreservesValuesAndDisabledState(): void
    {
        $element = new LaminasCheckbox('confirmation');
        $element->setLabel('Confirm');
        $element->setCheckedValue('Y');
        $element->setUncheckedValue('N');
        $element->setChecked(true);
        $element->setAttribute('disabled', true);
        $xpath = $this->xpath($this->renderer()->plugin('formCheckbox')($element));

        self::assertSame(1, $xpath->query('//input[@type="checkbox" and @value="Y" and @checked and @disabled]')->length);
        self::assertSame(1, $xpath->query('//input[@type="hidden" and @value="N" and @disabled]')->length);
    }

    public function testHelperCanBeRetrievedWithoutAnElement(): void
    {
        self::assertInstanceOf(FormCheckbox::class, $this->renderer()->formCheckbox());
    }

    public function testInputRenderingPreservesHelperConfiguration(): void
    {
        $element = new LaminasCheckbox('confirmation');
        $element->setAttribute('custom-attribute', 'Custom value');
        $element->setAttribute('custom-prefix-attribute', 'Other value');
        $element->setChecked(true);

        $helper = new FormCheckbox(new LaminasFormCheckbox());
        $laminasHelper = new LaminasFormCheckbox();
        foreach ([$helper, $laminasHelper] as $inputHelper) {
            $inputHelper->setEncoding('ISO-8859-1');
            $inputHelper->addValidAttribute('custom-attribute');
            $inputHelper->addValidAttributePrefix('custom-prefix-');
        }

        $expected = $laminasHelper->render($element);
        self::assertStringContainsString('custom-attribute="Custom&#x20;value"', $expected);
        self::assertStringContainsString('custom-prefix-attribute="Other&#x20;value"', $expected);
        self::assertSame($expected, $helper->renderInput($element));
        self::assertSame($expected, $helper->renderInput($element));
    }

    public function testInputRenderingRejectsNonCheckboxElements(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FormCheckbox(new LaminasFormCheckbox()))->renderInput(new \Laminas\Form\Element('confirmation'));
    }

    public function testInputRenderingUsesInjectedHelper(): void
    {
        $inputHelper = new LaminasFormCheckbox();
        $inputHelper->addValidAttribute('injected-attribute');
        $helper = new FormCheckbox($inputHelper);
        $element = new LaminasCheckbox('confirmation');
        $element->setAttribute('injected-attribute', 'present');

        self::assertStringContainsString('injected-attribute="present"', $helper->renderInput($element));
    }

    public function testInputRenderingUsesConfiguredTranslator(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('translate')
            ->with('Original', 'checkbox-domain')
            ->willReturn('Translated');
        $helper = new FormCheckbox(new LaminasFormCheckbox());
        $helper->setTranslator($translator, 'checkbox-domain');
        $helper->addValidAttribute('custom-attribute');
        $helper->addTranslatableAttribute('custom-attribute');
        $element = new LaminasCheckbox('confirmation');
        $element->setAttribute('custom-attribute', 'Original');

        self::assertStringContainsString('custom-attribute="Translated"', $helper->renderInput($element));
    }

    public function testAdvancedCheckboxLinksConditionalContentAndRestoresCheckedState(): void
    {
        $element = new CheckboxAdvanced('confirmation');
        $element->setLabel('Confirm');
        $element->setOption('content', 'checkbox-content');
        $renderer = $this->renderer();
        $resolver = new \Laminas\View\Resolver\AggregateResolver();
        $resolver->attach($renderer->resolver());
        $resolver->attach(new \Laminas\View\Resolver\TemplateMapResolver([
            'checkbox-content' => __DIR__ . '/_files/checkbox-content.phtml',
        ]));
        $renderer->setResolver($resolver);
        foreach ([false, true] as $checked) {
            $element->setChecked($checked);
            $element->setMessages(['This confirmation is required']);
            $markup = $renderer->formRow($element);
            self::assertSame(1, substr_count($markup, 'class="validation-wrapper"'));
            self::assertSame(substr_count($markup, '<div'), substr_count($markup, '</div>'));
            $xpath = $this->xpath($markup);
            self::assertSame(
                1,
                $xpath->query('//input[@type="checkbox" and @data-aria-controls="confirmation-hidden"]')->length
            );
            self::assertSame('Conditional content', trim($xpath->evaluate('string(//*[@id="confirmation-hidden"])')));
            self::assertSame(
                $checked ? 0 : 1,
                $xpath->query('//*[contains(@class, "govuk-checkboxes__conditional--hidden")]')->length
            );
            self::assertSame(0, $xpath->query('//*[contains(@class, "checkbox__hidden-content")]')->length);
        }
    }

    public function testMultiCheckboxOptionsUseGovukMarkupAndPreserveSubmissionValues(): void
    {
        $element = new MultiCheckbox('countries');
        $element->setLabel('Select countries');
        $element->setValueOptions([
            'GB' => 'Great Britain',
            'FR' => ['value' => 'FR', 'label' => 'France', 'disabled' => true],
        ]);
        $element->setValue(['GB']);
        foreach (['formMultiCheckbox', 'FormMultiCheckbox', 'formmulticheckbox', 'form_multi_checkbox', 'formElement', 'formRow'] as $entry) {
            $xpath = $this->xpath($this->renderer()->plugin($entry)($element));
            self::assertSame(1, $xpath->query('//div[@class="govuk-checkboxes"]')->length);
            self::assertSame(2, $xpath->query('//div[@class="govuk-checkboxes__item"]')->length);
            self::assertSame(
                1,
                $xpath->query('//input[@name="countries[]" and @value="GB" and @checked]')->length
            );
            self::assertSame(
                1,
                $xpath->query('//input[@name="countries[]" and @value="FR" and @disabled]')->length
            );
            foreach ($xpath->query('//input[@type="checkbox"]') as $input) {
                self::assertSame('govuk-checkboxes__input', $input->getAttribute('class'));
                self::assertSame(1, $xpath->query('//label[@for="' . $input->getAttribute('id') . '"]')->length);
                self::assertSame(0, $xpath->query('//label/input')->length);
            }
        }
    }

    public function testMultiCheckboxErrorKeepsExistingValidationWrapperAndDescription(): void
    {
        $element = new MultiCheckbox('countries');
        $element->setLabel('Select countries');
        $element->setValueOptions(['GB' => 'Great Britain', 'FR' => 'France']);
        $element->setMessages(['Select at least one country']);
        $xpath = $this->xpath($this->renderer()->plugin('formRow')($element));

        self::assertSame(1, $xpath->query('//div[@class="validation-wrapper"]')->length);
        self::assertSame(1, $xpath->query('//p[@id="countries-error"]')->length);
        self::assertSame(2, $xpath->query('//input[@type="checkbox" and @aria-describedby="countries-error"]')->length);
        self::assertSame(1, $xpath->query('//fieldset[@aria-describedby="countries-error"]')->length);
    }

    public function testMultiCheckboxPreservesCustomColumnSeparators(): void
    {
        $element = new MultiCheckbox('sections');
        $element->setValueOptions(['A' => 'First section', 'B' => 'Second section']);
        $helper = $this->renderer()->plugin('formMultiCheckbox');
        $helper->setSeparator('</div><div class="field--two-col">');
        $xpath = $this->xpath('<div class="field--two-col">' . $helper($element) . '</div>');

        self::assertSame(
            2,
            $xpath->query('//div[@class="field--two-col"]/div[@class="govuk-checkboxes"]/div/label')->length
        );
    }

    public static function errorTargetOptions(): array
    {
        return [
            'countries' => ['countries', ['GB' => 'Great Britain', 'FR' => 'France']],
            'areas' => ['areas', ['England', 'Wales']],
            'custom first option ID' => ['countries', [
                ['value' => 'GB', 'label' => 'Great Britain', 'attributes' => ['id' => 'country-GB']],
                'FR' => 'France',
            ]],
            'generated nested ID' => [null, ['GB' => 'Great Britain']],
        ];
    }

    #[DataProvider('errorTargetOptions')]
    public function testErrorSummaryTargetsAnExistingCheckboxBeforeAndAfterRendering(?string $id, array $options): void
    {
        $element = new MultiCheckbox('countries');
        if ($id !== null) {
            $element->setAttribute('id', $id);
        }
        $element->setLabel('Countries');
        $element->setValueOptions($options);
        $form = new \Laminas\Form\Form('Countries');
        $fields = new \Laminas\Form\Fieldset('fields');
        $fields->add($element);
        $form->add($fields);
        $form->setMessages(['fields' => ['countries' => ['Select at least one country']]]);
        $renderer = $this->renderer();
        $translator = new Translator();
        $errors = new FormErrors(new FormElementMessageFormatter($translator), $translator);
        $errors->setView($renderer);
        $summaryBefore = $errors($form);
        $form->prepare();
        $markup = $renderer->formRow($element);
        foreach ([$summaryBefore, $errors($form)] as $summary) {
            $xpath = $this->xpath($summary . $markup);
            $target = substr($xpath->evaluate('string(//a/@href)'), 1);
            self::assertNotSame('', $target);
            self::assertSame(1, $xpath->query('//input[@type="checkbox" and @id="' . $target . '"]')->length);
        }
    }

    private function xpath(string $markup): \DOMXPath
    {
        $document = new \DOMDocument();
        $document->loadHTML($markup);
        return new \DOMXPath($document);
    }
}
