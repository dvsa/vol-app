<?php

namespace Common\Form\View\Helper;

use Common\Form\Element\DynamicRadioHtml;
use Common\Form\Elements\InputFilters\ActionLink;
use Common\Form\Elements\Types\AttachFilesButton;
use Common\Form\Elements\Types\GuidanceTranslated;
use Common\Form\Elements\Types\Html;
use Common\Form\Elements\Types\HtmlTranslated;
use Common\Form\Elements\Types\PlainText;
use Common\Form\Elements\Types\Table;
use Common\Form\Elements\Types\TermsBox;
use Common\Form\Elements\Types\TrafficAreaSet;
use Laminas\Form\ElementInterface;
use Laminas\Form\ElementInterface as LaminasElementInterface;
use Laminas\Form\View\Helper\FormElement as LaminasFormElement;

/**
 * @see FormElementFactory
 */
class FormElement extends LaminasFormElement
{
    protected const GUIDANCE_WRAPPER = '<div class="article">%s</div>';

    protected const TERMS_BOX_WRAPPER = '<div %s>%s</div>';

    protected const FILE_CHOOSE_WRAPPER
        = '<ul class="%s"><li class="%s"><label class="%s">%s %s</label><p class="%s">%s</p></li></ul>';

    /**
     * The form row output format.
     *
     * @var string
     */
    private static $format = '%s<div class="%s">%s</div>';

    /**
     * The form row output format.
     *
     * @var string
     */
    private static $topFormat = '<p class="%s">%s</p>%s';

    private $hintClass = 'hint';

    /**
     * Render an element
     *
     * Introspects the element type and attributes to determine which
     * helper to utilize when rendering.
     *
     * @param LaminasElementInterface $element Form Element
     */
    #[\Override]
    public function render(LaminasElementInterface $element): string
    {
        if (!$element->getAttribute('id')) {
            $element->setAttribute('id', $element->getName());
        }

        /** @var \Laminas\View\Renderer\PhpRenderer $renderer */
        $renderer = $this->getView();
        if ($renderer === null || !method_exists($renderer, 'plugin')) {
            return '';
        }

        if ($element instanceof DynamicRadioHtml) {
            $values = $element->getValueOptions();
            $search = [];
            $replace = [];
            $replaceValues = [];
            foreach ($values as $value) {
                if (isset($value['html_elements'])) {
                    $theExtras = '';
                    foreach ($value['html_elements'] as $tag => $option) {
                        $class = $option['class'] ?? '';
                        $theExtras .= sprintf(
                            '<%s class="%s">%s</%s>',
                            $tag,
                            $class,
                            $option['content'],
                            $tag
                        );
                    }

                    $search[] = '[#' . $value['value'] . ']';
                    $replace[] = $theExtras;
                    if (isset($value['html_replace']) && $value['html_replace']) {
                        $value['label'] = '[#' . $value['value'] . ']';
                    } else {
                        $value['label'] = $value['label'] . '[#' . $value['value'] . ']';
                    }
                }

                $replaceValues[] = $value;
            }

            $element->setValueOptions($replaceValues);
            $renderedOptions = str_replace($search, $replace, parent::render($element));
            $element->setValueOptions($values);
            return $renderedOptions;
        }

        if ($element instanceof TrafficAreaSet) {
            $value = $element->getValue();
            $view = $this->getView();

            $markup = sprintf(
                '<div class="label">%s</div>',
                htmlspecialchars($view->translate($value), ENT_QUOTES, 'utf-8')
            );

            return $this->attachHint($element, $markup);
        }

        if ($element instanceof PlainText) {
            /** @var callable $helper */
            $helper = $renderer->plugin('form_plain_text');
            return $helper($element);
        }

        if ($element instanceof ActionLink) {
            $route = $element->getOption('route');
            $url = empty($route) ? $element->getValue() : $this->getView()->url($route, [], [], true);

            $class = '';

            if ($element->getAttribute('class')) {
                $class = $element->getAttribute('class');
            }

            $target = '';

            if ($element->getAttribute('target')) {
                $target = ' target="' . $element->getAttribute('target') . '"';
            }

            $label = $this->getView()->translate($element->getLabel());

            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'utf-8') . '" class="' . $class . '"' . $target . '>' . $label . '</a>';
        }

        if ($element instanceof HtmlTranslated) {
            $wrapper = $element instanceof GuidanceTranslated ? self::GUIDANCE_WRAPPER : '%s';
            $tokens = $element->getTokens();
            if (is_array($tokens) && count($tokens)) {
                $translated = [];

                foreach ($tokens as $token) {
                    $translated[] = $this->getView()->translate($token);
                }

                return sprintf($wrapper, vsprintf($this->getView()->translate($element->getValue()), $translated));
            }
            $value = $element->getValue();
            if (empty($value)) {
                return '';
            }
            return sprintf($wrapper, $this->getView()->translate($element->getValue()));
        }

        if ($element instanceof TermsBox) {
            $attributes = $element->getAttributes();

            if (!isset($attributes['class'])) {
                $attributes['class'] = '';
            }

            $attributes['class'] .= ' terms--box';

            $attr = $renderer->form()->createAttributesString($attributes);

            return sprintf(self::TERMS_BOX_WRAPPER, $attr, $this->getView()->translate($element->getValue()));
        }

        if ($element instanceof Html) {
            return is_string($element->getValue()) ? $element->getValue() : '';
        }

        if ($element instanceof Table) {
            return $element->render();
        }

        if ($element instanceof AttachFilesButton) {
            $attributes = $element->getAttributes();
            if (!isset($attributes['class'])) {
                $attributes['class'] = '';
            }

            $attributes['class'] .= ' attach-action__input';

            $element->setAttributes($attributes);

            $label = $renderer->translate($element->getOption('value'));
            $hint = $renderer->translate($element->getOption('hint'));

            return sprintf(
                self::FILE_CHOOSE_WRAPPER,
                'attach-action__list',
                'attach-action',
                'attach-action__label',
                $label,
                parent::render($element),
                'attach-action__hint',
                $hint
            );
        }

        // If the element has errors, then add a class to the elements HTML
        if ($element->getMessages() !== []) {
            $element->setAttribute('class', $element->getAttribute('class') . ' error__input');
        }

        $elementErrorsHelper = $this->view->plugin('form_element_errors');
        assert($elementErrorsHelper instanceof FormElementErrors, 'Expected instance of ' . FormElementErrors::class);

        $markup = parent::render($element);
        $markup = $elementErrorsHelper->render($element) . $markup;
        $markup = $this->attachHint($element, $markup);

        return $this->attachBelowHint($element, $markup);
    }

    /**
     * Attach hint html to element html
     *
     * @param ElementInterface $element element
     * @param string           $markup  string
     *
     * @return string
     */
    private function attachHint($element, $markup)
    {
        if (!$element->getOption('hint')) {
            return $markup;
        }

        $hint = $this->getView()->translate($element->getOption('hint'));
        $position = $element->getOption('hint-position');
        $customClass = $element->getOption('hint-class');
        $class = $customClass ?? $this->hintClass;

        if ($position === 'below') {
            return sprintf(self::$format, $markup, $class, $hint);
        }

        return sprintf(self::$topFormat, $class, $hint, $markup);
    }

    /**
     * Attach hint html below element
     * This is same as setting a "hint" and "hint-position" = "below", but this option allows a hint
     * above and below the element
     *
     * @param ElementInterface $element element
     * @param string           $markup  string
     *
     * @return string
     */
    private function attachBelowHint($element, $markup)
    {
        if (!$element->getOption('hint-below')) {
            return $markup;
        }

        $hint = $this->getView()->translate($element->getOption('hint-below'));
        $customClass = $element->getOption('hint-below-class') ?? $element->getOption('hint-class');
        $class = $customClass ?? $this->hintClass;
        return sprintf(self::$format, $markup, $class, $hint);
    }
}
