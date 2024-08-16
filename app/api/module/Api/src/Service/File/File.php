<?php

namespace Dvsa\Olcs\Api\Service\File;

/**
 * File
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @deprecated Please use \Dvsa\Olcs\DocumentShare\Data\Object\File
 */
class File
{
    /** @var string */
    protected $identifier;
    /** @var string */
    protected $name;
    /** @var string */
    protected $path;
    /** @var  string */
    private $mimeType;
    /** @var  int */
    protected $size;
    /** @var string */
    protected $content;

    /**
     * Setter for identifier
     *
     * @param string $identifier Identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Getter for identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Setter for name
     *
     * @param string $name Name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for path
     *
     * @param string $path Path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Getter for path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Getter for size
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->size === null) {
            $this->size = strlen($this->content);
        }

        return $this->size;
    }

    /**
     * Setter for content
     *
     * @param string|array $content Content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->size = null;
        $this->mimeType = null;

        if (is_array($content)) {
            $this->content = file_get_contents($content['tmp_name']);
        } else {
            $this->content = $content;
        }

        return $this;
    }

    /**
     * Getter for content
     *
     * @return int
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get Mime type
     *
     * @return null|string
     * @throws \Exception
     */
    public function getMimeType()
    {
        if ($this->mimeType === null) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'temp');
            try {
                file_put_contents($tmpFile, $this->content);

                $this->mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpFile);
            } catch (\Exception $e) {
                throw $e;
            } finally {
                if (is_file($tmpFile)) {
                    unlink($tmpFile);
                }
            }
        }

        return $this->mimeType;
    }
}
