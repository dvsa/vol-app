<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * DocumentToDelete Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="document_to_delete")
 */
class DocumentToDelete extends AbstractDocumentToDelete
{
    public const MAX_ATTEMPTS = 3;

    public const PROCESS_AFTER_MINUTES = 2;

    public function markAsFailed()
    {
        $this->setAttempts($this->getAttempts() + 1);
        $this->setProcessAfterDate($this->estimateProcessAfterDate());
    }

    /**
     * @return DateTime
     */
    private function estimateProcessAfterDate()
    {
        $now = new DateTime();
        $minutesToAdd = self::PROCESS_AFTER_MINUTES ** $this->getAttempts();

        $now->add(new \DateInterval('PT' . $minutesToAdd . 'M'));

        return $now;
    }
}
