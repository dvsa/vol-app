<?php

namespace Dvsa\Olcs\Api\Domain;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Query Builder
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @method QueryBuilder withApplication()
 * @method QueryBuilder withIrhpApplication()
 * @method QueryBuilder withBusReg()
 * @method QueryBuilder withRefdata($entity = null, $alias = null)
 * @method QueryBuilder byId($id)
 * @method QueryBuilder with($property, $alias = null)
 * @method QueryBuilder withCase()
 * @method QueryBuilder withCreatedBy()
 * @method QueryBuilder withCreatedByWithTeam()
 * @method QueryBuilder withUser()
 * @method QueryBuilder paginate($page, $limit)
 * @method QueryBuilder order($sort, $order, $compositeFields = null)
 */
class QueryBuilder implements QueryBuilderInterface
{
    public const ERR_QB_NOT_SET = 'Doctrine Query Builder is not set';

    /**
     * @var DoctrineQueryBuilder
     */
    private $qb;

    /**
     * QueryBuilder constructor.
     *
     * @param QueryPartialServiceManager $queryPartialServiceManager Service manager
     */
    public function __construct(private readonly ServiceLocatorInterface $queryPartialServiceManager)
    {
    }

    /**
     * Set Query Builder
     *
     * @param DoctrineQueryBuilder $qb Doctrine Query Builder
     *
     * @return $this
     */
    public function modifyQuery(DoctrineQueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * Call partial
     *
     * @param string $name      Query Partial name (method name)
     * @param array  $arguments Partial arguments (method argument)
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (!$this->qb instanceof DoctrineQueryBuilder) {
            throw new \RuntimeException(self::ERR_QB_NOT_SET);
        }

        $this->queryPartialServiceManager->get($name)->modifyQuery($this->qb, $arguments);

        return $this;
    }
}
