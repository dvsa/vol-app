<?php

namespace Common\Form\Elements\Types;

use Common\Module;
use Common\View\Helper\DateTime as DateTimeViewHelper;

/**
 * Html DateTime Element
 */
class HtmlDateTime extends Html
{
    /**
     * Set the element value
     *
     * @param mixed $value Value
     */
    #[\Override]
    public function setValue($value): void
    {
        $this->value = empty($value)
            ? null
            : (new DateTimeViewHelper())->__invoke(new \DateTime($value), Module::$dateTimeSecFormat);
    }
}
