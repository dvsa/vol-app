<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Base;

use Dvsa\Olcs\Api\Service\Document\Parser\ParserInterface;

/**
 * Abstract bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractBookmark
{
    /**
     * By default, all bookmarks are not assumed to have been preformatted.
     * This indicates that the parser should replace any relevant characters
     * such as newlines with its own representation (e.g. \par, <br>, etc)
     */
    public const PREFORMATTED = false;
    public const TYPE = null;

    /** @var string */
    protected $snippedPath;
    /** @var string */
    protected $token = null;
    /** @var ParserInterface */
    protected $parser = null;

    /**
     * Set Token
     *
     * @param string $token Token
     *
     * @return void;
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Is Static
     *
     * @return bool
     */
    public function isStatic()
    {
        return static::TYPE === 'static';
    }

    /**
     * Is Preformatted
     *
     * @return bool
     */
    public function isPreformatted()
    {
        return static::PREFORMATTED;
    }

    /**
     * Get Snippets
     *
     * @param string $className Class FQCN
     *
     * @return string
     */
    public function getSnippet($className = null)
    {
        if ($className === null) {
            $className = explode('\\', static::class);
            $className = array_pop($className);
        }

        $fileExt = $this->getParser()->getFileExtension();
        $path = ($this->snippedPath ?: __DIR__ . '/../Snippet/') . $className . '.' . $fileExt;

        return file_get_contents($path);
    }

    /**
     * Set Parser
     *
     * @param ParserInterface $parser Parser
     *
     * @return void
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
    }

    /**
     * Get Parser
     *
     * @return ParserInterface
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Set Snippet path
     *
     * @param string $path Path
     *
     * @return $this;
     */
    public function setSnippetPath($path)
    {
        $this->snippedPath = $path;
        return $this;
    }

    /**
     * Render
     *
     * @return string
     */
    abstract public function render();
}
