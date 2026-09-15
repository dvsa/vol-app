<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("licence-vehicle")
 */
class LicenceVehicleAppVar
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.receivedDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "default_date": "now"
     * })
     * @Form\Required(false)
     * @Form\Filter("DateSelectNullifier")
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $receivedDate;

    /**
     * @Form\Required(false)
     * @Form\Type("DateTimeSelect")
     * @Form\Attributes({"id":"specifiedDate"})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.specifiedDate",
     *     "create_empty_option": false,
     *     "render_delimiters": true,
     *     "field": "specifiedDate",
     *     "month_attributes": {"disabled":"disabled"},
     *     "year_attributes": {"disabled":"disabled"},
     *     "day_attributes": {"disabled":"disabled"},
     *     "hour_attributes": {"disabled":"disabled"},
     *     "minute_attributes": {"disabled":"disabled"},
     *     "display_every_minute": true
     * })
     * @Form\Filter("DateTimeSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format": "Y-m-d H:i:s"})
     */
    public $specifiedDate;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.removalDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "month_attributes": {"disabled":"disabled"},
     *     "year_attributes": {"disabled":"disabled"},
     *     "day_attributes": {"disabled":"disabled"}
     * })
     * @Form\Required(false)
     * @Form\Filter("DateSelectNullifier")
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $removalDate;

    /**
     * @Form\Attributes({"disabled":"disabled"})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.discNo"
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $discNo;
}
