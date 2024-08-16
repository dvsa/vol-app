<?php

/**
 * Generate IRHP Permit Documents
 *
 * @author Henry White <henry.white@capgemini.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * GeneratePermitDocuments
 *
 * @author Henry White <henry.white@capgemini.com>
 */
final class GeneratePermitDocuments extends AbstractCommand
{
    protected $ids = [];

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
