<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("PermitsRequired")
 * @Form\Attributes({
 *   "id" : "ecmt-number-of-permits",
 * })
 */
class PermitsRequired
{
    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     * @Form\Options({
     *     "label": "permits.page.no-of-permits.for.year",
     * })
     */
    public $topLabel;

    /**
     * @Form\Type("Common\Form\Elements\Custom\EcmtNoOfPermitsCombinedTotalElement")
     * @Form\Name("combinedTotalChecker")
     */
    public $combinedTotalChecker;
}
