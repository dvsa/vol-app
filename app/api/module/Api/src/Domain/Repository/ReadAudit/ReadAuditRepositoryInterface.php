<?php

namespace Dvsa\Olcs\Api\Domain\Repository\ReadAudit;

/**
 * Read Audit Repository Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface ReadAuditRepositoryInterface
{
    /**
     * Remove older records
     *
     * @param $oldestDate
     * @return int
     */
    public function deleteOlderThan($oldestDate);

    /**
     * Fetch one or more records by userId, entityId and date
     *
     * @param $userId
     * @param $entityId
     *
     * @return mixed
     */
    public function fetchOneOrMore($userId, $entityId, \DateTime $date);
}
