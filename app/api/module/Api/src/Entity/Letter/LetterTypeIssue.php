<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTypeIssue Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_type_issue",
 *    indexes={
 *        @ORM\Index(name="ix_letter_type_issue_letter_issue_version_id", columns={"letter_issue_version_id"}),
 *        @ORM\Index(name="ix_letter_type_issue_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_type_issue_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterTypeIssue extends AbstractLetterTypeIssue
{
    /**
     * Get issue heading
     *
     * @return string
     */
    public function getIssueHeading()
    {
        return $this->letterIssueVersion->getHeading();
    }

    /**
     * Get issue category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\Category
     */
    public function getIssueCategory()
    {
        return $this->letterIssueVersion->getCategory();
    }

    /**
     * Get issue sub category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SubCategory|null
     */
    public function getIssueSubCategory()
    {
        return $this->letterIssueVersion->getSubCategory();
    }
}