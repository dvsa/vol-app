<?php

/**
 * Generate letters
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\ContinuationDetail;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/continuation-detail/prepare-continuations")
 * @Transfer\Method("POST")
 */
final class PrepareContinuations extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Optional
     */
    protected $ids = [];

    public function getIds()
    {
        return $this->ids;
    }
}
