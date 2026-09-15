<?php

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Super-admin S3 bucket browser: overwrite/create an object at a RAW S3 key (not a document id).
 * Deliberately decoupled from the document table — does not use the Identity trait. The file bytes
 * arrive as a multipart `content` field; LoggerOmitContentInterface keeps them out of the logs.
 *
 * @Transfer\RouteName("backend/document/bucket-browser/overwrite")
 * @Transfer\Method("POST")
 */
final class BucketBrowserOverwrite extends AbstractCommand implements LoggerOmitContentInterface
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $key;

    protected $content;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function getContent()
    {
        return $this->content;
    }
}
