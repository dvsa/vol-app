<?php

/**
 * Create Correspondence Record
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;

/**
 * Create Correspondence Record
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateCorrespondenceRecord extends AbstractCommand
{
    use Licence;

    public const TYPE_STANDARD = 'standard';
    public const TYPE_CONTINUATION = 'continuation';
    public const TYPE_CLOSED_CONVERSATION = 'conversation';

    protected $document;

    protected $type = self::TYPE_STANDARD;

    /**
     * @return mixed
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
