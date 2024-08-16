<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Doctrine\ORM\Mapping as ORM;

/**
 * IrfoPsvAuthType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irfo_psv_auth_type",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_psv_auth_type_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_type_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_irfo_psv_auth_type_irfo_fee_type", columns={"irfo_fee_type"})
 *    }
 * )
 */
class IrfoPsvAuthType extends AbstractIrfoPsvAuthType
{
    public const IRFO_FEE_TYPE_EU_REG_17 = 'irfo_psv_eu_reg_17';
    public const IRFO_FEE_TYPE_EU_REG_19A = 'irfo_psv_eu_reg_19A';
    public const IRFO_FEE_TYPE_NON_EU_OCCASIONAL_19 = 'irfo_psv_non_eu_occasional_19';
    public const IRFO_FEE_TYPE_NON_EU_REG_18 = 'irfo_psv_non_eu_reg_18';
    public const IRFO_FEE_TYPE_NON_EU_REG_19 = 'irfo_psv_non_eu_reg_19';
    public const IRFO_FEE_TYPE_OWN_AC_21 = 'irfo_psv_own_ac_21';
    public const IRFO_FEE_TYPE_SHUTTLE_OPERATOR_20 = 'irfo_psv_shuttle_operator_20';
}
