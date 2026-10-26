<?php

/**
 * Post scoring email
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

class PostScoringEmail extends AbstractCommand
{
    /**
     * @var String
     */
    #[Transfer\Filter("Laminas\Filter\StringTrim")]
    protected $documentIdentifier;

    /**
     * @return string
     */
    public function getDocumentIdentifier()
    {
        return $this->documentIdentifier;
    }
}
