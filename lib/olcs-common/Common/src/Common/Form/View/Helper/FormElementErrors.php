<?php

namespace Common\Form\View\Helper;

use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Laminas\Form\View\Helper\FormElementErrors as LaminasFormElementErrors;
use Laminas\I18n\Translator\TranslatorInterface;
use Traversable;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;

/**
 * @see FormElementErrorsFactory
 * @see \CommonTest\Form\View\Helper\FormElementErrorsTest
 */
class FormElementErrors extends LaminasFormElementErrors
{
    protected $attributes = [
        'class' => 'govuk-error-message',
    ];

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
     * @psalm-suppress NoValue
     *
     * Render validation errors for the provided $element
     *
     * @param ElementInterface $element    Element to render errors for
     * @param array            $attributes HTML attributes to add to the render markup
     *
     * @throws Exception\DomainException
     */
    #[\Override]
    public function render(ElementInterface $element, array $attributes = []): string
    {
        $messages = $element->getMessages();

        if ($messages === []) {
            return '';
        }

        if (!is_array($messages) && !$messages instanceof Traversable) {
            throw new Exception\DomainException(
                sprintf(
                    '%s expects that $element->getMessages() will return an array or Traversable; received "%s"',
                    __METHOD__,
                    (get_debug_type($messages))
                )
            );
        }

        // Prepare attributes for opening tag
        $attributes = array_merge($this->attributes, $attributes);
        $attributes = $this->createAttributesString($attributes);
        if ($attributes !== '' && $attributes !== '0') {
            $attributes = ' ' . $attributes;
        }

        $elementShouldEscape = $element->getOption('shouldEscapeMessages');
        $escaper = $this->getEscapeHtmlHelper();

        // Flatten message array
        $messagesToPrint = [];
        array_walk_recursive($messages, function ($item, $itemKey) use (&$messagesToPrint, $elementShouldEscape, $element, $escaper) {
            $shouldEscape = true;
            $message = $this->messageFormatter->formatElementMessage($element, $item, $itemKey);
            if ($shouldEscape && $elementShouldEscape !== false) {
                $message = call_user_func($escaper, $message);
            }

            $messagesToPrint[] = $message;
        });

        if ($messagesToPrint === []) {
            return '';
        }

        $markup = '';
        foreach ($messagesToPrint as $message) {
            $markup .= sprintf('<p%s><span class="govuk-visually-hidden">Error:</span>%s</p>', $attributes, $message);
        }

        return $markup;
    }
}
