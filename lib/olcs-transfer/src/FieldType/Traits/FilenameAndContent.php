<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait FilenameAndContent
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 *
 */
trait FilenameAndContent
{
    /**
     * @Transfer\Filter("Laminas\Filter\PregReplace", options={"pattern": "/[^a-zA-Z0-9\-\_\.]+/", "replacement": ""})
     */
    protected $filename;

    protected $content;

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
