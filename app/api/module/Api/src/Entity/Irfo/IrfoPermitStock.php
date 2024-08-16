<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry;

/**
 * IrfoPermitStock Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irfo_permit_stock",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_permit_stock_irfo_gv_permit_id", columns={"irfo_gv_permit_id"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_irfo_country_id", columns={"irfo_country_id"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_status", columns={"status"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_permit_stock_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_irfo_permit_stock_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class IrfoPermitStock extends AbstractIrfoPermitStock
{
    public const STATUS_RETURNED = 'irfo_perm_s_s_ret';
    public const STATUS_VOID = 'irfo_perm_s_s_void';
    public const STATUS_ISSUED = 'irfo_perm_s_s_issued';
    public const STATUS_IN_STOCK = 'irfo_perm_s_s_in_stock';

    public function __construct($serialNo, $validForYear, IrfoCountry $irfoCountry)
    {
        $this->serialNo = $serialNo;
        $this->validForYear = $validForYear;
        $this->irfoCountry = $irfoCountry;
    }
}
