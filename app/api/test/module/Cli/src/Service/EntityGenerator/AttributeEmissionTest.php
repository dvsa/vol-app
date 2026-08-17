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
use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\InversedByConfig;
use Mockery as m;
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
            "#[ORM\\JoinColumn(name: 'licence_id', referencedColumnName: 'id', nullable: false)]\n    "
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

    public function testManyToOneWithConfiguredInverseSideEmitsInversedBy(): void
    {
        // cf. AbstractLicenceVehicle::$licence <-> AbstractLicence::$licenceVehicles:
        // EntityConfig's inversedBy drives the generated inverse collection, so the
        // owning side must name it with identical pluralization
        $table = new TableMetadata('licence_vehicle', [], [], [], [
            ['local_columns' => ['licence_id'], 'foreign_table' => 'licence'],
        ]);
        $sut = new RelationshipTypeHandler();
        $sut->setCurrentTable($table);
        $column = new ColumnMetadata('licence_id', 'integer', null, false);

        $this->assertSame(
            "#[ORM\\JoinColumn(name: 'licence_id', referencedColumnName: 'id', nullable: false)]\n    "
            . "#[ORM\\ManyToOne(targetEntity: \\Dvsa\\Olcs\\Api\\Entity\\Licence\\Licence::class,"
            . " inversedBy: 'licenceVehicles', fetch: 'LAZY')]",
            $sut->generateAnnotation($column, [
                'namespaces' => ['Licence' => 'Licence'],
                'fieldConfig' => new FieldConfig(
                    inversedBy: new InversedByConfig(entity: 'Licence', property: 'licenceVehicle'),
                ),
            ])
        );
    }

    public function testManyToOneWithoutConfiguredInverseSideStaysUnidirectional(): void
    {
        $table = new TableMetadata('application', [], [], [], [
            ['local_columns' => ['licence_id'], 'foreign_table' => 'licence'],
        ]);
        $sut = new RelationshipTypeHandler();
        $sut->setCurrentTable($table);
        $column = new ColumnMetadata('licence_id', 'integer', null, false);

        $this->assertStringNotContainsString(
            'inversedBy',
            $sut->generateAnnotation($column, ['namespaces' => ['Licence' => 'Licence']])
        );
    }

    public function testGenerateInverseFalseStillEmitsOwningInversedBy(): void
    {
        // cf. AbstractLetterInstanceChoice::$letterInstance - the inverse collection
        // is hand-written in the concrete LetterInstance, but the owning side must
        // still name it
        $table = new TableMetadata('letter_instance_choice', [], [], [], [
            ['local_columns' => ['letter_instance_id'], 'foreign_table' => 'letter_instance'],
        ]);
        $sut = new RelationshipTypeHandler();
        $sut->setCurrentTable($table);
        $column = new ColumnMetadata('letter_instance_id', 'integer', null, false);

        $this->assertStringContainsString(
            "inversedBy: 'letterInstanceChoices'",
            $sut->generateAnnotation($column, [
                'namespaces' => ['LetterInstance' => 'Letter'],
                'fieldConfig' => new FieldConfig(
                    inversedBy: new InversedByConfig(
                        entity: 'LetterInstance',
                        property: 'letterInstanceChoice',
                        generateInverse: false,
                    ),
                ),
            ])
        );
    }

    public function testGenerateInverseFalseSkipsInverseCollectionGeneration(): void
    {
        $appRoot = dirname(__DIR__, 6);
        $configService = new EntityConfigService($appRoot . '/data/db/EntityConfig.php');
        $processor = new InverseRelationshipProcessor($configService, new PropertyNameResolver());

        $table = new TableMetadata('letter_instance_choice', [
            new ColumnMetadata('letter_instance_id', 'integer', null, false),
        ], [], [], [
            ['local_columns' => ['letter_instance_id'], 'foreign_table' => 'letter_instance'],
        ]);

        $this->assertSame([], $processor->processInverseRelationships([$table]));
    }

    public function testUnsignedAndFixedColumnOptionsAreEmitted(): void
    {
        $sut = new DefaultTypeHandler();

        // cf. address.olbs_key: int unsigned with no default
        $unsigned = new ColumnMetadata('olbs_key', 'integer', null, true, false, false, null, null, ['unsigned' => true]);
        $this->assertSame(
            "#[ORM\\Column(type: 'integer', name: 'olbs_key', nullable: true, options: ['unsigned' => true])]",
            $sut->generateAnnotation($unsigned)
        );

        // cf. address.admin_area: char(40) - fixed, after any default
        $fixed = new ColumnMetadata('admin_area', 'string', 40, true, false, false, null, null, ['fixed' => true]);
        $this->assertSame(
            "#[ORM\\Column(type: 'string', name: 'admin_area', length: 40, nullable: true, options: ['fixed' => true])]",
            $sut->generateAnnotation($fixed)
        );
    }

    /**
     * cf. Doc\DocumentAnalysis::$token - binary(16) NOT NULL.
     *
     * The Doctrine type must be `binary`, not `string`: DBAL declares a `string` column as
     * VARCHAR, so mapping it as a string both loses BinaryType's conversion and reports the
     * column as drifted against the Liquibase schema. `length` matters for the same reason -
     * DBAL falls back to 255 without it - and `fixed` is what makes it BINARY not VARBINARY.
     */
    public function testFixedBinaryColumnEmitsBinaryTypeWithLength(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('token', 'binary', 16, false, false, false, null, null, ['fixed' => true]);

        $this->assertSame(
            "#[ORM\\Column(type: 'binary', name: 'token', length: 16, nullable: false,"
            . " options: ['fixed' => true])]",
            $sut->generateAnnotation($column)
        );
    }

    public function testVarbinaryColumnKeepsLengthWithoutFixed(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('payload', 'binary', 255, true);

        $this->assertSame(
            "#[ORM\\Column(type: 'binary', name: 'payload', length: 255, nullable: true)]",
            $sut->generateAnnotation($column)
        );
    }

    /**
     * BinaryType::convertToPHPValue() always returns a php://temp stream, so a bare
     * `@var string` on the generated property would be false and would make legitimate
     * is_resource()/stream_get_contents() handling in the concrete entity look like a
     * type error to PHPStan.
     */
    public function testBinaryPropertyIsTypedAsStringOrResource(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('token', 'binary', 16, false, false, false, null, null, ['fixed' => true]);

        $property = $sut->generateProperty($column);

        $this->assertSame('string|resource', $property['type']);
        $this->assertSame('string|resource', (new MethodGeneratorService())->getPhpTypeFromType($property['type']));
    }

    /** A NOT NULL binary gets no property initialiser: '' is not a valid 16-byte token. */
    public function testBinaryPropertyHasNoEmptyStringDefault(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('token', 'binary', 16, false, false, false, null, null, ['fixed' => true]);

        $this->assertSame('null', $sut->generateProperty($column)['defaultValue']);
    }

    /**
     * cf. document_analysis.status - an ENUM, which the introspector maps to string and
     * which MySQL reports with length 0. Emitting that would declare VARCHAR(0).
     */
    public function testZeroLengthIsNotEmitted(): void
    {
        $sut = new DefaultTypeHandler();
        $column = new ColumnMetadata('status', 'string', 0, false, false, false, 'PENDING');

        $this->assertSame(
            "#[ORM\\Column(type: 'string', name: 'status', nullable: false,"
            . " options: ['default' => 'PENDING'])]",
            $sut->generateAnnotation($column)
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

    /**
     * cf. document_analysis.status. DBAL has no ENUM mapping, and an unmappable column
     * aborts the whole generation run ("Unknown database type enum requested"), not just
     * the table that owns it.
     */
    public function testEnumIsRegisteredAsStringSoIntrospectionDoesNotAbort(): void
    {
        $platform = new \Doctrine\DBAL\Platforms\MySQL80Platform();
        $connection = m::mock(\Doctrine\DBAL\Connection::class);
        $connection->shouldReceive('getDatabasePlatform')->andReturn($platform);
        $connection->shouldReceive('createSchemaManager')
            ->andReturn(m::mock(\Doctrine\DBAL\Schema\AbstractSchemaManager::class));

        $this->assertFalse($platform->hasDoctrineTypeMappingFor('enum'), 'precondition');

        new Doctrine3SchemaIntrospector($connection);

        $this->assertSame('string', $platform->getDoctrineTypeMapping('enum'));
    }

    public function testNullableRelationshipPropertyUsesColumnNullability(): void
    {
        $table = new TableMetadata('application', [], [], [], [
            ['local_columns' => ['licence_id'], 'foreign_table' => 'licence'],
        ]);

        $sut = new RelationshipTypeHandler();
        $sut->setCurrentTable($table);

        $nullableColumn = new ColumnMetadata('licence_id', 'integer', null, true);
        $nonNullableColumn = new ColumnMetadata('licence_id', 'integer', null, false);

        $this->assertTrue($sut->generateProperty($nullableColumn)['nullable']);
        $this->assertFalse($sut->generateProperty($nonNullableColumn)['nullable']);
    }

    public function testCollectionPhpTypeUsesCollectionInterface(): void
    {
        $sut = new MethodGeneratorService();

        $this->assertSame(
            '\Doctrine\Common\Collections\Collection',
            $sut->getPhpTypeFromType('\Doctrine\Common\Collections\Collection')
        );

        $this->assertSame(
            '\Doctrine\Common\Collections\Collection',
            $sut->getPhpTypeFromType('\Doctrine\Common\Collections\ArrayCollection')
        );

        $this->assertSame(
            '\Doctrine\Common\Collections\Collection',
            $sut->getPhpTypeFromType(
                '\Doctrine\Common\Collections\Collection<int, \Dvsa\Olcs\Api\Entity\Task\Task>'
            )
        );
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
