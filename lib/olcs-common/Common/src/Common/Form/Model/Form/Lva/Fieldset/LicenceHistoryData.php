<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Licence History Data
 */
class LicenceHistoryData
{
    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label":"application_previous-history_licence-history_prevHasLicence",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "legend-attributes": {"class": "form-element__label"},
     *     "error-message":"licenceHistoryData_prevHasLicence-error",
     *     "value_options": {"Y":"Yes", "N":"No"}
     * })
     * @Form\Type("radio")
     * @Form\Validator("Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     options={"table": "prevHasLicence-table"}
     *)
     * @Form\Flags({"priority": -10})
     */
    public $prevHasLicence;

    /**
     * @Form\Name("prevHasLicence-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *      "id":"prevHasLicence",
     *      "class": "help__text help__text--removePadding"
     * })
     * @Form\Flags({"priority": -20})
     */
    public $prevHasLicenceTable;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "application_previous-history_licence-history_prevHadLicence",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "legend-attributes": {"class": "form-element__label"},
     *     "error-message": "licenceHistoryData_prevHadLicence-error",
     *     "value_options": {"Y":"Yes", "N":"No"}
     * })
     * @Form\Type("radio")
     * @Form\Validator("Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     options={"table": "prevHadLicence-table"}
     *)
     * @Form\Flags({"priority": -30})
     */
    public $prevHadLicence;

    /**
     * @Form\Name("prevHadLicence-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *      "id":"prevHadLicence",
     *      "class": "help__text help__text--removePadding"
     * })
     * @Form\Flags({"priority": -40})
     */
    public $prevHadLicenceTable;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label":"application_previous-history_licence-history_prevBeenDisqualifiedTc",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "legend-attributes": {"class": "form-element__label"},
     *     "error-message":"licenceHistoryData_prevBeenDisqualifiedTc-error",
     *     "value_options": {"Y":"Yes", "N":"No"}
     * })
     * @Form\Type("radio")
     * @Form\Validator("Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     options={"table": "prevBeenDisqualifiedTc-table"}
     *)
     * @Form\Flags({"priority": -50})
     */
    public $prevBeenDisqualifiedTc;

    /**
     * @Form\Name("prevBeenDisqualifiedTc-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *      "id":"prevBeenDisqualifiedTc",
     *      "class": "help__text help__text--removePadding"
     * })
     * @Form\Flags({"priority": -60})
     */
    public $prevBeenDisqualifiedTcTable;
}
