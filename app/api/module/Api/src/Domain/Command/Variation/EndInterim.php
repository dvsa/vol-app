<?php

/**
 * End interim for variations
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Variation;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * End interim for variations
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EndInterim extends AbstractCommand
{
    protected $licenceId;

    /**
     * @return mixed
     */
    public function getLicenceId()
    {
        return $this->licenceId;
    }
}
