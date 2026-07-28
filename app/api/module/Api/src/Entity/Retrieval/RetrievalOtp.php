<?php

namespace Dvsa\Olcs\Api\Entity\Retrieval;

use Doctrine\ORM\Mapping as ORM;

/**
 * RetrievalOtp Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="retrieval_otp",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_otp_retrieval_link_id", columns={"retrieval_link_id"}),
 *        @ORM\Index(name="ix_retrieval_otp_expires_at", columns={"expires_at"})
 *    }
 * )
 */
class RetrievalOtp extends AbstractRetrievalOtp
{
}
