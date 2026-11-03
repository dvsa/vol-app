<?php

/**
 * Bulk reprint community licences
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

abstract class AbstractBulkReprint extends AbstractCommand
{
    use User;

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
