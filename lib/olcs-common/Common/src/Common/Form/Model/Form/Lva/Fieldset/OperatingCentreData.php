<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Operating centre fieldset
 */
class OperatingCentreData
{
    /**
     * @Form\Attributes({"class":"tiny","pattern":"\d*","id":"noOfVehiclesRequired"})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation-sub-action.data.noOfVehiclesRequired",
     * })
     * @Form\Validator("Between", options={"min":0, "max":1000000})
     */
    public $noOfVehiclesRequired;

    /**
     * @Form\Attributes({"class":"tiny","pattern":"\d*","id":"noOfTrailersRequired"})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation-sub-action.data.noOfTrailersRequired",
     *     "error-message": "Your total number of trailers"
     * })
     * @Form\Validator("Between", options={"min":0, "max":1000000})
     */
    public $noOfTrailersRequired;

    /**
     * @Form\Name("permission")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\OperatingCentrePermission")
     * @Form\Options({"label":"lva-operating-centre-newspaper-permission"})
     */
    public $permission;

    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $guidance;
}
