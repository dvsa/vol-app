<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces;

use Dvsa\Olcs\Api\Service\Date;

/**
 * Date Helper Aware Interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
interface DateHelperAwareInterface
{
    public function setDateHelper(Date $helper);

    public function getDateHelper();
}
