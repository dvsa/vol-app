<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\EntityGenerator;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Cli\Domain\Command\EntityGenerator\GenerateEntities as Command;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Adapters\Doctrine3SchemaIntrospector;
use Dvsa\Olcs\Cli\Service\EntityGenerator\EntityGenerator;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\GenerationResult;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Generate entities command handler
 */
class GenerateEntities extends AbstractCommandHandler
{
    private Doctrine3SchemaIntrospector $schemaIntrospector;
    private EntityGenerator $entityGenerator;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->schemaIntrospector = $container->get(Doctrine3SchemaIntrospector::class);
        $this->entityGenerator = $container->get(EntityGenerator::class);

        return parent::__invoke($container, $requestedName, $options);
    }

    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof Command);

        $config = $this->loadConfiguration($command);
        
        // Get all tables from database
        $tableNames = $this->schemaIntrospector->getTableNames();
        
        // Filter tables based on include/exclude lists
        $tableNames = $this->filterTables($tableNames, $command);
        
        // Get table metadata
        $tables = [];
        foreach ($tableNames as $tableName) {
            $tables[] = $this->schemaIntrospector->getTableMetadata($tableName);
        }

        // Get relationships (including ManyToMany from join tables)
        $relationships = $this->schemaIntrospector->getRelationships();
        $config['relationships'] = $relationships;

        // Generate entities
        $result = $this->entityGenerator->generateEntities($tables, $config);

        // Write files if not dry run
        if (!$command->isDryRun()) {
            $this->writeGeneratedFiles($result, $command, $config);
        }

        // Create OLCS Result object
        $olcsResult = new Result();
        
        if ($result->isSuccessful()) {
            $olcsResult->addMessage(sprintf('Generated %d entities in %.2f seconds', $result->getEntityCount(), $result->getDuration()));
        } else {
            foreach ($result->getErrors() as $error) {
                $olcsResult->addMessage('Error: ' . $error);
            }
        }
        
        foreach ($result->getWarnings() as $warning) {
            $olcsResult->addMessage('Warning: ' . $warning);
        }
        
        // Store additional data as flags
        $olcsResult->setFlag('success', $result->isSuccessful());
        $olcsResult->setFlag('entityCount', $result->getEntityCount());
        $olcsResult->setFlag('errors', $result->getErrors());
        $olcsResult->setFlag('warnings', $result->getWarnings());
        $olcsResult->setFlag('duration', $result->getDuration());
        $olcsResult->setFlag('dryRun', $command->isDryRun());

        return $olcsResult;
    }

    /**
     * Load configuration for entity generation
     */
    private function loadConfiguration(Command $command): array
    {
        $defaultConfigPath = 'data/db/EntityConfig.php';
        $configPath = $command->getConfigPath() ?? $defaultConfigPath;
        
        if (!file_exists($configPath)) {
            throw new \RuntimeException(sprintf('Configuration file not found: %s', $configPath));
        }

        $entityConfig = include $configPath;
        
        return [
            'entityConfig' => $entityConfig,
            'mappingConfig' => $entityConfig['mappingConfig'] ?? [],
            'namespaces' => $entityConfig['namespaces'] ?? [],
            'outputPath' => $command->getOutputPath() ?? '/tmp/generated-entities',
            'replace' => $command->isReplace(),
        ];
    }

    /**
     * Filter tables based on include/exclude lists
     */
    private function filterTables(array $tableNames, Command $command): array
    {
        $includeTables = $command->getIncludeTables();
        $excludeTables = $command->getExcludeTables();

        // Apply include filter if specified
        if (!empty($includeTables)) {
            $tableNames = array_intersect($tableNames, $includeTables);
        }

        // Apply exclude filter
        if (!empty($excludeTables)) {
            $tableNames = array_diff($tableNames, $excludeTables);
        }

        return array_values($tableNames);
    }

    /**
     * Write generated files to disk
     */
    private function writeGeneratedFiles(GenerationResult $result, Command $command, array $config): void
    {
        $outputPath = $config['outputPath'];
        
        // Create output directories
        $this->createDirectories($outputPath);

        foreach ($result->getEntities() as $entityData) {
            $namespaceFolder = $entityData->getNamespaceFolder();
            
            // Create namespace folder if it doesn't exist
            $namespacePath = $outputPath . '/' . $namespaceFolder;
            if (!is_dir($namespacePath)) {
                mkdir($namespacePath, 0755, true);
            }
            
            // Write abstract entity in namespace folder
            $abstractPath = $namespacePath . '/Abstract' . $entityData->getClassName() . '.php';
            file_put_contents($abstractPath, $entityData->getAbstractContent());

            // Write concrete entity in namespace folder
            $concretePath = $namespacePath . '/' . $entityData->getClassName() . '.php';
            file_put_contents($concretePath, $entityData->getConcreteContent());

            // Write test in tests namespace folder
            $testNamespacePath = $outputPath . '/tests/' . $namespaceFolder;
            if (!is_dir($testNamespacePath)) {
                mkdir($testNamespacePath, 0755, true);
            }
            $testPath = $testNamespacePath . '/' . $entityData->getClassName() . 'EntityTest.php';
            file_put_contents($testPath, $entityData->getTestContent());
        }
    }

    /**
     * Create necessary directories
     */
    private function createDirectories(string $outputPath): void
    {
        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        $testDir = $outputPath . '/tests';
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
    }
}