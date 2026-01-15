<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Doc;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractDocumentToDelete Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="document_to_delete")
 */
abstract class AbstractDocumentToDelete implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Document store id
     *
     * @var string
     *
     * @ORM\Column(type="string", name="document_store_id", length=255, nullable=false)
     */
    protected $documentStoreId = '';

    /**
     * Document id
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="document_id", nullable=false)
     */
    protected $documentId = 0;

    /**
     * Attempts
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="attempts", nullable=false, options={"default": 0})
     */
    protected $attempts = 0;

    /**
     * Process after date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="process_after_date", nullable=true)
     */
    protected $processAfterDate;


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return DocumentToDelete
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the document store id
     *
     * @param string $documentStoreId new value being set
     *
     * @return DocumentToDelete
     */
    public function setDocumentStoreId($documentStoreId)
    {
        $this->documentStoreId = $documentStoreId;

        return $this;
    }

    /**
     * Get the document store id
     *
     * @return string     */
    public function getDocumentStoreId()
    {
        return $this->documentStoreId;
    }

    /**
     * Set the document id
     *
     * @param int $documentId new value being set
     *
     * @return DocumentToDelete
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;

        return $this;
    }

    /**
     * Get the document id
     *
     * @return int     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * Set the attempts
     *
     * @param int $attempts new value being set
     *
     * @return DocumentToDelete
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Get the attempts
     *
     * @return int     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * Set the process after date
     *
     * @param \DateTime $processAfterDate new value being set
     *
     * @return DocumentToDelete
     */
    public function setProcessAfterDate($processAfterDate)
    {
        $this->processAfterDate = $processAfterDate;

        return $this;
    }

    /**
     * Get the process after date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getProcessAfterDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->processAfterDate);
        }

        return $this->processAfterDate;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}