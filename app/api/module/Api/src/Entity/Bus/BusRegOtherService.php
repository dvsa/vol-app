<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusRegOtherService Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="bus_reg_other_service",
 *    indexes={
 *        @ORM\Index(name="ix_bus_reg_other_service_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_bus_reg_other_service_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_bus_reg_other_service_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_bus_reg_other_service_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class BusRegOtherService extends AbstractBusRegOtherService
{
    public function __construct(BusReg $busReg, $serviceNo)
    {
        $this->busReg = $busReg;
        $this->serviceNo = $serviceNo;
    }

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedValues()
    {
        return [
            'busReg' => null
        ];
    }
}
