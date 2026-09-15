<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("submissionSections")
 * @Form\Options({"label":"Select one or more categories"})
 */
class SubmissionSections
{
    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "Compliance",
     *     "category": "case_categories_compliance"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $compliance;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "TM",
     *     "category": "case_categories_tm"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $tm;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "Licensing application",
     *     "category": "case_categories_app"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $app;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "Licence referral",
     *     "category": "case_categories_referral"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $referral;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "Bus registration",
     *     "category": "case_categories_bus"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $bus;
}
