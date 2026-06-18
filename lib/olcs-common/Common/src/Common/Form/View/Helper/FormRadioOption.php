<?php

namespace Common\Form\View\Helper;

use Common\Form\View\Helper\FormRadio;
use Laminas\Form\ElementInterface;

/**
 * Class FormRadioOption
 *
 * The decision to implement like this was to minimize the amount of code that would need to be copied from Laminas Helpers
 *
 * @package Common\Form\View\Helper
 */
class FormRadioOption extends FormRadio
{
    /**
     * Invoke helper
     *
     * @param ElementInterface|null $element       Radio element
     * @param mixed                 $labelPosition key of option to render, (strict standards do not allow changing
     *                                             method signature)
     */
    #[\Override]
    public function __invoke(ElementInterface $element = null, mixed $labelPosition = null)
    {
        if (!$element instanceof ElementInterface) {
            return $this;
        }

        $key = $labelPosition;

        // Only want to render one option, so store all options in tmp variable
        $savedOptions = $element->getValueOptions();
        $element->setValueOptions([$key => $savedOptions[$key]]);
        $rendered = $this->render($element);

        // put original value options back
        $element->setValueOptions($savedOptions);
        return $rendered;
    }
}
