<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessPreviousConviction;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Mockery as m;

/**
 * CanAccessPreviousConvictionTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessPreviousConvictionTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessPreviousConviction
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessPreviousConviction();

        parent::setUp();
    }

    public function testIsValidTransportManager(): void
    {
        $mockTm = m::mock();

        $entity = m::mock(Application::class);
        $entity->shouldReceive('getTransportManager')->with()->twice()->andReturn($mockTm);

        $user = $this->mockUser();
        $user->shouldReceive('getTransportManager')->with()->once()->andReturn($mockTm);

        $repo = $this->mockRepo('PreviousConviction');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->assertEquals(true, $this->sut->isValid(111));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $isOwner, mixed $expected): void
    {
        $entity = m::mock(Application::class);
        $entity->shouldReceive('getTransportManager')->with()->once()->andReturn(null);

        $this->setIsGranted(\Dvsa\Olcs\Api\Entity\User\Permission::INTERNAL_USER, null);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);

        $repo = $this->mockRepo('PreviousConviction');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
