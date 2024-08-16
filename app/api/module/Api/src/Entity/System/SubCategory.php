<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubCategory Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="sub_category",
 *    indexes={
 *        @ORM\Index(name="ix_sub_category_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_sub_category_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_sub_category_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class SubCategory extends AbstractSubCategory
{
    // copy constants from old Common\Service\Data\CategoryDataService as required
    public const DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL = 112;
    public const DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL = 13;
    public const DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE = 74;
    public const DOC_SUB_CATEGORY_ADVERT_DIGITAL = 5;

    public const DOC_SUB_CATEGORY_APPLICATION_OTHER_DOCUMENTS = 21;
    public const DOC_SUB_CATEGORY_DISCS = 166;
    public const DOC_SUB_CATEGORY_COMMUNITY_LICENCE = 167;
    public const DOC_SUB_CATEGORY_COMMUNITY_LICENCE_COVER = 202;
    public const DOC_SUB_CATEGORY_SCANNING_SEPARATOR = 168;
    public const DOC_SUB_CATEGORY_MAINT_OTHER_DIGITAL = 190;
    public const DOC_SUB_CATEGORY_PSV_CERTIFIED_COPY = 207;

    public const DOC_SUB_CATEGORY_IRFO_CONTINUATIONS_AND_RENEWALS = 69;

    public const DOC_SUB_CATEGORY_PERMIT = 197;
    public const DOC_SUB_CATEGORY_PERMIT_COVERING_LETTER = 198;
    public const DOC_SUB_CATEGORY_PERMIT_APPLICATION = 200;

    public const TM_SUB_CATEGORY_DECLARED_UNFIT = 105;
    public const TM_SUB_CATEGORY_TM1_REMOVAL = 191;

    public const REPORT_SUB_CATEGORY_PSV = 192;
    public const REPORT_SUB_CATEGORY_GV = 193;
    public const REPORT_SUB_CATEGORY_PERMITS = 196;

    public const DOC_SUB_CATEGORY_REG_29_31_SECTION_57 = 81;
    public const DOC_SUB_CATEGORY_IN_OFFICE_REVOCATION = 195;
    public const DOC_SUB_CATEGORY_REPORT_LETTER = 203;
    public const DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE = 204;
    public const DOC_SUB_CATEGORY_POST_SCORING_EMAIL = 205;
    public const DOC_SUB_CATEGORY_MESSAGING = 213;
    public const DOC_SUB_CATEGORY_LICENCING_OTHER_DOCUMENTS = 79;
}
