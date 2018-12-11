<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("operator-licence-document")
 */
class OperatorLicenceDocument
{
    /**
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"class": "govuk-radios--conditional", "data-module":"radios"}
     *     })
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class":"form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {
     *          "possession": {
     *              "label": "licence.surrender.operator_licence.possession.label",
     *              "value": "possession",
     *              "attributes": {"data-aria-controls":"conditional-surrender-licence-possession", "id":"surrender-licence-possession"},
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceInPossession",
     *                  "attributes": {"id":"conditional-surrender-licence-possession","class":"govuk-radios__conditional govuk-radios__conditional--hidden"}
     *              }
     *          },
     *          "lost": {
     *              "label": "licence.surrender.operator_licence.lost.label",
     *              "value": "lost",
     *              "attributes": {"data-aria-controls":"conditional-surrender-licence-lost", "id":"surrender-licence-lost"},
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceLost",
     *                  "attributes": {"id":"conditional-surrender-licence-lost","class":"govuk-radios__conditional govuk-radios__conditional--hidden"}
     *              },
     *          },
     *          "stolen": {
     *              "label": "licence.surrender.operator_licence.stolen.label",
     *              "value": "stolen",
     *              "attributes": {"data-aria-controls":"conditional-surrender-licence-stolen", "id":"surrender-licence-stolen"},
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceStolen",
     *                  "attributes": {"id":"conditional-surrender-licence-stolen","class":"govuk-radios__conditional govuk-radios__conditional--hidden"}
     *              }
     *          }
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $licenceDocumentOptions = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButton")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
