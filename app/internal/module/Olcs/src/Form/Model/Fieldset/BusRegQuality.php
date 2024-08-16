<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-reg-quality-fields")
 */
class BusRegQuality extends BusRegDetails
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Operate on part of a Quality Partnership Scheme current or future",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"isQualityPartnership",
     * })
     */
    public $isQualityPartnership;

    /**
     * @Form\Attributes({
     *      "id":"qualityPartnershipDetails",
     *      "class":"extra-long",
     *      "name":"qualityPartnershipDetails"
     * })
     * @Form\Options({
     *     "label": "Local transport authority or lead authority for Quality Partnership Scheme",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Filter("Laminas\Filter\StringTrim")
     *
     * @Form\Type("Textarea")
     * @Form\Required(false)
     *
     * @Form\Filter("Laminas\Filter\StringTrim")
     *
     * @Form\Validator("Laminas\Validator\StringLength",
     *      options={
     *          "max":4000
     *      }
     * )
     */
    public $qualityPartnershipDetails;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Will Quality Partnership Scheme facilities be used",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"qualityPartnershipFacilitiesUsed",
     * })
     */
    public $qualityPartnershipFacilitiesUsed;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Operate on part of a Quality Contract Scheme current or future",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })

     * @Form\Attributes({
     *      "id":"isQualityContract",
     * })
     */
    public $isQualityContract;

    /**
     * @Form\Attributes({
     *      "id":"qualityContractDetails",
     *      "class":"extra-long",
     *      "name":"qualityContractDetails"
     * })
     * @Form\Options({
     *     "label": "Local transport authority or lead authority for Quality Contract Scheme",
     *     "label_attributes": {
     *         "class": "extra-long"
     *     }
     * })
     *
     * @Form\Type("Textarea")
     * @Form\Required(false)
     *
     * @Form\Filter("Laminas\Filter\StringTrim")
     *
     * @Form\Validator("Laminas\Validator\StringLength",
     *      options={
     *          "max":4000
     *      }
     * )
     */
    public $qualityContractDetails;
}
