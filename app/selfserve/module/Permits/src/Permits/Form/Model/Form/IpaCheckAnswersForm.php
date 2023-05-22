<?php
namespace Permits\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("CheckAnswers")
 * @Form\Attributes({
 *     "method":"POST",
 *     "class":"govuk-button-group",
 * })
 * @Form\Type("Common\Form\Form")
 */

class IpaCheckAnswersForm
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "id":"submitbutton",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"confirm-and-return-to-overview",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;
}
