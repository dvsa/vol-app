<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as Entity;

/**
 * Application Step
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationStep extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Return the application step corresponding to the specified path and slug
     *
     * @param int $applicationPathId
     * @param string $slug
     *
     * @return Entity
     *
     * @throws NotFoundException
     */
    public function fetchByApplicationPathIdAndSlug($applicationPathId, $slug)
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select('ast')
                ->from(Entity::class, 'ast')
                ->innerJoin('ast.question', 'q')
                ->where('IDENTITY(ast.applicationPath) = ?1')
                ->andWhere('q.slug = ?2')
                ->setParameter(1, $applicationPathId)
                ->setParameter(2, $slug)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundException(
                sprintf(
                    'Unable to find application step with path id %s and slug %s',
                    $applicationPathId,
                    $slug
                )
            );
        }
    }
}
