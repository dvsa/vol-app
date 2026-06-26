<?php

/**
 * Form Collection wrapper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\View\Helper;

use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Form\Elements\Types\CheckboxAdvanced;
use Common\Form\Elements\Types\CompanyNumber;
use Common\Form\Elements\Types\FileUploadList;
use Common\Form\Elements\Types\FileUploadListItem;
use Common\Form\Elements\Types\HoursPerWeek;
use Common\Form\Elements\Types\InputSearch;
use Common\Form\Elements\Types\PostcodeSearch;
use Common\Form\Elements\Types\RadioHorizontal;
use Common\Form\Elements\Types\RadioVertical;
use Common\Form\View\Helper\Readonly\FormFieldset;
use Laminas\Form\Element\Collection as CollectionElement;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\LabelAwareInterface;

/**
 * Form Collection wrapper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormCollection extends \Laminas\Form\View\Helper\FormCollection
{
    /**
     * Instance map to view helper
     *
     * @var array
     */
    protected $classMap = [
        RadioHorizontal::class => 'formRadioHorizontal',
        CheckboxAdvanced::class => 'formCheckboxAdvanced',
        RadioVertical::class => 'formRadioVertical',
        AbstractInputSearch::class => FormInputSearch::class,
    ];

    private static $htmlFileUploadCntr =
        '<div class="help__text"><h3 class="file__heading">%s</h3><ul%s>%s%s</ul></div>';

    /**
     * @var bool
     */
    protected $readOnly = false;

    /**
     * Hint format
     *
     * @var string
     */
    private static $hintFormat = '<p class="%s">%s</p>';

    private static $hintClass = 'hint';

    /**
     * Set Is Form in Read only status
     *
     * @param boolean $readOnly Is Read Only
     *
     * @return $this
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * Is Form in Read only status
     *
     * @return boolean
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Render a collection by iterating through all fieldsets and elements
     *
     * @param ElementInterface $element Element
     */
    #[\Override]
    public function render(ElementInterface $element): string
    {
        foreach ($this->classMap as $class => $helperName) {
            if ($element instanceof $class) {
                $helper = $this->view->plugin($helperName);
                return $helper($element);
            }
        }

        $messages = $element->getMessages();

        if ($element instanceof HoursPerWeek && isset($messages['hoursPerWeekContent'])) {
            $tmpMessages = [];
            foreach ($messages['hoursPerWeekContent'] as $fieldMessages) {
                foreach ($fieldMessages as $fieldMessage) {
                    $tmpMessages[] = $fieldMessage;
                }
            }

            unset($messages['hoursPerWeekContent']);
            $messages = array_merge($messages, $tmpMessages);
        }

        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        $hint = $element->getOption('hint');
        $hintClass = $element->getOption('hintClass') ?: self::$hintClass;
        if (!empty($hint)) {
            $view = $this->getView();
            $hint = sprintf(self::$hintFormat, $hintClass, $view->translate($hint));
        }

        $attributes       = $element->getAttributes();
        $markup           = '';
        $templateMarkup   = '';
        $readOnly = $this->isReadOnly() || $element->getOption('readonly');

        /** @var callable $elementHelper */
        $elementHelper    = (
            $readOnly ?
            $this->getView()->plugin('readonlyformrow') :
            $this->getElementHelper()
        );

        // hide readonly elements with additional option remove_if_readonly
        // e.g. HtmlTranslatable elements where we don't know whether to hide or not
        if ($readOnly && $element->getOption('remove_if_readonly')) {
            return '';
        }

        /** @var callable $fieldsetHelper */
        $fieldsetHelper = (
            $readOnly
            ? $this->view->plugin(FormFieldset::class)
            : $this->getFieldsetHelper()
        );

        if ($element instanceof CollectionElement && $element->shouldCreateTemplate()) {
            $templateMarkup = $this->renderTemplate($element);
        }

        if ($element instanceof CompanyNumber && $messages !== []) {
            $attributes['class'] = '';
        }

        foreach ($element->getIterator() as $elementOrFieldset) {
            // Check if this element class has a bespoke view helper
            foreach ($this->classMap as $class => $helperName) {
                if ($elementOrFieldset instanceof $class) {
                    $helper = $this->view->plugin($helperName);
                    $markup .= $helper($elementOrFieldset);
                    continue 2;
                }
            }

            if ($elementOrFieldset instanceof FieldsetInterface) {
                $markup .= $fieldsetHelper($elementOrFieldset);
            } elseif ($elementOrFieldset instanceof ElementInterface) {
                $markup .= $elementHelper($elementOrFieldset);
            }
        }

        if ($readOnly) {
            if ($markup === '') {
                return '';
            }

            return '<ul class="definition-list readonly">' . $markup . '</ul>';
        }

        // If $templateMarkup is not empty, use it for simplify adding new element in JavaScript
        if ($templateMarkup !== '' && $templateMarkup !== '0') {
            $markup .= $templateMarkup;
        }

        // Every collection is wrapped by a fieldset if needed
        if ($element->getOption('shouldWrap') || $this->shouldWrap) {
            $label = $element->getLabel();
            $legend = '';

            if ($label !== null && $label !== '' && $label !== '0') {
                if (null !== ($translator = $this->getTranslator())) {
                    $label = $translator->translate(
                        $label,
                        $this->getTranslatorTextDomain()
                    );
                }

                if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                    $escapeHtmlHelper = $this->getEscapeHtmlHelper();
                    $label = $escapeHtmlHelper($label);
                }

                $legendAttributesString = $this->createAttributesString($element->getLabelAttributes());

                if ($legendAttributesString !== '' && $legendAttributesString !== '0') {
                    $legendAttributesString = ' ' . $legendAttributesString;
                }

                $legend = sprintf(
                    '<legend%s>%s</legend>',
                    $legendAttributesString,
                    $label
                );
            }

            // it's helpful from a JS perspective to give our containers
            // (usually fieldsets) an attribute so we can latch on to them
            // explicitally rather than sniffing around in the DOM
            if (($fieldsetName = $element->getName()) !== null) {
                $attributes['data-group'] = $fieldsetName;
            }

            $attributesString = $this->createAttributesString($attributes);
            if ($attributesString !== '' && $attributesString !== '0') {
                $attributesString = ' ' . $attributesString;
            }

            if ($element instanceof FileUploadList) {
                if ($markup !== '' && $markup !== '0') {
                    $markup = sprintf(
                        self::$htmlFileUploadCntr,
                        $this->view->translate('common.file-upload.table.col.FileName'),
                        $attributesString,
                        $hint,
                        $markup
                    );
                } else {
                    $markup = '';
                }
            } elseif ($element instanceof FileUploadListItem) {
                $markup = sprintf('<li%s>%s%s</li>', $attributesString, $hint, $markup);
            } elseif ($element->getOption('hint-position') === 'below') {
                $markup = sprintf('<fieldset%s>%s%s%s</fieldset>', $attributesString, $legend, $markup, $hint);
            } else {
                $markup = sprintf('<fieldset%s>%s%s%s</fieldset>', $attributesString, $legend, $hint, $markup);
            }
        }

        if ($messages === []) {
            return $markup;
        }

        if (
            !($element instanceof PostcodeSearch)
            && !($element instanceof CompanyNumber)
            && !($element instanceof HoursPerWeek)
            && !$element->getOption('showErrors')
        ) {
            return $markup;
        }

        $elementErrors = $this->view->plugin('form_element_errors')->render($element);

        return sprintf('<div class="validation-wrapper">%s%s</div>', $elementErrors, $markup);
    }
}
