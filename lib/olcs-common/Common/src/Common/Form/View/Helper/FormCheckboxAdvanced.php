<?php

namespace Common\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\View\Renderer\PhpRenderer;

/**
 * Helper to render the advanced checkbox control for continuation
 */
class FormCheckboxAdvanced extends \Laminas\Form\View\Helper\FormCollection
{
    /**
     * Render
     *
     * @param ElementInterface $element Element to render
     *
     * @return string HTML
     */
    #[\Override]
    public function render(ElementInterface $element): string
    {
        $view = $this->view;
        return $view->partial(
            'partials/form/checkbox-advanced',
            ['element' => $element, 'content' => $element->getOption('content'), 'data' => $view->data]
        );
    }
}
