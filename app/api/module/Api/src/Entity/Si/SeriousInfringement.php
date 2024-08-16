<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;

/**
 * SeriousInfringement Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="serious_infringement",
 *    indexes={
 *        @ORM\Index(name="ix_serious_infringement_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_serious_infringement_si_category_id", columns={"si_category_id"}),
 *        @ORM\Index(name="ix_serious_infringement_si_category_type_id", columns={"si_category_type_id"}),
 *        @ORM\Index(name="ix_serious_infringement_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_serious_infringement_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class SeriousInfringement extends AbstractSeriousInfringement
{
    /**
     * @param \DateTime $checkDate
     * @param \DateTime $infringementDate
     * @param SiCategoryEntity $siCategory
     * @param SiCategoryTypeEntity $siCategoryType
     * @return void
     */
    public function __construct(
        CaseEntity $case,
        \DateTime $checkDate,
        \DateTime $infringementDate,
        SiCategoryEntity $siCategory,
        SiCategoryTypeEntity $siCategoryType
    ) {
        parent::__construct();

        $this->case = $case;
        $this->update($checkDate, $infringementDate, $siCategory, $siCategoryType);
    }

    /**
     * Update
     *
     * @return void
     */
    public function update(
        \DateTime $checkDate,
        \DateTime $infringementDate,
        SiCategoryEntity $siCategory,
        SiCategoryTypeEntity $siCategoryType
    ) {
        $this->checkDate = $checkDate;
        $this->infringementDate = $infringementDate;
        $this->siCategory = $siCategory;
        $this->siCategoryType = $siCategoryType;
    }

    /**
     * Whether there is a response set for the serious infringement
     *
     * @return bool
     */
    public function responseSet()
    {
        return (bool)!$this->appliedPenalties->isEmpty();
    }

    /**
     * Calculated values to be added to a bundle
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return ['responseSet' => $this->responseSet()];
    }
}
