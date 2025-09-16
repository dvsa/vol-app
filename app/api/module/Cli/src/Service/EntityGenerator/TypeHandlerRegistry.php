<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TypeHandlerInterface;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TypeHandlerRegistryInterface;

/**
 * Registry for type handlers
 */
class TypeHandlerRegistry implements TypeHandlerRegistryInterface
{
    /** @var TypeHandlerInterface[] */
    private array $handlers = [];

    /** @var bool */
    private bool $sorted = false;

    public function register(TypeHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
        $this->sorted = false;
    }

    public function getHandler(ColumnMetadata $column, array $config = []): ?TypeHandlerInterface
    {
        $this->sortHandlers();

        foreach ($this->handlers as $handler) {
            if ($handler->supports($column, $config)) {
                return $handler;
            }
        }

        return null;
    }

    public function getHandlers(): array
    {
        $this->sortHandlers();
        return $this->handlers;
    }

    /**
     * Sort handlers by priority (highest first)
     */
    private function sortHandlers(): void
    {
        if ($this->sorted) {
            return;
        }

        usort($this->handlers, function (TypeHandlerInterface $a, TypeHandlerInterface $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        $this->sorted = true;
    }
}