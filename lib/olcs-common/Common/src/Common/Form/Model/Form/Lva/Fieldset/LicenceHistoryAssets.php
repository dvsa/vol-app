<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Licence History Assets
 */
class LicenceHistoryAssets
{
    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "application_previous-history_licence-history_prevPurchasedAssets",
     *     "error-message": "licenceHistoryAssets_prevPurchasedAssets-error",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "legend-attributes": {"class": "form-element__label"},
     *     "value_options": {"Y":"Yes", "N":"No"}
     * })
     * @Form\Type("radio")
     * @Form\Validator("Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     options={"table": "prevPurchasedAssets-table"}
     *)
     */
    public $prevPurchasedAssets;

    /**
     * @Form\Name("prevPurchasedAssets-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *      "id":"prevPurchasedAssets",
     *      "class": "help__text help__text--removePadding"
     * })
     */
    public $prevPurchasedAssetsTable;
}
