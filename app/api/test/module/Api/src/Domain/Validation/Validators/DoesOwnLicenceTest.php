<?php

declare(strict_types=1);

/**
 * Does Own Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\DoesOwnLicence;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;

/**
 * Does Own Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DoesOwnLicenceTest extends AbstractValidatorsTestCase
{
    /**
     * @var DoesOwnLicence
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DoesOwnLicence();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidTrue(mixed $isOwner, mixed $expected): void
    {
        $entity = m::mock(Licence::class);

        $repo = $this->mockRepo('Licence');
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
