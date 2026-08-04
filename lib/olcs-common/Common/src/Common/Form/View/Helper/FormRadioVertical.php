<?php

namespace Common\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Laminas\Form\Element\MultiCheckbox;

/**
 * Helper to render the GDS vertical radio button pattern.
 *
 * @see \CommonTest\Form\View\Helper\FormRadioVerticalTest
 */
class FormRadioVertical extends \Laminas\Form\View\Helper\FormCollection
{
    /**
     * @param ElementInterface $element Element to render
     * @return string HTML
     */
    #[\Override]
    public function render(ElementInterface $element): string
    {
        $variables = $this->view->vars()->getArrayCopy();
        $fieldset = $variables['element'] = $this->wrapInFieldSet($element);
        $radioElement = $variables['radioElement'] = $fieldset->get($fieldset->getOption('radio-element') ?? 'radio');
        $variables['valueOptions'] = $this->parseElementValueOptions($fieldset, $radioElement);
        $variables['hint'] = $element->getOption('hint');
        $variables['label'] = $element->getLabel();
        $variables['label_attributes'] = $element->getLabelAttributes();
        return $this->view->render('partials/form/radio-vertical', $variables);
    }

    protected function wrapInFieldSet($element): Fieldset
    {
        $fieldset = $element;
        if (! ($fieldset instanceof Fieldset)) {
            $fieldset = new Fieldset();
            $fieldset->add($element);
            $fieldset->setOption('radio-element', $element->getName());
        }

        return $fieldset;
    }

    protected function parseElementValueOptions(Fieldset $parentFieldset, MultiCheckbox $element): array
    {
        $valueOptions = [];
        foreach ($element->getValueOptions() as $key => $valueOption) {
            if (! is_array($valueOption)) {
                $valueOption = ['value' => $key, 'label' => $valueOption];
            }

            if (! isset($valueOption['conditional_content'])) {
                $conditionalContentSiblingKey = sprintf('%sContent', $key);
                if ($parentFieldset->has($conditionalContentSiblingKey)) {
                    $valueOption['conditional_content'] = $parentFieldset->get($conditionalContentSiblingKey);
                }
            }

            $valueOptions[$key] = $valueOption;
        }

        return $valueOptions;
    }
}
