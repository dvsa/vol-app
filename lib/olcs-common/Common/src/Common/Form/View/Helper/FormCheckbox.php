<?php

namespace Common\Form\View\Helper;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\FormCheckbox as LaminasFormCheckbox;
use Laminas\Form\View\Helper\FormInput;
use Laminas\View\Renderer\PhpRenderer;

class FormCheckbox extends FormInput
{
    public function __construct(private LaminasFormCheckbox $checkboxHelper)
    {
    }

    #[\Override]
    public function render(ElementInterface $element): string
    {
        assert($element instanceof Checkbox, 'Expected a checkbox element');

        $this->prepareElement($element);
        if ($element->getOption('content')) {
            $element->setAttribute('data-aria-controls', $element->getAttribute('id') . '-hidden');
        }

        $view = $this->getView();
        assert($view instanceof PhpRenderer, 'Expected a PHP view renderer');

        return $view->partial(
            'partials/form/checkbox-govuk',
            [
                'element' => $element,
                'input' => $this->renderInput($element),
                'small' => $element instanceof SingleCheckbox,
                'content' => $element->getOption('content'),
                'data' => isset($view->data) ? $view->data : null,
            ]
        );
    }

    public function prepareElement(Checkbox $element): void
    {
        if (!$element->getAttribute('id')) {
            $element->setAttribute(
                'id',
                preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $element->getName())
            );
        }

        $inputClasses = array_filter(
            preg_split('/\s+/', trim((string) $element->getAttribute('class'))) ?: [],
            static fn(string $class): bool => $class !== ''
        );
        $inputClasses[] = 'govuk-checkboxes__input';
        $element->setAttribute('class', implode(' ', array_unique($inputClasses)));

        $labelAttributes = $element->getLabelAttributes();
        $labelClasses = preg_split('/\s+/', trim((string) ($labelAttributes['class'] ?? ''))) ?: [];
        $labelAttributes['class'] = implode(
            ' ',
            array_unique(array_merge(['govuk-label', 'govuk-checkboxes__label'], $labelClasses))
        );
        $element->setLabelAttributes($labelAttributes);
    }

    public function renderInput(ElementInterface $element): string
    {
        $helper = $this->checkboxHelper;
        if ($this->getView() !== null) {
            $helper->setView($this->getView());
        } else {
            $helper->setDoctype($this->getDoctype());
            if ($helper->getEncoding() !== $this->getEncoding()) {
                $helper->setEncoding($this->getEncoding());
            }
        }
        $helper->setTranslator($this->getTranslator(), $this->getTranslatorTextDomain());

        foreach (array_keys($this->validTagAttributes) as $attribute) {
            $helper->addValidAttribute($attribute);
        }
        foreach ($this->validTagAttributePrefixes as $prefix) {
            $helper->addValidAttributePrefix($prefix);
        }
        foreach (array_keys($this->translatableAttributes) as $attribute) {
            $helper->addTranslatableAttribute($attribute);
        }
        foreach ($this->translatableAttributePrefixes as $prefix) {
            $helper->addTranslatableAttributePrefix($prefix);
        }

        return $helper->render($element);
    }
}
