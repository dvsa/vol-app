<?php

declare(strict_types=1);

/**
 * Can Access Company Subsidiary Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessCompanySubsidiary;
use Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

/**
 * Can Access Company Subsidiary Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessCompanySubsidiaryTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanAccessCompanySubsidiary
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessCompanySubsidiary();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $isOwner, mixed $expected): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(CompanySubsidiary::class);

        $repo = $this->mockRepo('CompanySubsidiary');
        $repo->shouldReceive('fetchById')->with(111)->andReturn($entity);

        $this->setIsValid('isOwner', [$entity], $isOwner);

        $this->assertEquals($expected, $this->sut->isValid(111));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValidInternal(mixed $isOwner, mixed $expected): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $entity = m::mock(CompanySubsidiary::class);

        $repo = $this->mockRepo('CompanySubsidiary');
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
