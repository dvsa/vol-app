<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\Types\PlainText;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 */
class EnableConversationsText
{
    /**
     * @Form\Type(\Common\Form\Elements\Types\PlainText::class)
     * @Form\Attributes({
     *     "value": "Are you sure you want to enable messaging?"
     * })
     */
    public ?PlainText $text = null;
}
