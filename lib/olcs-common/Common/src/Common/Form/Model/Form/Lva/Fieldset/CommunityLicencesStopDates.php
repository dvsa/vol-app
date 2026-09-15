<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"id":"dates"})
 * @Form\Name("community-licences-data-stop-dates")
 */
class CommunityLicencesStopDates
{
    /**
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "internal.community_licence.form.start_date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\DateInFuture",
     *      options={
     *          "include_today": true,
     *          "use_time": false
     *      }
     * )
     */
    public $startDate;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "internal.community_licence.form.end_date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("DateCompare",
     *      options={
     *          "has_time": false,
     *          "allow_empty": true,
     *          "compare_to":"startDate",
     *          "operator":"gt",
     *          "compare_to_label":"Start date"
     *      }
     * )
     */
    public $endDate;
}
