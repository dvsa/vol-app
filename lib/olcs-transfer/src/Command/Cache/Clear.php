<?php

namespace Dvsa\Olcs\Transfer\Command\Cache;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Cache Clear Command DTO
 *
 * @Transfer\RouteName("backend/cache-clear")
 * @Transfer\Method("POST")
 */
class Clear extends AbstractCommand
{
    /**
     * @Transfer\Optional()
     */
    protected ?bool $flushAll = null;

    /**
     * @Transfer\Optional()
     */
    protected ?string $namespace = null;

    /**
     * @Transfer\Optional()
     */
    protected ?string $pattern = null;

    /**
     * @Transfer\Optional()
     */
    protected ?bool $dryRun = null;

    /**
     * @return bool|null
     */
    public function getFlushAll(): ?bool
    {
        return $this->flushAll;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return bool|null
     */
    public function getDryRun(): ?bool
    {
        return $this->dryRun;
    }
}
