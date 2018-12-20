<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Name("operator-licence-document")
 * @Form\Options({
 *     "radio-element": "licenceDocument"
 * })
 */
class OperatorLicenceDocument
{
    /**
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"class": "govuk-radios--conditional", "data-module":"radios"}
     * })
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
     *          },
     *          "lost": {
     *              "label": "licence.surrender.operator_licence.lost.label",
     *              "value": "lost",
     *              "attributes": {"data-aria-controls":"conditional-surrender-licence-lost", "id":"surrender-licence-lost"}
     *          },
     *          "stolen": {
     *              "label": "licence.surrender.operator_licence.stolen.label",
     *              "value": "stolen",
     *              "attributes": {"data-aria-controls":"conditional-surrender-licence-stolen", "id":"surrender-licence-stolen"}
     *          }
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $licenceDocument = null;

    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceInPossession")
     */
    public $possessionContent = null;

    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceLost")
     */
    public $lostContent;

    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceStolen")
     */
    public $stolenContent;
}
