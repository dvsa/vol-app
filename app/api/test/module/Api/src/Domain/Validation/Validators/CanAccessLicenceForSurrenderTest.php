<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessLicenceForSurrender;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

final class CanAccessLicenceForSurrenderTest extends AbstractValidatorsTestCase
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
        bool $hasQueuedRevocation,
        bool $shouldFetchSurrender,
        mixed $expected
    ): void {
        $entityId = 111;

        $this->setIsGranted($permission, true);
        $this->auth->shouldReceive('getIdentity')->andReturn(null);
        $entity = m::mock(Licence::class);

        switch ($this->dataName()) {
            case 'selfservice-user-queued-revocation':
            case 'selfservice-user-owner':
                $this->setIsValid('isOwner', [$entity], $isOwner);

                break;
            case 'selfservice-user-owner-not-surrendered':
            case 'selfservice-user-surrender-signed':
                $this->setIsGranted(Permission::INTERNAL_USER, false);
                $this->setIsValid('isOwner', [$entity], $isOwner);

                break;

            case 'internal-user-not-surrendered':
            case 'internal-user-surrendered':
            case 'internal-user-queued-revocation':
                $this->setIsGranted(Permission::SELFSERVE_USER, false);
                $this->setIsValid('isOwner', [$entity], $isOwner);
                break;
            case 'selfservice-user-surrender-submitted':
                $this->setIsValid('isOwner', [$entity], $isOwner);
        }

        if ($permission === Permission::SELFSERVE_USER) {
            $entity->expects('hasQueuedRevocation')->withNoArgs()->andReturn($hasQueuedRevocation);
            if (!$hasQueuedRevocation) {
                $entity->expects('getStatus->getId')->withNoArgs()->andReturn($licenceState);
            }
        }

        $repo = $this->mockRepo('Licence');
        $repo->shouldReceive('fetchById')->with($entityId)->andReturn($entity);

        if ($shouldFetchSurrender) {
            $surrender = m::mock(Surrender::class);
            $surrender->expects('getStatus->getId')->withNoArgs()->andReturn($surrenderStatus);

            $surrenderRepo = $this->mockRepo('Surrender');
            $surrenderRepo->expects('fetchOneByLicenceId')->with($entityId)->andReturn($surrender);
        }

        $this->assertEquals($expected, $this->sut->isValid($entityId));
    }

    public static function dpLicencePermissions(): \Iterator
    {
        yield 'selfservice-user-owner' => [
            Permission::SELFSERVE_USER,
            true,
            Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
            Surrender::SURRENDER_STATUS_APPROVED,
            false,
            true,
            false
        ];
        yield 'selfservice-user-owner-not-surrendered' => [
            Permission::SELFSERVE_USER,
            true,
            Licence::LICENCE_STATUS_VALID,
            Surrender::SURRENDER_STATUS_DISCS_COMPLETE,
            false,
            false,
            true

        ];
        yield 'selfservice-user-queued-revocation' => [
            Permission::SELFSERVE_USER,
            true,
            Licence::LICENCE_STATUS_VALID,
            Surrender::SURRENDER_STATUS_DISCS_COMPLETE,
            true,
            false,
            false
        ];
        yield 'internal-user-not-surrendered' => [
            Permission::INTERNAL_USER,
            false,
            Licence::LICENCE_STATUS_VALID,
            Surrender::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE,
            false,
            false,
            true
        ];
        yield 'internal-user-queued-revocation' => [
            Permission::INTERNAL_USER,
            false,
            Licence::LICENCE_STATUS_VALID,
            Surrender::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE,
            true,
            false,
            true
        ];
        yield 'internal-user-surrendered' => [
            Permission::INTERNAL_USER,
            false,
            Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
            Surrender::SURRENDER_STATUS_SIGNED,
            false,
            false,
            true
        ];
        yield 'selfservice-user-surrender-submitted' => [
            Permission::SELFSERVE_USER,
            true,
            Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
            Surrender::SURRENDER_STATUS_SUBMITTED,
            false,
            true,
            false
        ];
        yield 'selfservice-user-surrender-signed' => [
            Permission::SELFSERVE_USER,
            true,
            Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
            Surrender::SURRENDER_STATUS_SIGNED,
            false,
            true,
            true
        ];
    }
}
