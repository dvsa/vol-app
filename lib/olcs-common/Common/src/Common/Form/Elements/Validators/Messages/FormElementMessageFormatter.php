<?php

declare(strict_types=1);

namespace Common\Form\Elements\Validators\Messages;

use Laminas\Form\ElementInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Common\Helper\Str;

/**
 * @see FormElementMessageFormatterFactory
 * @see \CommonTest\Form\Elements\Validators\Messages\FormElementMessageFormatterTest
 */
class FormElementMessageFormatter
{
    public const FIELD_LABEL_PLACEHOLDER = '{{fieldLabel}}';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $messagesReplacementProviders = [];

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string|callable $defaultMessageOrProvider
     */
    public function enableReplacementOfMessage(string $messageKey, $defaultMessageOrProvider): void
    {
        if (is_string($defaultMessageOrProvider)) {
            $defaultMessageOrProvider = static fn(): string => $defaultMessageOrProvider;
        }

        assert(is_callable($defaultMessageOrProvider), 'Expected default message provider to be callable or string');
        $this->messagesReplacementProviders[$messageKey] = $defaultMessageOrProvider;
    }

    /**
     * @return callable|null
     */
    public function getReplacementFor(string $messageKey)
    {
        return $this->messagesReplacementProviders[$messageKey] ?? null;
    }

    /**
     * @param string $message
     */
    public function formatElementMessage(ElementInterface $element, $message, mixed $messageKey = null): string
    {
        $label = $this->getElementShortLabel($element);
        if ($label === '' || $label === '0') {
            $message = $this->replaceDefaultValidationMessages($element, $messageKey, $message);
            $message = $this->translator->translate($message);
            $message = $this->replaceMessageVariables($element, $message);
        } else {
            $label = $this->translator->translate($label) . ': ';
            $message = $this->translator->translate($message);
            $message = $this->translator->translate($label . $message);
        }

        // If there is a specified custom error message, use that
        if ($this->getElementCustomErrorMessage($element)) {
            $message = $this->getElementCustomErrorMessage($element);

            // Translate the message since we have now got new untranslated content
            $message = $this->translator->translate($message);
        }

        return ucfirst($message);
    }

    /**
     * Get the custom error message if it exists
     *
     * @param ElementInterface $element Element to get cusomt error message from
     *
     * @return string
     */
    protected function getElementCustomErrorMessage(ElementInterface $element)
    {
        $errorMessage = $element->getOption('error-message');

        if ($errorMessage) {
            return $errorMessage;
        }

        return '';
    }

    /**
     * Get the short label for an element if it exists
     */
    protected function getElementShortLabel(ElementInterface $element): string
    {
        $label = $element->getOption('short-label');
        return $label ?: '';
    }

    protected function replaceMessageVariables(ElementInterface $element, string $message): string
    {
        $labelText = $this->getFieldLabelForElement($element);

        // Replace field label message variable
        $message = str_replace(static::FIELD_LABEL_PLACEHOLDER, $labelText, $message);

        return $message;
    }

    /**
     * Determines whether an element has values for each message variable used in a message.
     */
    protected function elementHasVariablesInMessage(ElementInterface $element, string $message): bool
    {
        if (str_contains($message, static::FIELD_LABEL_PLACEHOLDER)) {
            $elementLabel = $this->getFieldLabelForElement($element);
            if ($elementLabel === '' || $elementLabel === '0') {
                return false;
            }

            if (Str::containsHtml($elementLabel)) {
                return false;
            }
        }

        return true;
    }

    protected function getFieldLabelForElement(ElementInterface $element): string
    {
        $label = $element->getLabel();
        if (! is_string($label)) {
            return '';
        }

        $label = trim($label);
        if ($label === '' || $label === '0') {
            return '';
        }

        return $this->translator->translate($label);
    }

    protected function replaceDefaultValidationMessages(ElementInterface $element, mixed $messageKey, string $message): string
    {
        if ($this->isDefaultMessageForKey($messageKey, $message)) {
            $elementType = $element->getAttribute('type');
            $replacementMessage = $this->getReplacementForDefaultElementMessage($element, $messageKey, $elementType);
            if ($replacementMessage !== false) {
                return $replacementMessage;
            }

            $replacementMessage = $this->getReplacementForDefaultElementMessage($element, $messageKey, 'default');
            if ($replacementMessage !== false) {
                return $replacementMessage;
            }
        }

        return $message;
    }

    /**
     * @param $messageKey
     * @param string|null $elementType
     * @return false|string
     */
    protected function getReplacementForDefaultElementMessage(ElementInterface $element, $messageKey, string $elementType = null)
    {
        if (null === $elementType) {
            return false;
        }

        $translationKey = sprintf('validation.element.%s.%s', $elementType, $messageKey);
        $replacementMessage = $this->translator->translate($translationKey);
        if ($replacementMessage !== $translationKey && $this->elementHasVariablesInMessage($element, $replacementMessage)) {
            return $translationKey;
        }

        return false;
    }

    /**
     * @param $messageKey
     */
    protected function isDefaultMessageForKey($messageKey, string $message): bool
    {
        $defaultMessageProvider = $this->messagesReplacementProviders[$messageKey] ?? null;
        if (null === $defaultMessageProvider) {
            return false;
        }

        $defaultMessage = $defaultMessageProvider($messageKey);
        if ($defaultMessage === $message) {
            return true;
        }

        $translatedDefaultMessage = $this->translator->translate($defaultMessage);
        return $translatedDefaultMessage === $message;
    }
}
