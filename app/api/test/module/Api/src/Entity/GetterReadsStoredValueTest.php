<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Support\EntityFieldReads;
use PHPUnit\Framework\TestCase;

class GetterReadsStoredValueTest extends TestCase
{
    private const KNOWN_DIFFERENCES = [
        'Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis::$token',
        'Dvsa\Olcs\Api\Entity\View\BusRegSearchView::$isTxcApp',
        'Dvsa\Olcs\Api\Entity\View\BusRegSearchView::$organisationId',
        'Dvsa\Olcs\Api\Entity\View\TaskSearchView::$messaging',
    ];

    #[\Ergebnis\PHPUnit\SlowTestDetector\Attribute\MaximumDuration(5000)]
    public function testGetterAndStoredValueAgreeForEveryMappedField(): void
    {
        $entityManager = require __DIR__ . '/../../../../../phpstan-object-manager.php';

        $comparisons = 0;
        $differing = [];

        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $class = new \ReflectionClass($metadata->getName());
            if ($class->isAbstract() || $metadata->isMappedSuperclass) {
                continue;
            }

            $fields = [];
            foreach ($metadata->fieldMappings as $name => $mapping) {
                $type = (string) $mapping->type;
                $fields[$name] = match (true) {
                    in_array($type, ['date', 'datetime', 'datetime_immutable', 'date_immutable'], true)
                        => ['2020-01-02', new \DateTime('2020-01-02 03:04:05')],
                    in_array($type, ['yesno', 'yesnonull'], true) => ['Y', 'N'],
                    $type === 'boolean' => [true, false, 0, 1],
                    in_array($type, ['integer', 'smallint', 'bigint'], true) => [7, 0],
                    in_array($type, ['decimal', 'float'], true) => ['12.50', 0],
                    in_array($type, ['json', 'simple_array', 'array'], true) => [['a' => 1], []],
                    default => ['abc', ''],
                };
            }
            foreach ($metadata->associationMappings as $name => $mapping) {
                if ($mapping->isToOne()) {
                    $target = new \ReflectionClass($mapping->targetEntity);
                    $fields[$name] = [
                        $target->isAbstract() ? new \stdClass() : $target->newInstanceWithoutConstructor(),
                    ];
                } else {
                    $fields[$name] = [new ArrayCollection([new \stdClass()]), new ArrayCollection()];
                }
            }

            foreach ($fields as $name => $values) {
                $property = $this->findProperty($class, $name);
                $states = array_merge(
                    [['default', null]],
                    array_map(static fn($value) => ['set', $value], $values),
                    [['null', null]]
                );

                foreach ($states as [$state, $value]) {
                    $entity = $class->newInstanceWithoutConstructor();
                    if ($state !== 'default') {
                        if ($property === null) {
                            continue;
                        }
                        try {
                            $property->setValue($entity, $value);
                        } catch (\Throwable) {
                            continue;
                        }
                    }

                    try {
                        $viaGetter = EntityFieldReads::viaGetter($entity, $name);
                    } catch (\Throwable $e) {
                        $viaGetter = $e;
                    }
                    try {
                        $stored = EntityFieldReads::stored($entity, $name);
                    } catch (\Throwable $e) {
                        $stored = $e;
                    }

                    $comparisons++;
                    $same = ($viaGetter instanceof \Throwable && $stored instanceof \Throwable)
                        ? $viaGetter::class === $stored::class
                        : $viaGetter === $stored;
                    if (!$same) {
                        $differing[$metadata->getName() . '::$' . $name] = true;
                    }
                }
            }
        }

        $differing = array_keys($differing);
        sort($differing);
        $expected = self::KNOWN_DIFFERENCES;
        sort($expected);

        $this->assertGreaterThan(10000, $comparisons, 'The walk over entity metadata made far fewer comparisons than expected, so it may have skipped most entities.');
        $this->assertSame(
            $expected,
            $differing,
            'Fields whose getter does not return the stored value. Under doctrine/collections 3 an in-memory'
                . ' Criteria filter reads the stored value, not the getter, so see "Getters and stored values"'
                . ' in docs/app/testing.md before changing this list.'
        );
    }

    private function findProperty(\ReflectionClass $class, string $name): ?\ReflectionProperty
    {
        for ($c = $class; $c; $c = $c->getParentClass()) {
            if ($c->hasProperty($name)) {
                return $c->getProperty($name);
            }
        }

        return null;
    }
}
