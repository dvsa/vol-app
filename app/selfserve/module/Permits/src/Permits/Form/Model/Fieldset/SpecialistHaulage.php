<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SpecialistHaulage")
 */
class SpecialistHaulage
{
    /**
     * @Form\Name("SpecialistHaulage")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--specialist-haulier",
     *   "id" : "specialistHaulierRadio",
     * })
     * @Form\Options({
     *     "label": "",
     *     "short-label": "error.messages.restricted.countries",
     *     "label_attributes":{
     *          "class" : "form-control form-control--radio form-control--inline specialistRadio"
     *     },
     *     "value_options":{
     *          "1" : "Yes",
     *          "0" : "No"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $specialistHaulage = null;

    /**
     * @Form\Name("SectorList")
     * @Form\Attributes({
     *      "allowWrap":true,
     *      "data-container-class": "form-control__container",
     *      "id" : "sectorList",
     * })
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SectorList")
     */
    public $sectorList = null;

}
