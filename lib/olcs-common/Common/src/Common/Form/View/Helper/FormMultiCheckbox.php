<?php

namespace Common\Form\View\Helper;

use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\MultiCheckbox;

class FormMultiCheckbox extends \Laminas\Form\View\Helper\FormMultiCheckbox
{
    #[\Override]
    protected function renderOptions(
        MultiCheckbox $element,
        array $options,
        array $selectedOptions,
        array $attributes
    ): string {
        $view = $this->getView();
        $checkboxHelper = $view->plugin('formCheckbox');
        assert($checkboxHelper instanceof FormCheckbox);
        $labelHelper = $this->getLabelHelper();
        $baseId = $attributes['id'] ?? $element->getName();
        $items = [];
        $index = 0;

        foreach ($options as $key => $option) {
            ++$index;
            $option = is_scalar($option) ? ['value' => $key, 'label' => $option] : $option;
            $value = $option['value'] ?? '';
            $checkbox = new Checkbox($attributes['name']);
            $checkbox->setAttributes(array_merge($attributes, $option['attributes'] ?? []));
            $checkbox->setAttribute('id', $option['attributes']['id'] ?? ($index === 1 ? $baseId : $baseId . '-' . $index));
            $checkbox->setAttribute('disabled', $option['disabled'] ?? $attributes['disabled'] ?? false);
            $checkbox->setUseHiddenElement(false);
            $checkbox->setCheckedValue($value);
            $checkbox->setChecked(
                in_array($value, $selectedOptions)
                || ($option['selected'] ?? $attributes['selected'] ?? false)
            );
            $checkbox->setLabel($option['label'] ?? '');
            $checkbox->setLabelOptions($element->getLabelOptions());
            $checkbox->setLabelAttributes(array_merge(
                $element->getLabelAttributes() ?: ($this->getLabelAttributes() ?? []),
                $option['label_attributes'] ?? []
            ));
            $checkboxHelper->prepareElement($checkbox);
            $label = $this->escapeLabel($element, $this->translateLabel($checkbox->getLabel()));
            $items[] = '<div class="govuk-checkboxes__item">' .
                $checkboxHelper->renderInput($checkbox) .
                $labelHelper->openTag(array_merge($checkbox->getLabelAttributes(), [
                    'for' => $checkbox->getAttribute('id'),
                ])) .
                $label . $labelHelper->closeTag() . '</div>';
        }

        $open = '<div class="govuk-checkboxes" data-module="govuk-checkboxes">';
        $separator = $this->getSeparator();
        $separator = $separator === '' ? '' : '</div>' . $separator . $open;

        return $open . implode($separator, $items) . '</div>';
    }
}
