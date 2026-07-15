<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("community-licences-stop")
 */
class CommunityLicencesStop
{
    /**
     * @Form\Options({
     *     "label": "internal.community_licence.form.change_status_to",
     *     "value_options": {
     *          "Y": "internal.community_licence.form.suspended",
     *          "N": "internal.community_licence.form.withdrawn"
     *      },
     * })
     * @Form\Type("radio")
     */
    public $type;

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
    public $reason;
}
