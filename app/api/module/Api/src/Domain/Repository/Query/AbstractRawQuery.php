<?php

/**
 * Abstract Raw Query
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * Abstract Raw Query
 *
 * @NOTE Where possible, you should try to write a DQL query. However, there are certain limitations, and occasionally
 * you will need to write a "raw" sql query instead
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractRawQuery implements AuthAwareInterface, QueryInterface, FactoryInterface
{
    use AuthAwareTrait;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Namespace prefix
     *
     * @var string
     */
    protected $entityNamespacePrefix = '\Dvsa\Olcs\Api\Entity\\';

    /**
     * Map aliases to entities
     *
     * @var array
     */
    protected $templateMap = [];

    /**
     * To be extended
     *
     * @var string
     */
    protected $queryTemplate = '';

    /**
     * Params
     *
     * @var array
     */
    protected $params = [];

    /**
     * Param types
     *
     * @var array
     */
    protected $paramTypes = [];

    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * Execute the query
     *
     * @param array $params     params
     * @param array $paramTypes param types
     *
     * @throws RuntimeException
     */
    public function execute(array $params = [], array $paramTypes = []): Result
    {
        $masqueradedAsSystemUser = $this->identityProvider->getMasqueradedAsSystemUser();
        if ($masqueradedAsSystemUser) {
            $currentUserId = $this->getSystemUser()->getId();
        } else {
            $currentUserId = $this->getCurrentUser()->getId();
        }

        $params = array_merge(
            $this->getParams(),
            $params,
            [
                'currentUserId' => $currentUserId
            ]
        );
        if ($this->templateMap) {
            $query = $this->buildQueryFromTemplate($this->getQueryTemplate());
        } else {
            $query = $this->getQueryTemplate();
        }
        try {
            $paramTypes = array_merge($this->getParamTypes(), $paramTypes);
            return $this->connection->executeQuery($query, $params, $paramTypes);
        } catch (\Exception $ex) {
            throw new RuntimeException(
                'An unexpected error occurred while running query: ' .  static::class . ' ' . $ex->getMessage()
            );
        }
    }

    /**
     * Get the query template.
     *
     * @return string
     */
    protected function getQueryTemplate()
    {
        // strips excess whitespace from the query template
        return preg_replace('/\s\s+/', ' ', $this->queryTemplate);
    }

    /**
     * Get the default query params
     *
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    /**
     * Get the default query param types
     *
     * @return array
     */
    protected function getParamTypes()
    {
        return $this->paramTypes;
    }

    /**
     * Grab the table name of the entity
     *
     * @param string $entity entity
     *
     * @return string
     */
    private function getTableName($entity)
    {
        return $this->em->getClassMetadata($entity)->getTableName();
    }

    /**
     * Grab the column name for the field
     *
     * @param string $entity entity
     * @param string $field  field
     *
     * @return string
     */
    private function getColumnName($entity, $field)
    {
        $meta = $this->em->getClassMetadata($entity);

        if ($meta->isAssociationWithSingleJoinColumn($field)) {
            return $this->em->getClassMetadata($entity)->getSingleAssociationJoinColumnName($field);
        }

        return $this->em->getClassMetadata($entity)->getColumnName($field);
    }

    /**
     * Build a query from the template. Replace entity and field aliases with actual table and column names
     *
     * e.g.
     * Given template map:
     *      ['f' => \Entity\Foo::class, 'b' => \Entity\Bar::class]
     * and query template:
     *      UPDATE {f} INNER JOIN {b} ON {b.id} = {f.b} WHERE {b.fieldName} = 1
     * the output would be:
     *      UPDATE tbl_foo f INNER JOIN tbl_bar b ON b.id = f.b_id WHERE b.column_name = 1
     *
     * @param string $template  template
     * @param bool   $withAlias if true db fields will be prefixed with the table alias
     *
     * @return string
     */
    protected function buildQueryFromTemplate($template, $withAlias = true)
    {
        $method = ($withAlias) ? 'replaceTableOrField' : 'replaceTableOrFieldWithoutAlias';

        return preg_replace_callback(
            '/\{(?P<alias>[a-zA-Z]+)(?:\.(?P<field>[a-zA-Z0-9]+))?\}/',
            [$this, $method],
            $template
        );
    }

    /**
     * Replace a table or field name
     *
     * @param array $matches matches
     *
     * @return string
     */
    private function replaceTableOrField(array $matches = [])
    {
        $entity = $this->templateMap[$matches['alias']];

        if (empty($matches['field'])) {
            return $this->getTableName($entity) . ' ' . $matches['alias'];
        }

        return $matches['alias'] . '.' . $this->getColumnName($entity, $matches['field']);
    }

    /**
     * Replace a table or field name
     *
     * @param array $matches matches
     *
     * @return string
     */
    private function replaceTableOrFieldWithoutAlias(array $matches = [])
    {
        $entity = $this->templateMap[$matches['alias']];

        if (empty($matches['field'])) {
            return $this->getTableName($entity);
        }

        return $this->getColumnName($entity, $matches['field']);
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->em = $container->get('doctrine.entitymanager.orm_default');
        $this->connection = $this->em->getConnection();
        $this->identityProvider = $container->get(IdentityProviderInterface::class);
        $this->setAuthService($container->get(AuthorizationService::class));
        $this->setUserRepository($container->get('RepositoryServiceManager')->get('User'));
        return $this;
    }
}
