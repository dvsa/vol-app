<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Traits;

use Dvsa\Olcs\Api\Service\Date;

/**
 * Date Helper Aware Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait DateHelperAwareTrait
{
    protected $dateHelper;

    public function setDateHelper(Date $helper)
    {
        $this->dateHelper = $helper;
    }

    public function getDateHelper()
    {
        return $this->dateHelper;
    }
}
