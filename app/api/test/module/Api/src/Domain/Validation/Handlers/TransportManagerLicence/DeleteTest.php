<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\TransportManagerLicence;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerLicence\Delete;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete as DeleteDto;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTransportManagerLicence;

/**
 * DeleteTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class DeleteTest extends AbstractHandlerTestCase
{
    /**
     * @var Delete
     */
    protected $sut;

    /**
     * set up
     */
    public function setUp(): void
    {
        $this->sut = new Delete();

        parent::setUp();
    }

    /**
     * test isValid
     *
     * @param $canAccess
     * @param $expected
     * @param $userId
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
    public function testIsValid(mixed $canAccess, mixed $expected, mixed $userId): void
    {
        $mockUser = $this->mockUser();
        $mockUser->shouldReceive('getId')->once()->andReturn($userId)->getMock();

        /** @var m/MockInterface $dto */
        $dto = m::mock(new DeleteDto());
        $dto->shouldReceive('getIds')->andReturn([19, 11, 2015]);

        $mockValidator = m::mock(CanAccessTransportManagerLicence::class);
        $this->validatorManager->setService('canAccessTransportManagerLicence', $mockValidator);

        $mockValidator->shouldReceive('isValid')->with(19)->andReturn($canAccess);
        $mockValidator->shouldReceive('isValid')->with(11)->andReturn($canAccess);
        $mockValidator->shouldReceive('isValid')->with(2015)->andReturn($canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * data provider for testIsValid
     *
     * @return array
     */
    public static function dpIsValid(): array
    {
        return [
            [true, true, 10],
            [false, false, 10],
            [false, true, IdentityProviderInterface::SYSTEM_USER]
        ];
    }
}
