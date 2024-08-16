<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocParagraphBookmark Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="doc_paragraph_bookmark",
 *    indexes={
 *        @ORM\Index(name="ix_doc_paragraph_bookmark_doc_paragraph_id", columns={"doc_paragraph_id"}),
 *        @ORM\Index(name="ix_doc_paragraph_bookmark_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_doc_paragraph_bookmark_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="IDX_34C39149C1FDC79C", columns={"doc_bookmark_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_doc_paragraph_bookmark_doc_bookmark_id_doc_paragraph_id", columns={"doc_bookmark_id","doc_paragraph_id"})
 *    }
 * )
 */
class DocParagraphBookmark extends AbstractDocParagraphBookmark
{
}
