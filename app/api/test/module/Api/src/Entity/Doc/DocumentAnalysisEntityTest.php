<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Doc;

use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

/**
 * Hand-written entity, so there is no generated AbstractDocumentAnalysis for EntityTester
 * to reflect over; this extends TestCase directly.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(Entity::class)]
final class DocumentAnalysisEntityTest extends TestCase
{
    private Entity $sut;

    private UuidV7 $token;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new Entity();
        $this->token = new UuidV7();

        parent::setUp();
    }

    /** In-memory, pre-flush: the property still holds the raw string handed to setToken(). */
    public function testGetTokenStringConvertsRawBytesToTheRfc4122Form(): void
    {
        $this->sut->setToken($this->token->toBinary());

        $this->assertSame($this->token->toRfc4122(), $this->sut->getTokenString());
    }

    /**
     * Doctrine's BinaryType always hands back a stream, never a string, so this is the
     * shape the entity actually has once it has been hydrated from the database.
     */
    public function testGetTokenStringReadsAHydratedBinaryStream(): void
    {
        $this->hydrateAsDoctrineWould($this->token->toBinary());

        $this->assertSame($this->token->toRfc4122(), $this->sut->getTokenString());
    }

    /**
     * Regression: a stream is consumed by reading it, so a naive read returns the token once
     * and an empty string every time after. The sweeper logs each row once today, but any
     * second read of the same managed entity - a retry, a second handler in the same request,
     * a result handler that logs and then responds - would blow up on an empty uid.
     */
    public function testGetTokenStringIsRepeatableOnAHydratedBinaryStream(): void
    {
        $this->hydrateAsDoctrineWould($this->token->toBinary());

        $this->assertSame($this->token->toRfc4122(), $this->sut->getTokenString());
        $this->assertSame($this->token->toRfc4122(), $this->sut->getTokenString(), 'second read');
        $this->assertSame($this->token->toRfc4122(), $this->sut->getTokenString(), 'third read');
    }

    /** getToken() stays raw; the string form is the only thing safe to put on the wire. */
    public function testGetTokenReturnsTheStoredValueUntouched(): void
    {
        $binary = $this->token->toBinary();
        $this->sut->setToken($binary);

        $this->assertSame($binary, $this->sut->getToken());
        $this->assertSame(16, strlen($this->sut->getToken()));
    }

    /**
     * Regression: the UnitOfWork compares scalar fields by identity, so swapping a hydrated
     * stream for an equal string would be read as a change and would write a pointless
     * UPDATE plus a history row. setToken(getToken()) must leave the property alone.
     */
    public function testReassigningTheSameTokenLeavesTheHydratedStreamInPlace(): void
    {
        $this->hydrateAsDoctrineWould($this->token->toBinary());

        $property = new \ReflectionProperty(Entity::class, 'token');
        $before = $property->getValue($this->sut);

        $this->sut->setToken($this->sut->getToken());

        $this->assertSame($before, $property->getValue($this->sut), 'property was replaced');
        $this->assertSame($this->token->toRfc4122(), $this->sut->getTokenString());
    }

    /** A genuinely different token still assigns, otherwise the setter would be a lie. */
    public function testAssigningADifferentTokenReplacesTheHydratedStream(): void
    {
        $this->hydrateAsDoctrineWould($this->token->toBinary());

        $replacement = new UuidV7();
        $this->sut->setToken($replacement->toBinary());

        $this->assertSame($replacement->toRfc4122(), $this->sut->getTokenString());
    }

    /**
     * Exactly what the ORM does to a BINARY(16) column on hydration: BinaryType turns the
     * value into a stream and the hydrator writes it straight onto the property by
     * reflection, bypassing the string-typed setter.
     */
    private function hydrateAsDoctrineWould(string $binary): void
    {
        $stream = Type::getType(Types::BINARY)->convertToPHPValue($binary, new MySQL80Platform());

        $property = new \ReflectionProperty(Entity::class, 'token');
        $property->setValue($this->sut, $stream);
    }
}
