<?php

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Overwrite the content of an existing document in the document store.
 *
 * @Transfer\RouteName("backend/document/overwrite-content")
 * @Transfer\Method("POST")
 */
final class OverwriteContent extends AbstractCommand implements LoggerOmitContentInterface
{
    use Identity;

    protected $content;

    public function getContent()
    {
        return $this->content;
    }
}
