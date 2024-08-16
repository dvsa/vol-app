<?php

namespace Admin\Form\Model\Fieldset;

use Common\Form\Model\Form\Traits\IdTrait;
use Common\Form\Model\Form\Traits\VersionTrait;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("team-details")
 */
class TaskAllocationRule
{
    use VersionTrait;
    use IdTrait;

    /**
     * @Form\Attributes({"value": "Criteria:"})
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $headingCriteria = null;

    /**
     * @Form\Attributes({"id":"category","placeholder":""})
     * @Form\Options({
     *     "short-label": "Category",
     *     "label": "Category",
     *     "service_name": "Olcs\Service\Data\Category",
     *     "context": {
     *       "isTaskCategory": "Y"
     *     },
     *     "empty_option": "Please select"
     * })
     * @Form\Required(true)
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"id":"subCategory","placeholder":""})
     * @Form\Options({
     *     "short-label": "Sub category",
     *     "label": "Sub category",
     *     "service_name": "Olcs\Service\Data\SubCategory",
     *     "context": {
     *       "isTaskCategory": "Y"
     *     },
     *     "empty_option": "Not applicable"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $subCategory = null;

    /**
     * @Form\Name("goodsOrPsv")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "short-label": "Operator type",
     *      "fieldset-attributes": {
     *          "id": "fieldset-goodsOrPsv",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
     *      "label": "Operator type",
     *      "value_options":{
     *          "lcat_gv":"Goods",
     *          "lcat_psv":"PSV",
     *          "na":"N/A"
     *      }
     * })
     * @Form\Type("Radio")
     * @Form\Required(true)
     */
    public $goodsOrPsv = null;

    /**
     * @Form\Name("isMlh")
     * @Form\Type("Radio")
     * @Form\Attributes({"id": "is-mlh"})
     * @Form\Options({
     *      "short-label": "Is MLH",
     *      "fieldset-attributes": {
     *          "id": "fieldset-isMlh",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "isMlh",
     *      "label": "Is MLH",
     *      "value_options":{
     *          "Y":"Yes",
     *          "N":"No"
     *      }
     * })
     * @Form\Required(false)
     */
    public $isMlh = null;

    /**
     * @Form\Attributes({"id":"trafficArea","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "short-label": "Traffic area",
     *     "label": "Traffic area",
     *     "service_name": "Common\Service\Data\TrafficArea",
     *     "empty_option": "Not applicable",
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $trafficArea = null;

    /**
     * @Form\Attributes({"value": "Assign to:"})
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $headingAssignTo = null;

    /**
     * @Form\Type("Hidden")
     */
    public $teamId = null;

    /**
     * @Form\Attributes({"id":"team","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "short-label": "Team",
     *     "label": "Team",
     *     "service_name": "Olcs\Service\Data\Team",
     *     "empty_option": "Please select",
     * })
     *
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     */
    public $team = null;

    /**
     * @Form\Attributes({"id":"user","placeholder":""})
     * @Form\Options({
     *     "short-label": "User",
     *     "label": "User",
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "empty_option": "Unassigned",
     *     "extra_option": {"alpha-split": "Alpha split"},
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $user = null;

    /**
     * @Form\Name("taskAlphaSplit")
     * @Form\Attributes({"id":"taskAlphaSplit"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $taskAlphaSplit = null;
}
