<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("operator-licence-document")
 */
class OperatorLicenceDocument
{
    /**
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class":"form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {
     *          "posession": {
     *              "label": "In your possession",
     *              "value": "possession",
     *              "hint" : "You'll have a user ID if you've registered for Self Assessment or filed a tax return online before.",
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceInPossession",
     *                  "attributes": {"id":"surrender-licence-possession","class":"govuk-radios__conditional"}
     *              }
     *          },
     *          "lost": {
     *              "label": "Lost",
     *              "value": "lost",
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceLost",
     *                  "attributes": {"id":"surrender-licence-lost","class":"govuk-radios__conditional"}
     *              }
     *          },
     *          "stolen": {
     *              "label": "Stolen",
     *              "value": "stolen",
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceStolen",
     *                  "attributes": {"id":"surrender-licence-stolen","class":"govuk-radios__conditional"}
     *              }
     *          }
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $licenceDocumentOptions = null;
}