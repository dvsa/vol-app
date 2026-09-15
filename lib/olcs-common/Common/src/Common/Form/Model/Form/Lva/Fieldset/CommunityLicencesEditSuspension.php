<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("community-licences-edit-suspension")
 */
class CommunityLicencesEditSuspension
{
    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium",  "multiple" : true})
     * @Form\Options({
     *     "label": "internal.community_licence.form.reasons",
     *     "category":"com_lic_sw_reason",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *      options={
     *          "messages":{Laminas\Validator\NotEmpty::IS_EMPTY:"internal.community_licence.form.licences_validation"}
     *      }
     * )
     */
    public $reasons;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $status;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;
}
