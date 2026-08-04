<?php

namespace Common\Data\Mapper\Lva\TransportManager\Sections;

/**
 * Class Responsibilities
 *
 * @package Common\Data\Mapper\Lva\TransportManager\Sections
 */
class Responsibilities extends AbstractSection implements TransportManagerSectionInterface
{
    use SectionSerializeTrait;

    private $typeOfTransportManager;

    private $ownerTm;

    public function setOwnerTm(mixed $ownerTm): void
    {
        $this->ownerTm = $ownerTm;
    }

    public function setTypeOfTransportManager(mixed $typeOfTransportManager): void
    {
        $this->typeOfTransportManager = $typeOfTransportManager;
    }

    /**
     * populate
     *
     * @return static
     */
    #[\Override]
    public function populate(array $transportManagerApplication)
    {
        $this->setOwnerTm($transportManagerApplication['isOwner']);

        $this->setTypeOfTransportManager($transportManagerApplication['tmType']['description']);

        return $this;
    }
}
