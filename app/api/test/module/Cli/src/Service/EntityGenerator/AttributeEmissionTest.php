<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\EntityGenerator;

use Doctrine\DBAL\Schema\Table;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Adapters\Doctrine3SchemaIntrospector;
use Dvsa\Olcs\Cli\Service\EntityGenerator\EntityConfigService;
use Dvsa\Olcs\Cli\Service\EntityGenerator\EntityGenerator;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TableMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\InverseRelationshipProcessor;
use Dvsa\Olcs\Cli\Service\EntityGenerator\MethodGeneratorService;
use Dvsa\Olcs\Cli\Service\EntityGenerator\PropertyNameResolver;
use Dvsa\Olcs\Cli\Service\EntityGenerator\TemplateRenderer;
use Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlerRegistry;
use Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\BlameableTypeHandler;
use Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\DefaultTypeHandler;
use Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\RelationshipTypeHandler;
use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig;
use PHPUnit\Framework\TestCase;

/**
 * Guards the exact PHP-attribute source emitted by the entity generator.
 *
 * The expected strings mirror the committed (converted) entities so that a
 * regeneration round-trips without drift — e.g. the JoinColumn-before-relation
 * ordering, the leading backslash on ::class references (which would otherwise
 * resolve relative to the generated entity's namespace), and string-typed
 * decimal defaults (a float 0.00 renders as DEFAULT '0' in schema diffs).
 */
final class AttributeEmissionTest extends TestCase
{
    public function testToOneRelationshipMatchesCommittedForm(): void
    {
        $table = new TableMetadata('application', [], [], [], [
            ['local_columns' => ['licence_id'], 'foreign_table' => 'licence'],
        ]);
        $sut = new RelationshipTypeHandler();
        $sut->setCurrentTable($table);
        $column = new ColumnMetadata('licence_id', 'integer', null, false);

        // cf. AbstractApplication::$licence
        $this->assertSame(
            "#[ORM\\JoinColumn(name: 'licence_id', referencedColumnName: 'id')]\n    "
            . "#[ORM\\ManyToOne(targetEntity: \\Dvsa\\Olcs\\Api\\Entity\\Licence\\Licence::class, fetch: 'LAZY')]",
            $sut->generateAnnotation($column, ['namespaces' => ['Licence' => 'Licence']])
        );
    }

    public function testBlameableMatchesCommittedForm(): void
    {
        $sut = new BlameableTypeHandler();
        $column = new ColumnMetadata('created_by', 'integer', null, true);

        // cf. any Abstract* entity's $createdBy
        $this->assertSame(
            "#[ORM\\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]\n    "
            . "#[ORM\\ManyToOne(targetEntity: \\Dvsa\\Olcs\\Api\\Entity\\User\\User::class, fetch: 'LAZY')]\n    "
            . "#[Gedmo\\Blameable(on: 'create')]",
            $sut->generateAnnotation($column)
        );
    }

    public function testDecimalDefaultStaysString(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('vat_amount', 'decimal', 10, false, false, false, '0.00', null, ['scale' => 2]);

        // cf. AbstractFee::$vatAmount
        $this->assertSame(
            "#[ORM\\Column(type: 'decimal', name: 'vat_amount', nullable: false,"
            . " options: ['default' => '0.00'], precision: 10, scale: 2)]",
            $sut->generateAnnotation($column)
        );
    }

    public function testStringDefaultIsSingleQuoted(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('input_type', 'string', 32, false, false, false, 'checkbox');

        // cf. AbstractLetterChoice::$inputType
        $this->assertSame(
            "#[ORM\\Column(type: 'string', name: 'input_type', length: 32, nullable: false,"
            . " options: ['default' => 'checkbox'])]",
            $sut->generateAnnotation($column)
        );
    }

    public function testStringDefaultWithQuoteAndDollarIsValidPhpSource(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('label', 'string', 32, false, false, false, "O'Brien \$x");

        $annotation = $sut->generateAnnotation($column);

        $this->assertStringContainsString("options: ['default' => 'O\\'Brien \$x']", $annotation);
    }

    public function testTranslatableFieldConfigEmitsGedmoAttribute(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('description', 'string', 512, true);

        // cf. ref_data.description in EntityConfig
        $this->assertSame(
            "#[ORM\\Column(type: 'string', name: 'description', length: 512, nullable: true)]\n    "
            . '#[Gedmo\\Translatable]',
            $sut->generateAnnotation($column, ['fieldConfig' => new FieldConfig(translatable: true)])
        );
    }

    public function testOwningManyToManyMatchesCommittedForm(): void
    {
        // cf. AbstractOpposition::$operatingCentres
        $this->assertSame(
            "#[ORM\\JoinTable(name: 'operating_centre_opposition')]\n    "
            . "#[ORM\\JoinColumn(name: 'opposition_id', referencedColumnName: 'id')]\n    "
            . "#[ORM\\InverseJoinColumn(name: 'operating_centre_id', referencedColumnName: 'id')]\n    "
            . "#[ORM\\ManyToMany(targetEntity: \\Dvsa\\Olcs\\Api\\Entity\\OperatingCentre\\OperatingCentre::class,"
            . " inversedBy: 'oppositions', fetch: 'LAZY')]",
            $this->invokeGeneratorMethod('buildOwningManyToManyAnnotation', [
                'Dvsa\\Olcs\\Api\\Entity\\OperatingCentre\\OperatingCentre',
                'oppositions',
                [
                    'join_table' => 'operating_centre_opposition',
                    'join_columns' => ['opposition_id'],
                    'local_columns' => ['id'],
                    'inverse_join_columns' => ['operating_centre_id'],
                    'foreign_columns' => ['id'],
                ],
            ])
        );
    }

    public function testInverseManyToManyMatchesCommittedForm(): void
    {
        // cf. AbstractOperatingCentre::$oppositions
        $this->assertSame(
            "#[ORM\\ManyToMany(targetEntity: \\Dvsa\\Olcs\\Api\\Entity\\Opposition\\Opposition::class,"
            . " mappedBy: 'operatingCentres', fetch: 'LAZY')]",
            $this->invokeGeneratorMethod('buildInverseManyToManyAnnotation', [
                'Dvsa\\Olcs\\Api\\Entity\\Opposition\\Opposition',
                'operatingCentres',
            ])
        );
    }

    public function testGetTargetEntityParsesAttributeSyntax(): void
    {
        $sut = (new \ReflectionClass(MethodGeneratorService::class))->newInstanceWithoutConstructor();

        $this->assertSame(
            'Dvsa\\Olcs\\Api\\Entity\\Cases\\Appeal',
            $sut->getTargetEntity([
                'annotation' => "#[ORM\\OneToOne(targetEntity: \\Dvsa\\Olcs\\Api\\Entity\\Cases\\Appeal::class, mappedBy: 'case')]",
            ])
        );
    }

    public function testUniqueKeysAreEmittedOnlyAsUniqueConstraints(): void
    {
        // cf. AbstractTransaction: uk_txn_receipt_document_id must not be emitted as
        // both #[ORM\Index] and #[ORM\UniqueConstraint] - DBAL rejects the name clash
        $table = new Table('txn');
        $table->addColumn('id', 'integer');
        $table->addColumn('receipt_document_id', 'integer');
        $table->addColumn('reference', 'string');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['receipt_document_id'], 'uk_txn_receipt_document_id');
        $table->addIndex(['reference'], 'ix_txn_reference');

        $indexes = $this->invokeIntrospectorMethod('extractIndexes', [$table]);
        $uniqueConstraints = $this->invokeIntrospectorMethod('extractUniqueConstraints', [$table]);

        $this->assertSame(['ix_txn_reference'], array_column($indexes, 'name'));
        $this->assertSame(['uk_txn_receipt_document_id'], array_column($uniqueConstraints, 'name'));
    }

    public function testOwningManyToManyToRefDataIsUnidirectional(): void
    {
        // cf. AbstractOpposition::$grounds - RefData never declares inverse
        // collections, so an inversedBy would point at a nonexistent property
        $field = $this->invokeConstructedGeneratorMethod('createManyToManyField', [
            [
                'join_table' => 'opposition_grounds',
                'foreign_table' => 'ref_data',
                'join_columns' => ['opposition_id'],
                'local_columns' => ['id'],
                'inverse_join_columns' => ['ground_id'],
                'foreign_columns' => ['id'],
                'is_owning' => true,
            ],
            'opposition',
            [],
        ]);

        $this->assertStringContainsString(
            "#[ORM\\ManyToMany(targetEntity: \\Dvsa\\Olcs\\Api\\Entity\\System\\RefData::class, fetch: 'LAZY')]",
            $field['annotation']
        );
        $this->assertStringNotContainsString('inversedBy', $field['annotation']);
    }

    public function testOwningManyToManyToNonRefDataKeepsInversedBy(): void
    {
        // cf. AbstractOpposition::$operatingCentres - bidirectional sides are unaffected
        $field = $this->invokeConstructedGeneratorMethod('createManyToManyField', [
            [
                'join_table' => 'operating_centre_opposition',
                'foreign_table' => 'operating_centre',
                'join_columns' => ['opposition_id'],
                'local_columns' => ['id'],
                'inverse_join_columns' => ['operating_centre_id'],
                'foreign_columns' => ['id'],
                'is_owning' => true,
            ],
            'opposition',
            [],
        ]);

        $this->assertStringContainsString("inversedBy: 'oppositions'", $field['annotation']);
    }

    private function invokeGeneratorMethod(string $method, array $args): string
    {
        $generator = (new \ReflectionClass(EntityGenerator::class))->newInstanceWithoutConstructor();

        return (new \ReflectionMethod(EntityGenerator::class, $method))->invoke($generator, ...$args);
    }

    private function invokeConstructedGeneratorMethod(string $method, array $args): mixed
    {
        $appRoot = dirname(__DIR__, 6);
        $configService = new EntityConfigService($appRoot . '/data/db/EntityConfig.php');
        $propertyNameResolver = new PropertyNameResolver();
        $generator = new EntityGenerator(
            new TypeHandlerRegistry(),
            new TemplateRenderer(
                $appRoot . '/module/Cli/src/Service/EntityGenerator/Templates',
                new MethodGeneratorService()
            ),
            $configService,
            new InverseRelationshipProcessor($configService, $propertyNameResolver),
            $propertyNameResolver
        );

        return (new \ReflectionMethod(EntityGenerator::class, $method))->invoke($generator, ...$args);
    }

    private function invokeIntrospectorMethod(string $method, array $args): array
    {
        $introspector = (new \ReflectionClass(Doctrine3SchemaIntrospector::class))->newInstanceWithoutConstructor();

        return (new \ReflectionMethod(Doctrine3SchemaIntrospector::class, $method))->invoke($introspector, ...$args);
    }
}
