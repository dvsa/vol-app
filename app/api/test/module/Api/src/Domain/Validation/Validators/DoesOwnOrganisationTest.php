<?php

declare(strict_types=1);

/**
 * Does Own Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\DoesOwnOrganisation;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Mockery as m;

/**
 * Does Own Organisation Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DoesOwnOrganisationTest extends AbstractValidatorsTestCase
{
    /**
     * @var DoesOwnOrganisation
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DoesOwnOrganisation();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidTrue(mixed $isOwner, mixed $expected): void
    {
        $entity = m::mock(Organisation::class);

        $repo = $this->mockRepo('Organisation');
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
