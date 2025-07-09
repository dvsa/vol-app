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
        
        // Get relationships (including ManyToMany from join tables)
        $relationships = $this->schemaIntrospector->getRelationships();
        $config['relationships'] = $relationships;
        
        // Get list of join tables to exclude from entity generation
        $joinTableNames = [];
        foreach ($relationships as $tableName => $tableRelationships) {
            foreach ($tableRelationships as $relationship) {
                if ($relationship['type'] === 'many_to_many' && isset($relationship['join_table'])) {
                    $joinTableNames[] = $relationship['join_table'];
                }
            }
        }
        $joinTableNames = array_unique($joinTableNames);
        
        // Get table metadata and filter out ignored tables and join tables
        $tables = [];
        foreach ($tableNames as $tableName) {
            // Skip join tables
            if (in_array($tableName, $joinTableNames)) {
                continue;
            }
            
            $tableMetadata = $this->schemaIntrospector->getTableMetadata($tableName);
            
            // Skip tables with @settings['ignore'] in comment
            if ($this->shouldIgnoreTable($tableMetadata)) {
                continue;
            }
            
            $tables[] = $tableMetadata;
        }

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
        $olcsResult->setFlag('outputPath', $config['outputPath']);

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
     * Check if table should be ignored based on comment
     */
    private function shouldIgnoreTable($tableMetadata): bool
    {
        $comment = $tableMetadata->getComment();
        if (empty($comment)) {
            return false;
        }

        // Look for @settings['ignore'] in the comment
        return preg_match('/@settings\s*\[\s*[\'"]ignore[\'"]\s*\]/', $comment) === 1;
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
            
            // Handle path construction - avoid Entity/Entity duplication
            if (empty($namespaceFolder) || $namespaceFolder === 'Entity') {
                // For root entities, write directly to output path
                $namespacePath = $outputPath;
                $testNamespacePath = $outputPath . '/tests';
            } else {
                // For namespaced entities, create subdirectory
                $namespacePath = $outputPath . '/' . $namespaceFolder;
                $testNamespacePath = $outputPath . '/tests/' . $namespaceFolder;
            }
            
            // Create directories if they don't exist
            if (!is_dir($namespacePath)) {
                mkdir($namespacePath, 0755, true);
            }
            
            // Write abstract entity
            $abstractPath = $namespacePath . '/Abstract' . $entityData->getClassName() . '.php';
            file_put_contents($abstractPath, $entityData->getAbstractContent());

            // Write concrete entity ONLY if it doesn't already exist
            $concretePath = $namespacePath . '/' . $entityData->getClassName() . '.php';
            if (!file_exists($concretePath)) {
                file_put_contents($concretePath, $entityData->getConcreteContent());
            }

            // Test generation disabled
            // if (!is_dir($testNamespacePath)) {
            //     mkdir($testNamespacePath, 0755, true);
            // }
            // $testPath = $testNamespacePath . '/' . $entityData->getClassName() . 'EntityTest.php';
            // file_put_contents($testPath, $entityData->getTestContent());
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

        // Test directory creation disabled
        // $testDir = $outputPath . '/tests';
        // if (!is_dir($testDir)) {
        //     mkdir($testDir, 0755, true);
        // }
    }
}