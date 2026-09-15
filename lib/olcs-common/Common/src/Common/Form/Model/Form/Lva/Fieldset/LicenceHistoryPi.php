<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Licence History Pi
 */
class LicenceHistoryPi
{
    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label":"application_previous-history_licence-history_prevBeenAtPi",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "legend-attributes": {"class": "form-element__label"},
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "error-message":"licenceHistoryPi_prevBeenAtPi-error"
     * })
     * @Form\Type("radio")
     * @Form\Validator("Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     options={"table": "prevBeenAtPi-table"}
     *)
     */
    public $prevBeenAtPi;

    /**
     * @Form\Name("prevBeenAtPi-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *      "id":"prevBeenAtPi",
     *      "class": "help__text help__text--removePadding"
     * })
     */
    public $prevBeenAtPiTable;
}
