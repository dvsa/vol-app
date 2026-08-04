<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("AdvertisementsAdPlacedNow")
 */
class AdvertisementsAdPlacedNow
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"long","id":"adPlacedIn"})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation-sub-action.advertisements.adPlacedIn"
     * })
     * @Form\Type("Text")
     */
    public $adPlacedIn;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"adPlacedDate", "data-container-class": "adPlacedDate"})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation-sub-action.advertisements.adPlacedDate",
     *     "legend-attributes": {"class": "form-element__label"},
     *     "label_attributes": {"class": "form-element__label"},
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldset-attributes":{
     *          "id":"adPlacedDate_day"
     *      }
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Type("DateSelect")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $adPlacedDate;

    /**
     * @Form\Name("file")
     * @Form\Attributes({"id":"file"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation-sub-action.advertisements.file",
     *     "label_attributes": {"class": "form-element__label"}
     * })
     */
    public $file;
}
