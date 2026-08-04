<?php

namespace Common\Util;

/**
 * File Content
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FileContent implements \Stringable
{
    /**
     * FileContent constructor.
     *
     * @param string $fileName File name
     * @param string $mimeType Mime type
     */
    public function __construct(private $fileName, private $mimeType = null)
    {
    }

    /**
     * Get File Name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Get Mime Type
     *
     * @return null|string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * To string
     *
     * @return string
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->fileName;
    }
}
