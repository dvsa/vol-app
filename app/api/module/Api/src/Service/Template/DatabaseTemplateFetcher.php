<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Api\Entity\Template\Template;

class DatabaseTemplateFetcher
{
    public const COMPONENT_OFFSET_LOCALE = 0;
    public const COMPONENT_OFFSET_FORMAT = 1;
    public const COMPONENT_OFFSET_NAME = 2;

    /**
     * Create service instance
     *
     *
     * @return DatabaseTemplateFetcher
     */
    public function __construct(private readonly TemplateRepo $repo)
    {
    }

    /**
     * Fetch template from repository
     *
     * @param string $name
     *
     * @return Template
     *
     * @throws NotFoundException
     */
    public function fetch($name)
    {
        $components = explode('/', $name);
        if (count($components) != 3) {
            throw new NotFoundException('Incorrect number of path components');
        }

        return $this->repo->fetchByLocaleFormatName(
            $components[self::COMPONENT_OFFSET_LOCALE],
            $components[self::COMPONENT_OFFSET_FORMAT],
            $components[self::COMPONENT_OFFSET_NAME]
        );
    }
}
