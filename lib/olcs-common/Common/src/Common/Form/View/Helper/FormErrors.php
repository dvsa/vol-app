<?php

namespace Common\Form\View\Helper;

use Common\Form\Elements\Types\PostcodeSearch;
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
    public function __invoke(FormInterface $form = null, $ignoreValidation = false)
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
<div class="validation-summary" role="alert" id="validationSummary">
    <h2 class="govuk-heading-m">%s</h2>
    <p>%s</p>
    <ol class="validation-summary__list">
        <li class="validation-summary__item">';
            $messageSeparatorString = '
        </li>
        <li class="validation-summary__item">';
            $messageCloseString = '
        </li>
    </ol>
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
    protected function getFlatMessages($messages, $fieldset)
    {
        $flatMessages = [];

        foreach ($messages as $field => $message) {
            if ($fieldset instanceof Fieldset) {
                $element = $fieldset->has($field) ? $fieldset->get($field) : $fieldset;
            } else {
                $element = $fieldset;
            }

            if (is_array($message)) {
                $flatMessages = array_merge(
                    $flatMessages,
                    $this->getFlatMessages($message, $element)
                );
            } else {
                $flatMessages[] = $this->formatMessage($element, $message, $field);
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
    protected function formatMessage(ElementInterface $element, $message, mixed $messageKey): string
    {
        $elementShouldEscape = $element->getOption('shouldEscapeMessages');
        $shouldEscape = true;
        $message = $this->messageFormatter->formatElementMessage($element, $message, $messageKey);
        if ($shouldEscape && $elementShouldEscape !== false) {
            $message = call_user_func($this->getEscapeHtmlHelper(), $message);
        }

        // Try and find an element to link to
        $anchor = $this->getNamedAnchor($element);

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
    protected function getNamedAnchor($element)
    {
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
            return $element->getAttribute('id') . '_day';
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

        // Last resort, If can't find an ID then use the name as that is often copied to ID when rendered
        return $element->getName();
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
