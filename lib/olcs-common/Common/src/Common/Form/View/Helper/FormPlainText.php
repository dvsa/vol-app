<?php

/**
 *  PlainText Form View Helper
 *
 *  @author Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace Common\Form\View\Helper;

use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\ElementInterface;

/**
 *  PlainText Form View Helper
 *
 *  @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class FormPlainText extends AbstractHelper
{
    /**
     * @return mixed
     */
    public function __invoke(ElementInterface $element = null)
    {
        return $this->render($element);
    }

    public function render(ElementInterface $element): string
    {
        return $element->getValue() ?
            $this->getView()->translate($element->getValue()) : '';
    }
}
