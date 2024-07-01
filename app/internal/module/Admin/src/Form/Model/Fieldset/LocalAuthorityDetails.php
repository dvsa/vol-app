<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 */
class LocalAuthorityDetails
{
    use IdTrait;

    /**
     * @Form\Attributes({"id":"naptanCode", "readonly":"true", "disabled":"disabled"})
     * @Form\Options({
     *     "label": "NAPTAN Code (Ready only)",
     * })
     */
    public $naptanCode = null;

    /**
     * @Form\Attributes({"id":"txcName", "readonly":"true", "disabled":"disabled"})
     * @Form\Options({
     *     "label": "TXC Name (Ready only)",
     * })
     */
    public $txcName = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"emailAddress","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"Email Address"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"LTA Descriptive Name"})
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $description = null;
}
