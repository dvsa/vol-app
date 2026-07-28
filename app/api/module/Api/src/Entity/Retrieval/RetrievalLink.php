<?php

namespace Dvsa\Olcs\Api\Entity\Retrieval;

use Doctrine\ORM\Mapping as ORM;

/**
 * RetrievalLink Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="retrieval_link",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_link_expires_at", columns={"expires_at"}),
 *        @ORM\Index(name="ix_retrieval_link_flow_key", columns={"flow_key"}),
 *        @ORM\Index(name="ix_retrieval_link_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_retrieval_link_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_retrieval_link_token", columns={"token"})
 *    }
 * )
 */
class RetrievalLink extends AbstractRetrievalLink
{
}
