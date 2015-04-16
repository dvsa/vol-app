<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-affect-immediate")
 */
class LicenceStatusDecisionAffectImmediate
{
    /**
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "licence-status.curtailment.immediate-affect",
     *      "value_options":{
     *          "Y":"Yes",
     *          "N":"No"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *     "value": "Y"
     * })
     */
    public $immediateAffect;
}
