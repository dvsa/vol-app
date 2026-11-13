<?php

namespace Common\Form\View\Helper;

use Common\Form\Elements\Types\PostcodeSearch;
use Common\Form\Elements\Types\Radio;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\FormInterface;
use Laminas\Form\Fieldset;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * @see FormErrorsFactory
 * @see \CommonTest\Form\View\Helper\FormErrorsTest
 */
class FormErrors extends AbstractHelper
{
    /**
     * If set to true, then render formErrors regardless of whether the form is valid.
     * Required for EBSR upload where the form is valid but we still display errors.
     * @var bool
     */
    protected $ignoreValidation = false;

    /**
     * @var FormElementMessageFormatter
     */
    protected $messageFormatter;

    public function __construct(FormElementMessageFormatter $messageFormatter, TranslatorInterface $translator)
    {
        $this->messageFormatter = $messageFormatter;
        $this->setTranslator($translator);
    }

    /**
     * Invoke as function
     *
     * @param FormInterface|null $form             Form to be rendered
     * @param bool               $ignoreValidation Ignore validation
     *
     * @return static|string
     */
    public function __invoke(?FormInterface $form = null, $ignoreValidation = false)
    {
        if (!$form instanceof \Laminas\Form\FormInterface) {
            return $this;
        }

        $this->ignoreValidation = (bool) $ignoreValidation;

        return $this->render($form);
    }

    /**
     * Renders the error messages.
     *
     * @param FormInterface $form Form that is being rendered
     *
     * @return string
     */
    public function render(FormInterface $form)
    {
        $messages = $form->getMessages();

        if ($messages === []) {
            return '';
        }

        $messagesOpenFormat = '
<div class="govuk-error-summary" data-module="govuk-error-summary">
    <div role="alert">
        <h2 class="govuk-error-summary__title">%s</h2>
        <div class="govuk-error-summary__body">
            <p>%s</p>
            <ul class="govuk-list govuk-error-summary__list">
                <li>';
                    $messageSeparatorString = '
                </li>
                <li>';
                    $messageCloseString = '
                </li>
            </ul>
        </div>
    </div>
</div>';

        $messagesTitle = $form->getOption('formErrorsTitle') ?
            $this->translate($form->getOption('formErrorsTitle')) :
            $this->translate('form-errors');
        $messagesParagraph = $form->getOption('formErrorsParagraph') ?
            $this->translate($form->getOption('formErrorsParagraph')) :
            '';

        return sprintf($messagesOpenFormat, $messagesTitle, $messagesParagraph)
            . implode($messageSeparatorString, $this->getFlatMessages($messages, $form))
            . $messageCloseString;
    }

    /**
     * Recurse the messages array and flatten them out
     *
     * @param array    $messages Multi dimension array of form error messages
     * @param Fieldset $fieldset The fieldset element owning the messages
     *
     * @return array
     */
    protected function getFlatMessages($messages, $fieldset, ?string $elementPath = null)
    {
        $flatMessages = [];

        foreach ($messages as $field => $message) {
            $path = $elementPath;
            if ($fieldset instanceof Fieldset && $fieldset->has($field)) {
                    $element = $fieldset->get($field);
                    $path = $elementPath === null ? (string) $field : $elementPath . '[' . $field . ']';
            } else {
                $element = $fieldset;
            }

            if (is_array($message)) {
                $flatMessages = array_merge(
                    $flatMessages,
                    $this->getFlatMessages($message, $element, $path)
                );
            } else {
                $flatMessages[] = $this->formatMessage($element, $message, $field, $path);
            }
        }

        return $flatMessages;
    }

    /**
     * Format a message
     *
     * @param string $message
     * @param (int|string) $messageKey
     *
     * @psalm-param array-key $messageKey
     */
    protected function formatMessage(
        ElementInterface $element,
        $message,
        mixed $messageKey,
        ?string $elementPath = null
    ): string {
        $elementShouldEscape = $element->getOption('shouldEscapeMessages');
        $shouldEscape = true;
        $message = $this->messageFormatter->formatElementMessage($element, $message, $messageKey);
        if ($shouldEscape && $elementShouldEscape !== false) {
            $message = call_user_func($this->getEscapeHtmlHelper(), $message);
        }

        // Try and find an element to link to
        $anchor = $this->getNamedAnchor($element, $elementPath);

        // If we have an ID
        if (!empty($anchor)) {
            return sprintf('<a href="#%s">%s</a>', $anchor, $message);
        }

        return $message;
    }

    /**
     * Try and find an anchor to link to
     *
     * @param ElementInterface $element Element that has the error that we want to link to
     *
     * @return string
     */
    protected function getNamedAnchor($element, ?string $elementPath = null)
    {
        $fallbackName = $elementPath !== null && str_contains($elementPath, '[')
            ? $elementPath
            : null;

        // For PostcodeSearch we want to use the id of the text input, as this is the element we want to receive focus
        if (
            $element instanceof PostcodeSearch
            && !empty($element->get('postcode')->getAttribute('id'))
        ) {
            return $element->get('postcode')->getAttribute('id');
        }

        // For DateSelect element we want to focus on the day element
        if ($element instanceof DateSelect) {
            // id is automatically generated on day element when it is rendered using this pattern
            return ($element->getAttribute('id') ?: ($fallbackName ?? $element->getName())) . '_day';
        }

        if ($element instanceof Radio) {
            $valueOptions = $element->getValueOptions();
            $firstOption = reset($valueOptions);
            $radioId = is_array($firstOption) ? ($firstOption['attributes']['id'] ?? null) : null;
            if (!empty($radioId)) {
                return $radioId;
            }
        }

        $fieldsetAttributes = $element->getOption('fieldset-attributes');
        if (isset($fieldsetAttributes['id'])) {
            return $fieldsetAttributes['id'];
        }

        $labelAttributes = $element->getOption('label_attributes');

        if (isset($labelAttributes['id'])) {
            return $labelAttributes['id'];
        }

        $id = $element->getAttribute('id');

        if ($id) {
            return $id;
        }

        // The input renderer uses the prepared, bracketed name when no ID is configured.
        return $fallbackName ?? $element->getName();
    }

    /**
     * Helper method to translate strings
     *
     * @param string $text Text to translate
     *
     * @return string
     */
    protected function translate($text)
    {
        return $this->translator->translate($text);
    }
}
