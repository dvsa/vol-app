<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * TradingName Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="trading_name",
 *    indexes={
 *        @ORM\Index(name="ix_trading_name_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_trading_name_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_trading_name_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_trading_name_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_trading_name_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class TradingName extends AbstractTradingName
{
    public function __construct($name, Organisation $organisation = null)
    {
        $this->setOrganisation($organisation);
        $this->setName($name);
    }

    protected function getCalculatedValues()
    {
        return ['organisation' => null, 'licence' => null];
    }
}
