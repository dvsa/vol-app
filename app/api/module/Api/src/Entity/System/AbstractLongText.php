<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use JsonSerializable;

#[ORM\Table(name: 'long_text')]
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractLongText implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer', name: 'id')]
    protected $id;

    #[ORM\Column(type: 'string', name: 'reference_key', length: 128, nullable: false, unique: true)]
    protected $referenceKey;

    #[ORM\Column(type: 'string', name: 'locale', length: 5, nullable: false)]
    protected $locale;

    #[ORM\Column(type: 'string', name: 'page_name', length: 255, nullable: false)]
    protected $pageName;

    #[ORM\Column(type: 'string', name: 'description', length: 1024, nullable: true)]
    protected $description;

    /** EditorJS document, stored as authored. */
    #[ORM\Column(type: 'json', name: 'content', nullable: false)]
    protected $content;

    #[ORM\Column(type: 'smallint', name: 'version', nullable: false, options: ['default' => 1])]
    #[ORM\Version]
    protected $version = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferenceKey(): string
    {
        return $this->referenceKey;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getPageName(): string
    {
        return $this->pageName;
    }

    public function setPageName(string $pageName): self
    {
        $this->pageName = $pageName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getVersion(): int
    {
        return (int) $this->version;
    }

    #[\Override]
    public function __toString(): string
    {
        return sprintf('%s (%s)', (string) $this->referenceKey, (string) $this->locale);
    }
}
