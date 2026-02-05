<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Diagnostics;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Cli\Domain\QueryHandler\DataRetention\EscapeMysqlIdentifierTrait;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use PDO;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

final class GenerateCheckFkIntegritySql extends AbstractQueryHandler
{
    use EscapeMysqlIdentifierTrait;

    /** @var string */
    private $databaseName;

    /** @var PDO */
    private $pdo;

    /**
     * @param QueryInterface $query
     * @return array[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        $constraintStatement = $this->pdo->prepare(
            "
            SELECT
              `TABLE_SCHEMA`,
              `TABLE_NAME`,
              `COLUMN_NAME`,
              `CONSTRAINT_NAME`,
              `REFERENCED_TABLE_SCHEMA`,
              `REFERENCED_TABLE_NAME`,
              `REFERENCED_COLUMN_NAME`
            FROM
              information_schema.KEY_COLUMN_USAGE
            WHERE
              (`CONSTRAINT_SCHEMA` LIKE :schema
               OR TABLE_SCHEMA LIKE :schema
               OR REFERENCED_TABLE_SCHEMA LIKE :schema
              )
              AND `REFERENCED_TABLE_SCHEMA` IS NOT NULL
            "
        );

        $constraintStatement->execute(['schema' => $this->databaseName]);

        $constraintsBySchemaAndTable = [];

        try {
            foreach ($constraintStatement->fetchAll(PDO::FETCH_ASSOC) as $constraint) {
                if (!isset($constraintsBySchemaAndTable[$constraint['TABLE_SCHEMA']])) {
                    $constraintsBySchemaAndTable[$constraint['TABLE_SCHEMA']] = [];
                }
                if (!isset($constraintsBySchemaAndTable[$constraint['TABLE_SCHEMA']][$constraint['TABLE_NAME']])) {
                    $constraintsBySchemaAndTable[$constraint['TABLE_SCHEMA']][$constraint['TABLE_NAME']] = [];
                }
                if (isset($constraintsBySchemaAndTable[$constraint['TABLE_SCHEMA']][$constraint['TABLE_NAME']][$constraint['CONSTRAINT_NAME']])) {
                    throw new RuntimeException(
                        'Compound key found - compound keys are not supported ' . var_export($constraint, true)
                    );
                }
                $constraintsBySchemaAndTable[$constraint['TABLE_SCHEMA']][$constraint['TABLE_NAME']][$constraint['CONSTRAINT_NAME']] = $constraint;
            }
        } finally {
            $constraintStatement->closeCursor();
        }

        $queries = [
            "
            DROP TEMPORARY TABLE IF EXISTS fk_integrity_violations_tmp;
            CREATE TEMPORARY TABLE fk_integrity_violations_tmp (
              fk_description VARCHAR(255) PRIMARY KEY,
              violations INT(11) NOT NULL
            ) ENGINE MEMORY;
            "
        ];

        foreach ($constraintsBySchemaAndTable as $schema => $constraintsByTable) {
            $escapedSchema = $this->escapeMysqlIdentifier($schema);
            foreach ($constraintsByTable as $table => $constraints) {
                $escapedTable = $this->escapeMysqlIdentifier($table);
                foreach ($constraints as $constraint) {
                    $escapedColumn = $this->escapeMysqlIdentifier($constraint['COLUMN_NAME']);
                    $escapedRefSchema = $this->escapeMysqlIdentifier($constraint['REFERENCED_TABLE_SCHEMA']);
                    $escapedRefTable = $this->escapeMysqlIdentifier($constraint['REFERENCED_TABLE_NAME']);
                    $escapedRefColumn = $this->escapeMysqlIdentifier($constraint['REFERENCED_COLUMN_NAME']);

                    $fkDescription = sprintf(
                        "%s.%s.%s -> %s.%s.%s (%s)",
                        $constraint['TABLE_SCHEMA'],
                        $constraint['TABLE_NAME'],
                        $constraint['COLUMN_NAME'],
                        $constraint['REFERENCED_TABLE_SCHEMA'],
                        $constraint['REFERENCED_TABLE_NAME'],
                        $constraint['REFERENCED_COLUMN_NAME'],
                        $constraint['CONSTRAINT_NAME']
                    );
                    $queries[] = "
                        INSERT INTO fk_integrity_violations_tmp
                        SELECT
                          " . $this->pdo->quote($fkDescription) . ",
                          COUNT(DISTINCT t0.$escapedColumn)
                        FROM
                          $escapedSchema.$escapedTable t0
                          LEFT JOIN $escapedRefSchema.$escapedRefTable t1 ON t1.$escapedRefColumn = t0.$escapedColumn
                        WHERE
                          t0.$escapedColumn IS NOT NULL
                          AND t1.$escapedRefColumn IS NULL;
                        ";
                }
            }
        }

        return ['queries' => $queries];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GenerateCheckFkIntegritySql
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $this->databaseName = $entityManager->getConnection()->getParams()['dbname'];
        $this->pdo = $entityManager->getConnection()->getNativeConnection();
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
