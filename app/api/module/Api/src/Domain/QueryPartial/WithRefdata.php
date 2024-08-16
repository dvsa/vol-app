<?php

/**
 * With Refdata
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * With Refdata
 */
final class WithRefdata implements QueryPartialInterface
{
    private $refDataEntity = \Dvsa\Olcs\Api\Entity\System\RefData::class;

    public function __construct(private readonly EntityManagerInterface $em, private readonly With $with)
    {
    }

    /**
     * Joins on all refdata relationships
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        $entity = ((isset($arguments[0]) && isset($arguments[1])) ? $arguments[0] : $qb->getRootEntities()[0]);
        $alias = ((isset($arguments[0]) && isset($arguments[1])) ? $arguments[1] : $qb->getRootAliases()[0]);

        /** @var $meta \Doctrine\ORM\Mapping\ClassMetadata */
        $meta = $this->em->getClassMetadata($entity);

        foreach ($meta->associationMappings as $property => $config) {
            if ($config['targetEntity'] === $this->refDataEntity) {
                $this->with->modifyQuery($qb, [$alias . '.' . $property]);
            }
        }
    }
}
