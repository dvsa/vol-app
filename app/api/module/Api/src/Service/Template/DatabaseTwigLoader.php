<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

class DatabaseTwigLoader implements LoaderInterface
{
    /**
     * Create service instance
     *
     *
     * @return DatabaseTwigLoader
     */
    public function __construct(private readonly DatabaseTemplateFetcher $templateFetcher)
    {
    }

    /**
     * Returns the source context for a given template logical name.
     *
     * @param string $name The template logical name
     *
     * @return Source
     *
     * @throws LoaderError When $name is not found
     */
    public function getSourceContext($name)
    {
        try {
            $template = $this->templateFetcher->fetch($name);
        } catch (NotFoundException) {
            throw new LoaderError(sprintf('Template "%s" does not exist.', $name));
        }

        return new Source($template->getSource(), $name);
    }

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @param string $name The name of the template to check if we can load
     *
     * @return bool If the template source code is handled by this loader or not
     */
    public function exists($name)
    {
        $exists = true;
        try {
            $this->templateFetcher->fetch($name);
        } catch (NotFoundException) {
            $exists = false;
        }

        return $exists;
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     *
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        return $name;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int    $time Timestamp of the last modification time of the
     *                     cached template
     *
     * @return bool true if the template is fresh, false otherwise
     */
    public function isFresh($name, $time)
    {
        $template = $this->templateFetcher->fetch($name);
        $lastModified = $template->getLastModifiedOn(true);

        if (is_null($lastModified)) {
            return true;
        }

        return $lastModified->getTimestamp() <= $time;
    }
}
