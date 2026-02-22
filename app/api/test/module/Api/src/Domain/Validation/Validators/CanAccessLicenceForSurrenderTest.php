<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessLicenceForSurrender;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

class CanAccessLicenceForSurrenderTest extends AbstractValidatorsTestCase
{
    public function setUp(): void
    {
        $this->sut = new CanAccessLicenceForSurrender();
        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpLicencePermissions')]
    public function testIsValidExternalUserLicenceOwner(
        mixed $permission,
        mixed $isOwner,
        mixed $licenceState,
        mixed $surrenderStatus,
        mixed $expected
    ): void {
        $entityId = 111;

        $this->setIsGranted($permission, true);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(Licence::class);

        switch ($this->dataName()) {
            case 'selfservice-user-owner':
                $this->setIsValid('isOwner', [$entity], $isOwner);
                $entity->shouldReceive('getStatus->getId')->once()->andReturn($licenceState);

                break;
            case 'selfservice-user-owner-not-surrendered':
                $this->setIsGranted(Permission::INTERNAL_USER, false);
                $this->setIsValid('isOwner', [$entity], $isOwner);
                $entity->shouldReceive('getStatus->getId')->once()->andReturn($licenceState);

                break;

            case 'internal-user-not-surrendered':
                $this->setIsGranted(Permission::SELFSERVE_USER, false);
                $this->setIsValid('isOwner', [$entity], $isOwner);
                break;
            case 'internal-user-surrendered':
                $this->setIsGranted(Permission::SELFSERVE_USER, false);
                $this->setIsValid('isOwner', [$entity], $isOwner);
                break;
            case 'selfservice-user-surrender-submitted':
                $this->setIsValid('isOwner', [$entity], $isOwner);
                $entity->shouldReceive('getStatus->getId')->once()->andReturn($licenceState);
        }

        $repo = $this->mockRepo('Licence');
        $repo->shouldReceive('fetchById')->with($entityId)->andReturn($entity);
        $surrenderRepo = $this->mockRepo('Surrender');
        $surrenderEntity = m::mock(Surrender::class);
        $surrenderEntity->shouldReceive('getId')->andReturn(1);
        $surrenderEntity->shouldReceive('getStatus->getId')->andReturn($surrenderStatus);
        $surrenderRepo->shouldReceive('fetchOneByLicenceId')->andReturn(
            $surrenderEntity
        );
        $this->setIsValid('isOwner', [$surrenderEntity], $isOwner);
        $this->assertEquals($expected, $this->sut->isValid($entityId));
    }

    public static function dpLicencePermissions(): array
    {
        return [
            'selfservice-user-owner' => [
                Permission::SELFSERVE_USER,
                true,
                Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                Surrender::SURRENDER_STATUS_APPROVED,
                false
            ],
            'selfservice-user-owner-not-surrendered' => [
                Permission::SELFSERVE_USER,
                true,
                Licence::LICENCE_STATUS_VALID,
                Surrender::SURRENDER_STATUS_DISCS_COMPLETE,
                true

            ],
            'internal-user-not-surrendered' => [
                Permission::INTERNAL_USER,
                false,
                Licence::LICENCE_STATUS_VALID,
                Surrender::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE,
                true
            ],
            'internal-user-surrendered' => [
                Permission::INTERNAL_USER,
                false,
                Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                Surrender::SURRENDER_STATUS_SIGNED,
                true
            ],
            'selfservice-user-surrender-submitted' => [
                Permission::SELFSERVE_USER,
                true,
                Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
                Surrender::SURRENDER_STATUS_SUBMITTED,
                false
            ]
        ];
    }
}
