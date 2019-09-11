<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("PermitsRequired")
 */
class PermitsRequired
{
    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     * @Form\Options({
     *     "label": "permits.page.no-of-permits.for.year",
     * })
     */
    public $topLabel;

    /**
     * @Form\Type("Common\Form\Elements\Custom\NoOfPermitsCombinedTotalElement")
     * @Form\Name("combinedTotalChecker")
     * @Form\Options({
     *     "maxPermitted": 0,
     * })
     */
    public $combinedTotalChecker;
}
