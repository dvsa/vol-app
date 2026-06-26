<?php

namespace Common\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\View\Renderer\PhpRenderer;

/**
 * Helper to render the GDS horizontal radio button pattern
 */
class FormRadioHorizontal extends \Laminas\Form\View\Helper\FormCollection
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
        return $view->render(
            'partials/form/radio-horizontal',
            array_merge($view->vars()->getArrayCopy(), ['element' => $element])
        );
    }
}
