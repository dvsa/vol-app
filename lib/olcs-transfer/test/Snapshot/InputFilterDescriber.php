<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Snapshot;

use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;

/**
 * Turns a built input filter into plain data, so it can be stored as a snapshot and compared.
 *
 * Inputs, filters and validators are expanded by reflection, recording every property that differs from
 * its declared default. That captures settings a class keeps in its own properties as well as those in
 * its options. Any other object
 * (plugin managers, translators, injected services) is recorded by class name only: its state belongs
 * to the service container, not to the DTO's declared rules.
 *
 * Filter and validator chains are recorded as the ordered list of what they run, since order matters.
 */
final class InputFilterDescriber
{
    /** @var list<object> objects currently being expanded, so a cycle is reported rather than followed */
    private array $path = [];

    public function describe(mixed $value): mixed
    {
        if ($value instanceof \Closure) {
            return ['class' => \Closure::class];
        }

        if (is_object($value)) {
            return $this->describeObject($value);
        }

        if (is_array($value)) {
            return array_map($this->describe(...), $value);
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function describeObject(object $object): array
    {
        if (!$this->isExpanded($object)) {
            return ['class' => $object::class];
        }

        if (in_array($object, $this->path, true)) {
            return ['class' => $object::class, 'recursion' => true];
        }

        $this->path[] = $object;

        try {
            return ['class' => $object::class] + match (true) {
                $object instanceof FilterChain => ['filters' => $this->describe(iterator_to_array($object, false))],
                $object instanceof ValidatorChain => [
                    'validators' => $this->describe(iterator_to_array($object, false)),
                ],
                default => $this->describeProperties($object),
            };
        } finally {
            array_pop($this->path);
        }
    }

    private function isExpanded(object $object): bool
    {
        return $object instanceof InputFilterInterface
            || $object instanceof InputInterface
            || $object instanceof FilterInterface
            || $object instanceof ValidatorInterface;
    }

    /**
     * Every instance property that differs from its declared default.
     *
     * Leaving out defaults keeps the snapshot to what the DTO and builder actually set: a class's stock
     * error messages and its unused runtime state (value, data, messages) are implied by the class name.
     *
     * @return array<string, mixed>
     */
    private function describeProperties(object $object): array
    {
        $properties = [];

        foreach ($this->propertiesOf($object) as $name => $property) {
            if (!$property->isInitialized($object)) {
                $properties[$name] = ['uninitialized' => true];
                continue;
            }

            $value = $property->getValue($object);

            if ($object instanceof InputFilterInterface && $property->getName() === 'inputs' && is_array($value)) {
                // Inputs are added in reflection order, which PHP 8.5 changed (a class's own properties now come
                // before inherited ones). Each input is validated independently, so their order doesn't matter.
                ksort($value);
            }

            if ($object instanceof AbstractValidator && $property->getName() === 'abstractOptions') {
                $value = $this->withoutDefaultValidatorOptions($object, $value);

                if ($value === []) {
                    continue;
                }
            } elseif ($property->hasDefaultValue() && $value === $property->getDefaultValue()) {
                continue;
            }

            $properties[$name] = $this->describe($value);
        }

        return $properties;
    }

    /**
     * The object's public and protected properties (inherited ones included), then each parent's private ones.
     *
     * @return array<string, \ReflectionProperty>
     */
    private function propertiesOf(object $object): array
    {
        $class = new \ReflectionObject($object);
        $properties = [];

        foreach ($class->getProperties() as $property) {
            if (!$property->isStatic()) {
                $properties[$property->getName()] = $property;
            }
        }

        while (($class = $class->getParentClass()) !== false) {
            foreach ($class->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
                if (!$property->isStatic() && $property->getDeclaringClass()->getName() === $class->getName()) {
                    // A parent's private property can share its name with one declared further down
                    $properties[$class->getName() . '::' . $property->getName()] = $property;
                }
            }
        }

        return $properties;
    }

    /**
     * Laminas validators copy their stock message templates and variables into abstractOptions when built,
     * so those keys are compared with the class's declared templates rather than the property default.
     * Custom messages set by a DTO still differ, so they are kept.
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function withoutDefaultValidatorOptions(AbstractValidator $validator, array $options): array
    {
        $class = new \ReflectionObject($validator);
        $defaults = $class->getProperty('abstractOptions')->getDefaultValue();

        // Only declared by validators that have messages or message variables of their own
        foreach (['messageTemplates', 'messageVariables'] as $key) {
            $defaults[$key] = $class->hasProperty($key) ? $class->getProperty($key)->getDefaultValue() ?? [] : [];
        }

        return array_filter(
            $options,
            static fn (mixed $value, string $key): bool => !array_key_exists($key, $defaults)
                || $value !== $defaults[$key],
            ARRAY_FILTER_USE_BOTH
        );
    }
}
