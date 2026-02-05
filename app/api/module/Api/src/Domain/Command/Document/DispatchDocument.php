<?php

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Dispatch Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DispatchDocument extends CreateDocumentSpecific
{
    use FieldType\PrintOptional;

    protected $user;

    /**
     * Get user id
     *
     * @return int
     */
    #[\Override]
    public function getUser()
    {
        return $this->user;
    }
}
