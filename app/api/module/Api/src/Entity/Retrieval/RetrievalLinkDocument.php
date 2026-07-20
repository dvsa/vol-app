<?php

namespace Dvsa\Olcs\Api\Entity\Retrieval;

use Doctrine\ORM\Mapping as ORM;

/**
 * RetrievalLinkDocument Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="retrieval_link_document",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_link_document_retrieval_link_id", columns={"retrieval_link_id"}),
 *        @ORM\Index(name="ix_retrieval_link_document_document_id", columns={"document_id"}),
 *        @ORM\Index(name="ix_retrieval_link_document_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_retrieval_link_document_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_retrieval_link_document_member_ref", columns={"member_ref"})
 *    }
 * )
 */
class RetrievalLinkDocument extends AbstractRetrievalLinkDocument
{
}
