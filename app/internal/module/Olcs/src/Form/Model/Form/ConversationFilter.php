<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Select;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("conversation_filter")
 * @Form\Attributes({
 *     "method": "get",
 *     "class": "filters form__filter"
 * })
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ConversationFilter
{
    /**
     * @Form\Attributes({"id": "status"})
     * @Form\Options({
     *     "label": "internal-licence-fees-status",
     *     "value_options": {
     *          "":"All",
     *          "open": "Open",
     *          "closed": "Closed"
     *     }
     * })
     * @Form\Type(Select::class)
     */
    public ?Select $status = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "Submit"
     * })
     * @Form\Type(Button::class)
     */
    public ?Button $filter = null;
}
