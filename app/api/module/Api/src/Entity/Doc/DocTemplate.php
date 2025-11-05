<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\DeletableInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * DocTemplate Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="doc_template",
 *    indexes={
 *        @ORM\Index(name="ix_doc_template_sub_category_id", columns={"sub_category_id"}),
 *        @ORM\Index(name="ix_doc_template_document_id", columns={"document_id"}),
 *        @ORM\Index(name="ix_doc_template_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_doc_template_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_doc_template_letter_type_id", columns={"letter_type_id"}),
 *        @ORM\Index(name="ix_doc_template_category_id", columns={"category_id"})
 *    }
 * )
 */
class DocTemplate extends AbstractDocTemplate implements DeletableInterface
{
    public const TEMPLATE_PATH_PREFIXES = [
        'root' => 'templates/',
        'ni' => 'templates/NI/',
        'gb' => 'templates/GB/',
        'image' => 'templates/Image/',
        'guides' => 'guides/'
    ];

    /**
     * @param SubCategory $subCategory
     * @param string $templateSlug
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType
     * @return DocTemplate
     */
    public static function createNew(
        Category $category,
        ?SubCategory $subCategory,
        string $description,
        Document $document,
        string $isNi,
        string $suppressFromOp,
        ?string $templateSlug,
        $letterType,
        User $createdBy
    ) {
        $docTemplate = new self();
        $docTemplate->category = $category;
        $docTemplate->subCategory = $subCategory;
        $docTemplate->description = $description;
        $docTemplate->document = $document;
        $docTemplate->isNi = $isNi;
        $docTemplate->suppressFromOp = $suppressFromOp;
        $docTemplate->templateSlug = $templateSlug;
        $docTemplate->letterType = $letterType;
        $docTemplate->category = $category;
        $docTemplate->createdBy = $createdBy;

        return $docTemplate;
    }

    /**
     * @param SubCategory $subCategory
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType
     * @return DocTemplate
     */
    public function updateMeta(
        Category $category,
        ?SubCategory $subCategory,
        string $description,
        string $isNi,
        string $suppressFromOp,
        $letterType = null
    ) {
        $this->category = $category;
        $this->subCategory = $subCategory;
        $this->description = $description;
        $this->isNi = $isNi;
        $this->suppressFromOp = $suppressFromOp;
        $this->letterType = $letterType;

        return $this;
    }

    /**
     * @return DocTemplate
     */
    public function updateDocument(
        Document $document
    ) {
        $this->document = $document;
        return $this;
    }

    /**
     * Can doc template be deleted?
     *
     * @return boolean
     */
    public function canDelete()
    {
        return true;
    }
}
