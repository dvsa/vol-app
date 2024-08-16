<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as Entity;

/**
 * Organisation User
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OrganisationUser extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch by user id
     *
     * @param int $userId user id
     *
     * @return array
     */
    public function fetchByUserId($userId)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq('m.user', $userId));
        $query = $qb->getQuery();
        $query->execute();

        return $query->getResult();
    }

    /**
     * Delete by user id
     *
     * @param int $userId user id
     *
     * @return null
     */
    public function deleteByUserId($userId)
    {
        $orgUsers = $this->fetchByUserId($userId);
        foreach ($orgUsers as $ou) {
            $this->delete($ou);
        }
    }
}
