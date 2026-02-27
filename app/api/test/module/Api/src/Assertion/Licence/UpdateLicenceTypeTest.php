<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Assertion\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Assertion\Licence\UpdateLicenceType;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Check whether the current user can update the type of licence for the given licence
 */
class UpdateLicenceTypeTest extends MockeryTestCase
{
    protected $sut;

    protected $auth;

    public function setUp(): void
    {
        $this->sut = new UpdateLicenceType();
        $this->auth = m::mock(AuthorizationService::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerAssert')]
    public function testAssert(mixed $isInternal, mixed $licCat, mixed $licTyp, mixed $expected): void
    {
        $licence = m::mock(Licence::class);

        $licence->shouldReceive('getGoodsOrPsv->getId')
            ->andReturn($licCat);

        $licence->shouldReceive('getLicenceType->getId')
            ->andReturn($licTyp);

        $this->auth->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER)
            ->andReturn($isInternal);

        $this->assertEquals($expected, $this->sut->assert($this->auth, $licence));
    }

    public static function providerAssert(): array
    {
        return [
            [
                true,
                'it dont matter',
                'it dont matter',
                true
            ],
            [
                false,
                Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
                'it dont matter',
                true
            ],
            [
                false,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                true
            ],
            [
                false,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                true
            ],
            [
                false,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                false
            ],
            [
                false,
                Licence::LICENCE_CATEGORY_PSV,
                Licence::LICENCE_TYPE_RESTRICTED,
                false
            ]
        ];
    }

    public function assert(AuthorizationService $authorizationService, Licence $context = null): bool
    {
        if ($authorizationService->isGranted(Permission::INTERNAL_USER)) {
            return true;
        }

        if ($context->getGoodsOrPsv()->getId() === Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return true;
        }

        $allowedLicTypes = [Licence::LICENCE_TYPE_STANDARD_NATIONAL, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL];

        if (in_array($context->getLicenceType()->getId(), $allowedLicTypes)) {
            return true;
        }

        return false;
    }
}
