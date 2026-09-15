<?php

namespace Dvsa\Olcs\Api\Entity\Retrieval;

use Doctrine\ORM\Mapping as ORM;

/**
 * RetrievalOtp Entity
 */
#[ORM\Table(name: 'retrieval_otp')]
#[ORM\Index(name: 'ix_retrieval_otp_retrieval_link_id', columns: ['retrieval_link_id'])]
#[ORM\Index(name: 'ix_retrieval_otp_expires_at', columns: ['expires_at'])]
#[ORM\Entity]
class RetrievalOtp extends AbstractRetrievalOtp
{
}
