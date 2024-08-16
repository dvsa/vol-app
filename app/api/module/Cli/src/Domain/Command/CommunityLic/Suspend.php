<?php

namespace Dvsa\Olcs\Cli\Domain\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Suspend community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Suspend extends AbstractCommand
{
    protected $communityLicenceIds;

    /**
     * Get community licence ids
     *
     * @return array
     */
    public function getCommunityLicenceIds()
    {
        return $this->communityLicenceIds;
    }
}
