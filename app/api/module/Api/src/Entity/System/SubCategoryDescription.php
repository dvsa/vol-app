<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubCategoryDescription Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="sub_category_description",
 *    indexes={
 *        @ORM\Index(name="ix_sub_category_description_sub_category_id", columns={"sub_category_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_sub_category_description", columns={"sub_category_id","description"})
 *    }
 * )
 */
class SubCategoryDescription extends AbstractSubCategoryDescription
{
    public const CONTINUATIONS_AND_RENEWALS_LICENCE_CHECKLIST = 112;
}
