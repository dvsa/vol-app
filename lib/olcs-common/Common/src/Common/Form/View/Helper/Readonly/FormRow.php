<?php

namespace Common\Form\View\Helper\Readonly;

use Common\Form\Elements;
use Common\Form\Elements\Types\Html;
use Common\Form\Elements\Types\HtmlTranslated;
use Common\Form\Elements\Types\Table;
use Laminas\Form\Element as LaminasElement;
use Laminas\Form\ElementInterface;
use Laminas\Form\LabelAwareInterface;
use Laminas\Form\View\Helper\AbstractHelper;

/**
 * Class FormRow
 * @package Common\Form\View\Helper\Readonly
 */
class FormRow extends AbstractHelper
{
    /**
     * @var string
     */
    protected $defaultHelper = 'readonlyformitem';

    /**
     * @var array
     */
    protected $classMap = [
        \Laminas\Form\Element\Radio::class => 'readonlyformselect',
        \Laminas\Form\Element\Select::class => 'readonlyformselect',
        \Laminas\Form\Element\DateSelect::class => 'readonlyformdateselect',
        \Common\Form\Elements\Types\Table::class => 'readonlyformtable',
    ];

    /**
     * @var string
     */
    private static $format = '<li class="%s"><dt>%s</dt><dd>%s</dd></li>';

    private static $formatWithoutLabel = '<li class="%s">%s</li>';

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param ElementInterface|null $element Element
     *
     * @return static|string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element instanceof \Laminas\Form\ElementInterface) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Retrieve the FormElement helper
     *
     * @param ElementInterface $element Element
     *
     * @return Callable
     */
    protected function getElementHelper(ElementInterface $element)
    {
        foreach ($this->classMap as $class => $plugin) {
            if ($element instanceof $class) {
                return $this->getView()->plugin($plugin);
            }
        }

        return $this->getView()->plugin($this->defaultHelper);
    }

    /**
     * Render element
     *
     * @param ElementInterface $element Element
     */
    public function render(ElementInterface $element): string
    {
        /** @var \Common\Form\View\Helper\FormElement $defElmHlpr */
        $defElmHlpr = $this->getView()->plugin('FormElement');

        if (
            $element instanceof LaminasElement\Csrf
            || (
                $element instanceof Elements\InputFilters\ActionButton
                && $element->getOption('keepForReadOnly') === true
            )
        ) {
            return $defElmHlpr->render($element);
        }

        if (
            $element instanceof Elements\InputFilters\ActionButton
            || $element instanceof Elements\Types\AttachFilesButton
        ) {
            return '';
        }

        if (
            in_array($element->getAttribute('type'), ['hidden', 'submit']) ||
            $element instanceof LaminasElement\Button ||
            $element->getOption('remove_if_readonly')
        ) {
            //bail early if we don't want to display this type of element
            return '';
        }

        if ($element instanceof Table) {
            // we dont want Tables to be rendered with a label / value so just return the result of the view helper
            $elementHelper = $this->getElementHelper($element);
            return  $elementHelper($element);
        }

        $cssClass = $this->getClass($element);

        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $elementHelper = $this->getElementHelper($element);
        $label = $element->getLabel();

        $translator = $this->getTranslator();
        if ($translator !== null && ($label !== null && $label !== '' && $label !== '0')) {
            $label = $translator->translate($label, $this->getTranslatorTextDomain());
        }

        if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
            $label = $escapeHtmlHelper($label);
        }

        $value = $element instanceof Html ? $defElmHlpr->render($element) : $elementHelper($element);

        if ($translator !== null) {
            if ($element instanceof HtmlTranslated) {
                $value = $defElmHlpr->render($element);
            } elseif (is_string($value) && !($element instanceof Html)) {
                $value = $translator->translate($value);
            }
        }

        if ($element instanceof HtmlTranslated && $label === null) {
            return sprintf(self::$formatWithoutLabel, $cssClass, $value);
        }

        return sprintf(self::$format, $cssClass, $label, $value);
    }

    /**
     * Get Class
     *
     * @param ElementInterface $element Element
     *
     * @return string
     */
    public function getClass($element)
    {
        return'definition-list__item readonly';
    }
}
