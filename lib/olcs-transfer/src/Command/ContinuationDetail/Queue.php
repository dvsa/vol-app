<?php

/**
 * Queue letters
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\ContinuationDetail;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/continuation-detail/queue")
 * @Transfer\Method("POST")
 */
final class Queue extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Optional
     */
    protected $ids = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "que_typ_cpid_export_csv",
     *              "que_typ_ch_initial",
     *              "que_typ_ch_compare",
     *              "que_typ_cont_checklist"
     *          }
     *      }
     * )
     * @Transfer\Optional
     */
    protected $type;

    public function getIds()
    {
        return $this->ids;
    }

    public function getType()
    {
        return $this->type;
    }
}
