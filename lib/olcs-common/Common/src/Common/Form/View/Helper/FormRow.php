<?php

namespace Common\Form\View\Helper;

use Common\Form\Elements\InputFilters\ActionButton;
use Common\Form\Elements\InputFilters\ActionLink;
use Common\Form\Elements\InputFilters\NoRender;
use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Elements\Types\Table;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\ElementInterface;
use Laminas\Form\LabelAwareInterface;
use Common\Form\Elements\Types\AttachFilesButton;
use Common\Form\Elements\Types\ReadonlyElement;

/**
 * @see \CommonTest\Form\View\Helper\FormRowTest
 */
class FormRow extends \Laminas\Form\View\Helper\FormRow
{
    /**
     * The form row output format.
     *
     * @var string
     */
    private static $format = '<div class="field %s">%s</div>';

    private static $formatNoDivClass = '<div class="%s">%s</div>';

    private static $readonlyFormat = '<div class="field read-only %s"><p>%s<br><b>%s</b></p></div>';

    private static $errorClass = '<div class="validation-wrapper">%s</div>';

    protected $fieldsetWrapper = '<fieldset%4$s>%2$s%1$s%3$s</fieldset>';

    protected $fieldsetLabelWrapper = '<legend>%s</legend>';

    protected $fieldsetHintFormat = '<p class="hint">%s</p>';

    public function __construct()
    {
    }

    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @param ElementInterface $element       Element
     * @param null|string      $labelPosition Label Position
     *
     * @throws \Laminas\Form\Exception\DomainException
     */
    #[\Override]
    public function render(ElementInterface $element, ?string $labelPosition = null): string
    {
        if ($element instanceof ReadonlyElement) {
            $class = $element->getAttribute('data-container-class');
            $label = $this->getView()->translate($element->getLabel());
            $value = $element->getValue();

            return sprintf(self::$readonlyFormat, $class, $label, $value);
        }

        if ($element instanceof ActionButton || $element instanceof ActionLink) {
            return $this->renderRow($element);
        }

        if ($element instanceof NoRender) {
            return '';
        }

        if ($element instanceof Table) {
            $markup = $element->render();
        } elseif ($element instanceof DateSelect) {
            $element->setOption('hint-position', 'start');

            if ($element->getOption('fieldsetClass') === null) {
                $element->setOption('fieldsetClass', 'date');
            }

            $markup = $this->renderFieldset($element, false);
        } else {
            if ($element instanceof SingleCheckbox) {
                $this->labelPosition = self::LABEL_APPEND;
            }

            $renderAsFieldset = $element->getOption('render_as_fieldset');

            $markup = $renderAsFieldset ? $this->renderFieldset($element) : $this->renderRow($element);

            if ($element instanceof SingleCheckbox) {
                $this->labelPosition = self::LABEL_PREPEND;
            }
        }

        $wrap = true;

        $type = $element->getAttribute('type');
        $allowWrap = $element->getAttribute('allowWrap');
        if (
            $type === 'multi_checkbox' || ($type === 'radio' && !$allowWrap)
            || $element->getAttribute('id') === 'security' || $allowWrap === false
        ) {
            $wrap = false;
        }

        if (!($element instanceof Hidden) && $wrap) {
            $classAttribute = $element->getAttribute('class');
            $class = $element->getMessages() === [] ? $element->getAttribute('data-container-class') : '';
            if ($classAttribute !== null && str_starts_with($classAttribute, 'govuk-visually-hidden')) {
                $markup = sprintf(self::$format, 'govuk-visually-hidden', $markup);
            } elseif ($element->getOption('render-container') !== false) {
                $renderAsFieldset = $element->getOption('render_as_fieldset');

                if (!$renderAsFieldset) {
                    if ($element instanceof AttachFilesButton) {
                        $markup = sprintf(self::$formatNoDivClass, $class, $markup);
                    } else {
                        $markup = sprintf(self::$format, $class, $markup);
                    }
                }
            }
        }

        if ($element->getMessages() !== []) {
            $markup = sprintf(self::$errorClass, $markup);
        }

        $this->setRenderErrors(true);

        return $markup;
    }

    /**
     * Render fieldset
     *
     * @param ElementInterface $element Element
     * @param bool             $primary Is primary
     *
     * @return string
     */
    protected function renderFieldset(ElementInterface $element, $primary = true)
    {
        $hintText = $element->getOption('hint');
        $legend = '';
        $label = $element->getLabel();

        if (isset($hintText) && '' !== $hintText) {
            $hint = sprintf($this->fieldsetHintFormat, $this->getView()->translate($hintText));
        } else {
            $hint = '';
        }

        $element->setOption('hint', '');
        $element->setLabel('');

        if ($element->getOption('hint-position') === 'end') {
            $markup = $this->renderRow($element) . $hint;
        } else {
            $markup = $hint . $this->renderRow($element);
        }

        if ($label !== null && $label !== '' && $label !== '0') {
            $translator = $this->getTranslator();

            if ($translator !== null) {
                $label = $translator->translate(
                    $label,
                    $this->getTranslatorTextDomain()
                );
            }

            if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                $escapeHtmlHelper = $this->getEscapeHtmlHelper();
                $label = $escapeHtmlHelper($label);
            }

            $labelSfx = (string) $element->getOption('label-suffix');
            if (isset($label) && '' !== $labelSfx) {
                $label .= ' ' . $translator->translate($labelSfx, $this->getTranslatorTextDomain());
            }

            $legend = sprintf(
                $this->fieldsetLabelWrapper,
                $label
            );
        }

        if ($element->getOption('fieldsetClass') !== null) {
            $fieldsetClass = $element->getOption('fieldsetClass');
        } elseif ($primary) {
            $fieldsetClass = 'fieldset--primary';
        }

        return sprintf(
            $this->fieldsetWrapper,
            $markup,
            $legend,
            '',
            isset($fieldsetClass) ? ' class="' . $fieldsetClass . '"' : ''
        );
    }

    protected function renderRow(ElementInterface $element): string
    {
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();

        $label           = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();

        // Translate the label
        if (isset($label) && '' !== $label && null !== ($translator = $this->getTranslator())) {
            $label = $translator->translate(
                $label,
                $this->getTranslatorTextDomain()
            );
        }

        // Does this element have errors ?
        if ($element->getMessages() !== [] && ($inputErrorClass !== '' && $inputErrorClass !== '0')) {
            $classAttributes = ($element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '');
            $classAttributes .= $inputErrorClass;

            $element->setAttribute('class', $classAttributes);
        }

        if ($this->partial) {
            $vars = [
                'element'           => $element,
                'label'             => $label,
                'labelAttributes'   => $this->labelAttributes,
                'labelPosition'     => $this->labelPosition,
                'renderErrors'      => $this->renderErrors,
            ];

            return $this->view->render($this->partial, $vars);
        }

        $elementString = $elementHelper->render($element);

        if (isset($label) && '' !== $label) {
            // @NOTE commented this out, we need to be able to add HTML to a label, can't see that commenting this out
            // will affect anything
            //$label = $escapeHtmlHelper($label);
            $labelAttributes = $element->getLabelAttributes();

            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }

            // Multicheckbox elements have to be handled differently as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            $type = $element->getAttribute('type');

            if ($type === 'multi_checkbox' || $type === 'radio') {
                $fieldsetAttributes = $element->getOption('fieldset-attributes');
                $dataGroup = $element->getOption('fieldset-data-group');

                if (!is_null($dataGroup)) {
                    $fieldsetAttributes['data-group'] = $dataGroup;
                }

                $legendAttributes = $element->getOption('legend-attributes');
                $legendAttributesString = '';
                if (is_array($legendAttributes)) {
                    $legendAttributesString = ' ' . $this->createAttributesString($legendAttributes);
                }

                $attributesString = '';
                if (is_array($fieldsetAttributes)) {
                    $attributesString = ' ' . $this->createAttributesString($fieldsetAttributes);
                }

                $singleRadio = $element->getOption('single-radio');
                if ($singleRadio) {
                    $markup = $elementString;
                } else {
                    $markup = sprintf(
                        '<fieldset%s><legend%s>%s</legend>%s</fieldset>',
                        $attributesString,
                        $legendAttributesString,
                        $label,
                        $elementString
                    );
                }
            } else {
                if (
                    $element->hasAttribute('id')
                    && ! ($element instanceof SingleCheckbox)
                    && ($element instanceof LabelAwareInterface && !$element->getLabelOption('always_wrap'))
                ) {
                    $labelOpen = '';
                    $labelClose = '';
                    $label = $labelHelper($element);
                } else {
                    $labelOpen  = $labelHelper->openTag($labelAttributes);
                    $labelClose = $labelHelper->closeTag();
                }

                // Button element is a special case, because label is always rendered inside it
                if ($element instanceof Button) {
                    $labelOpen = '';
                    $labelClose = '';
                    $label = '';
                }

                $labelPosition = $this->labelPosition;
                if ($element instanceof LabelAwareInterface && $element->getLabelOption('label_position')) {
                    $labelPosition = $element->getLabelOption('label_position');
                }

                $markup = match ($labelPosition) {
                    self::LABEL_PREPEND => $labelOpen . $label . $elementString . $labelClose,
                    default => $labelOpen . $elementString . $label . $labelClose,
                };
            }
        } else {
            $markup = $elementString;
        }

        return $markup;
    }
}
