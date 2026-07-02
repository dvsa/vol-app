<?php

declare(strict_types=1);

namespace Olcs\Service\WebDav;

use Sabre\DAV\Collection;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\INode;

class VirtualDirectory extends Collection
{
    /** @var INode[] */
    private array $children;

    /**
     * @param INode[] $children
     */
    public function __construct(
        private readonly string $name,
        array $children = [],
    ) {
        $this->children = $children;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function getChildren(): array
    {
        return $this->children;
    }

    #[\Override]
    public function getChild($name): INode
    {
        foreach ($this->children as $child) {
            if ($child->getName() === $name) {
                return $child;
            }
        }

        throw new NotFound('Node not found: ' . $name);
    }

    #[\Override]
    public function childExists($name): bool
    {
        foreach ($this->children as $child) {
            if ($child->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function createFile($name, $data = null): ?string
    {
        throw new Forbidden('Creating files via WebDAV is not permitted');
    }

    #[\Override]
    public function createDirectory($name): void
    {
        throw new Forbidden('Creating directories via WebDAV is not permitted');
    }
}
