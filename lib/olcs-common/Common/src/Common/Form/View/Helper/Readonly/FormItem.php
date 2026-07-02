<?php

namespace Common\Form\View\Helper\Readonly;

use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\AbstractHelper;
use Common\Form\Elements;

/**
 * Class FormItem
 * @package Common\Form\View\Helper\Readonly
 */
class FormItem extends AbstractHelper
{
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
     * Render
     *
     * @param ElementInterface $element Element
     */
    public function render(ElementInterface $element): string
    {
        if (
            $element instanceof Elements\InputFilters\ActionButton
            || $element instanceof Elements\Types\AttachFilesButton
            || $element instanceof \Laminas\Form\Element\Submit
            || $element instanceof \Laminas\Form\Element\Hidden
            || $element instanceof \Laminas\Form\Element\Button
        ) {
            return '';
        }

        if ($element->getOption('disable_html_escape')) {
            return (string) $element->getValue();
        }

        $escapeHelper = $this->getEscapeHtmlHelper();
        return (string) $escapeHelper($element->getValue());
    }
}
