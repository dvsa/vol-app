<?php

namespace Dvsa\Olcs\Cli\Domain\Command;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Cache Clear Command DTO
 *
 * @author OLCS Team
 */
class CacheClear extends AbstractCommand
{
    protected ?bool $flushAll = null;
    protected ?string $namespace = null;
    protected ?string $pattern = null;
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
