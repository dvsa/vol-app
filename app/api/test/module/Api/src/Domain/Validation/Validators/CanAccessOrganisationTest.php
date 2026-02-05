<?php

declare(strict_types=1);

/**
 * Can Access Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessOrganisation;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

/**
 * Can Access Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessOrganisationTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessOrganisation
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessOrganisation();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $isOwner, mixed $expected): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(Organisation::class);

        $repo = $this->mockRepo('Organisation');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidInternal(mixed $isOwner, mixed $expected): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $entity = m::mock(Organisation::class);

        $repo = $this->mockRepo('Organisation');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals(true, $this->sut->isValid(111));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
