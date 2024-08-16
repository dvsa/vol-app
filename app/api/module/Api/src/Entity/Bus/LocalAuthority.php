<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\ORM\Mapping as ORM;

/**
 * LocalAuthority Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="local_authority",
 *    indexes={
 *        @ORM\Index(name="ix_local_authority_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_local_authority_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_local_authority_traffic_area_id", columns={"traffic_area_id"})
 *    }
 * )
 */
class LocalAuthority extends AbstractLocalAuthority
{
    public function update(string $description, ?string $emailAddress): LocalAuthority
    {
        $this->description = $description;
        $this->emailAddress = $emailAddress;
        return $this;
    }
}
