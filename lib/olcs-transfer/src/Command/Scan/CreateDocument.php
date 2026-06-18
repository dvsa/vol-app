<?php

/**
 * CreateDocument - used by the olcs-scanning service to add scanned documents
 */

namespace Dvsa\Olcs\Transfer\Command\Scan;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;

/**
 * @Transfer\RouteName("backend/scan/create-document")
 * @Transfer\Method("POST")
 */
final class CreateDocument extends AbstractCommand implements LoggerOmitContentInterface
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $scanId;

    protected $content;

    protected $filename;

    /**
     * @return int
     */
    public function getScanId()
    {
        return $this->scanId;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
