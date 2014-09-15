<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("hearing")
 * @Form\Options({"label":"Hearing","id":"hearing_fieldset"})
 */
class Hearing
{
    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Hearing date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\HearingDateHasTime")
     */
    public $hearingDate = null;

    /**
     * @Form\Attributes({"min":"07:00","max":"19:00","step":"300"})
     * @Form\Options({"label":"Hearing time
     * (hh:mm)","create_empty_option":true,"format":"h:i"})
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\HearingTimeHasDate")
     */
    public $hearingTime = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Hearing location",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SelectEmpty")
     */
    public $piVenue = null;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Other hearing location"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $piVenueOther = null;
}
