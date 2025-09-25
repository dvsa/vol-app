<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Domain\Command\EntityGenerator;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Optional;

/**
 * Generate entities domain command DTO
 */
class GenerateEntities extends AbstractCommand
{
    protected ?string $outputPath = null;
    protected bool $dryRun = false;
    protected bool $replace = false;
    protected ?string $configPath = null;

    /**
     * Create instance from array
     */
    public static function create(array $data = []): self
    {
        $instance = new self();

        if (isset($data['outputPath'])) {
            $instance->setOutputPath($data['outputPath']);
        }
        if (isset($data['dryRun'])) {
            $instance->setDryRun($data['dryRun']);
        }
        if (isset($data['replace'])) {
            $instance->setReplace($data['replace']);
        }
        if (isset($data['configPath'])) {
            $instance->setConfigPath($data['configPath']);
        }

        return $instance;
    }

    /**
     * Get output path
     */
    public function getOutputPath(): ?string
    {
        return $this->outputPath;
    }

    /**
     * Set output path
     */
    public function setOutputPath(?string $outputPath): self
    {
        $this->outputPath = $outputPath;
        return $this;
    }

    /**
     * Is dry run
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * Set dry run
     */
    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * Is replace
     */
    public function isReplace(): bool
    {
        return $this->replace;
    }

    /**
     * Set replace
     */
    public function setReplace(bool $replace): self
    {
        $this->replace = $replace;
        return $this;
    }

    /**
     * Get config path
     */
    public function getConfigPath(): ?string
    {
        return $this->configPath;
    }

    /**
     * Set config path
     */
    public function setConfigPath(?string $configPath): self
    {
        $this->configPath = $configPath;
        return $this;
    }
}
