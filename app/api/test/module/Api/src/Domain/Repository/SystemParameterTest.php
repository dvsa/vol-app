<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as Repo;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as Entity;

#[\PHPUnit\Framework\Attributes\CoversClass(Repo::class)]
final class SystemParameterTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchValue(): void
    {
        $qb = $this->expectStoredValue('VALUE');

        $this->assertSame('VALUE', $this->sut->fetchValue('system.foo'));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame('system.foo', $qb->getParameter('byId')->getValue());
    }

    /**
     * A missing parameter is not an error: every accessor falls back to its own default, so both
     * an empty result and a NotFoundException have to read as "unset".
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('missingValueProvider')]
    public function testFetchValueWhenTheParameterIsMissing(bool $throws): void
    {
        $qb = $this->createRealQb();
        $expectation = $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT);

        $throws ? $expectation->andThrow(NotFoundException::class) : $expectation->andReturnNull();

        $this->assertNull($this->sut->fetchValue('system.foo'));
    }

    public static function missingValueProvider(): \Iterator
    {
        yield 'no rows' => [false];
        yield 'not found' => [true];
    }

    /**
     * Each accessor reads one named parameter and coerces the stored string. The name is part of
     * the contract — reading the wrong one would silently return another setting's value.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('accessorProvider')]
    public function testAccessors(string $method, string $expectedName, mixed $stored, mixed $expected): void
    {
        $qb = $this->expectStoredValue($stored);

        $this->assertSame($expected, $this->sut->{$method}());
        $this->assertSame($expectedName, $qb->getParameter('byId')->getValue());
    }

    public static function accessorProvider(): \Iterator
    {
        // Anything non-empty other than '0' is on; an unset parameter is off.
        $flags = [
            'getDisableSelfServeCardPayments' => Entity::DISABLED_SELFSERVE_CARD_PAYMENTS,
            'isSelfservePromptEnabled' => Entity::ENABLE_SELFSERVE_PROMPT,
            'getDisabledDigitalContinuations' => Entity::DISABLE_DIGITAL_CONTINUATIONS,
        ];

        foreach ($flags as $method => $name) {
            foreach (self::flagValues() as $label => [$stored, $expected]) {
                yield $method . ', ' . $label => [$method, $name, $stored, $expected];
            }

            yield $method . ', unset' => [$method, $name, null, false];
        }

        // The deletion switches invert that one case: unset means disabled, so a parameter nobody
        // has set cannot start deleting data.
        $deletionFlags = [
            'getDisableDataRetentionDocumentDelete' => Entity::DISABLE_DATA_RETENTION_DOCUMENT_DELETE,
            'getDisableDataRetentionDelete' => Entity::DISABLE_DATA_RETENTION_DELETE,
        ];

        foreach ($deletionFlags as $method => $name) {
            foreach (self::flagValues() as $label => [$stored, $expected]) {
                yield $method . ', ' . $label => [$method, $name, $stored, $expected];
            }

            yield $method . ', unset' => [$method, $name, null, true];
        }

        $reminderDefault = Repo::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT;

        yield 'reminder period, an int' => [
            'getDigitalContinuationReminderPeriod',
            Entity::DIGITAL_CONTINUATION_REMINDER_PERIOD,
            20,
            20,
        ];
        yield 'reminder period, a numeric string' => [
            'getDigitalContinuationReminderPeriod',
            Entity::DIGITAL_CONTINUATION_REMINDER_PERIOD,
            '99',
            99,
        ];
        yield 'reminder period, not a number' => [
            'getDigitalContinuationReminderPeriod',
            Entity::DIGITAL_CONTINUATION_REMINDER_PERIOD,
            'X',
            $reminderDefault,
        ];
        yield 'reminder period, unset' => [
            'getDigitalContinuationReminderPeriod',
            Entity::DIGITAL_CONTINUATION_REMINDER_PERIOD,
            null,
            $reminderDefault,
        ];

        // A delete limit of zero means unlimited, which is why a bad value falls back to it.
        yield 'delete limit, an int' => ['getDataRetentionDeleteLimit', Entity::DR_DELETE_LIMIT, 20, 20];
        yield 'delete limit, a numeric string' => ['getDataRetentionDeleteLimit', Entity::DR_DELETE_LIMIT, '99', 99];
        yield 'delete limit, not a number' => ['getDataRetentionDeleteLimit', Entity::DR_DELETE_LIMIT, 'X', 0];
        yield 'delete limit, unset' => ['getDataRetentionDeleteLimit', Entity::DR_DELETE_LIMIT, null, 0];
    }

    /** @return \Iterator<string, array{mixed, bool}> */
    private static function flagValues(): \Iterator
    {
        yield 'true' => [true, true];
        yield 'false' => [false, false];
        yield 'one' => [1, true];
        yield 'zero' => [0, false];
        yield "'1'" => ['1', true];
        yield "'0'" => ['0', false];
        yield 'empty' => ['', false];
        yield 'any other string' => ['X', true];
    }

    /**
     * The data-retention user has to be a real user id: there is no sensible default, so anything
     * else is fatal rather than quietly skipped.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataRetentionUserProvider')]
    public function testGetSystemDataRetentionUser(mixed $stored, ?int $expected): void
    {
        $qb = $this->expectStoredValue($stored);

        if ($expected === null) {
            $this->expectException(RuntimeException::class);

            $this->sut->getSystemDataRetentionUser();

            return;
        }

        $this->assertSame($expected, $this->sut->getSystemDataRetentionUser());
        $this->assertSame(Entity::SYSTEM_DATA_RETENTION_USER, $qb->getParameter('byId')->getValue());
    }

    public static function dataRetentionUserProvider(): \Iterator
    {
        yield 'an int' => [20, 20];
        yield 'a numeric string' => ['99', 99];
        yield 'not a number' => ['X', null];
        yield 'unset' => [null, null];
        yield 'zero' => [0, null];
    }

    private function expectStoredValue(mixed $value): \Dvsa\OlcsTest\Support\TestQueryBuilder
    {
        $parameter = new Entity();
        $parameter->setParamValue($value);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$parameter]);

        return $qb;
    }
}
