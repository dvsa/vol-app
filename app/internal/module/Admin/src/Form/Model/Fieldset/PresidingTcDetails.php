<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Common\Form\Model\Form\Traits\VersionTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("presiding-tc")
 */
class PresidingTcDetails
{
    use VersionTrait;
    use IdTrait;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":70})
     */
    public $name = null;

    /**
     * @Form\Attributes({"id":"user","placeholder":""})
     * @Form\Options({
     *     "short-label": "User",
     *     "label": "User",
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "empty_option": "Unassigned",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $user = null;
}
