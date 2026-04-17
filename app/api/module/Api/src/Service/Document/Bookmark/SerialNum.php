<?php

/**
 * Serial Num
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\DateHelperAwareInterface;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Traits\DateHelperAwareTrait;

/**
 * Serial Num
 *
 * This bookmark generates a licence number in the format of:
 *
 * <code>
 *  <licence number> <current date/time>
 * </code>
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class SerialNum extends LicenceNumber implements DateHelperAwareInterface
{
    use DateHelperAwareTrait;

    /**
     * Return the serial number as a string in the format of "licenceNo currentDateTime"
     *
     * @return string
     */
    #[\Override]
    public function render()
    {
        return parent::render() . ' ' . $this->getDateHelper()->getDate('d/m/Y H:i:s');
    }
}
