<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterTypeIssue Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_type_issue")
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
