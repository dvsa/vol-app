<?php

namespace Dvsa\Olcs\Api\Entity\Retrieval;

use Doctrine\ORM\Mapping as ORM;

/**
 * RetrievalLinkEvent Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="retrieval_link_event",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_link_event_retrieval_link_id", columns={"retrieval_link_id"}),
 *        @ORM\Index(name="ix_retrieval_link_event_type", columns={"event_type"}),
 *        @ORM\Index(name="ix_retrieval_link_event_created_on", columns={"created_on"})
 *    }
 * )
 */
class RetrievalLinkEvent extends AbstractRetrievalLinkEvent
{
}
