<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\MyAccount;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SysParamEntity;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\User\User;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SysParamRepo;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\MyAccount\MyAccount;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Olcs\Logging\Log\Logger;

/**
 * @see MyAccount
 */
class MyAccountTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new MyAccount();

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        $this->mockRepo('SystemParameter', SysParamRepo::class);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpUserIdProvider')]
    public function testHandleQueryFromCache(mixed $userId, mixed $searchUserId): void
    {
        $cacheResult = ['result'];

        /** @var User $mockUser */
        $mockUser = m::mock(User::class);
        $mockUser->expects('getId')->andReturn($userId);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $this->mockedSmServices[CacheEncryption::class]->expects('hasCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $searchUserId)
            ->andReturnTrue();

        $this->mockedSmServices[CacheEncryption::class]->expects('getCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $searchUserId)
            ->andReturn($cacheResult);

        $query = Qry::create([]);

        $this->assertEquals(
            $cacheResult,
            $this->sut->handleQuery($query)
        );
    }

    public static function dpUserIdProvider(): array
    {
        return [
            [999, 999],
            [null, 'anon'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQuery(mixed $isSelfservePromptEnabled, mixed $isEligibleForPermits, mixed $expectedEligibleForPrompt, mixed $canDeleteAdmin): void
    {
        $userId = 1;

        /** @var User $mockUser */
        $mockUser = m::mock(User::class);
        $mockUser->expects('getId')->withNoArgs()->andReturn($userId);
        $mockUser->expects('isInternal')->withNoArgs()->andReturnFalse();
        $mockUser->shouldReceive('serialize')->andReturn(['foo']);
        $mockUser->shouldReceive('hasActivePsvLicence')->andReturn(false);
        $mockUser->shouldReceive('getNumberOfVehicles')->andReturn(2);
        $mockUser->shouldReceive('hasOrganisationSubmittedLicenceApplication')->andReturn(true);
        $mockUser->expects('isEligibleForPermits')->withNoArgs()->andReturn($isEligibleForPermits);
        $mockUser->expects('getTeam')->never();
        $mockUser->expects('organisationCanDeleteOperatorAdmin')->withNoArgs()->andReturn($canDeleteAdmin);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $this->mockedSmServices[CacheEncryption::class]->expects('hasCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $userId)
            ->andReturnFalse();

        $mockSystemParameter = $this->repoMap['SystemParameter'];
        $mockSystemParameter->shouldReceive('isSelfservePromptEnabled')
            ->times($isEligibleForPermits ? 1 : 0)
            ->withNoArgs()
            ->andReturn($isSelfservePromptEnabled);

        $query = Qry::create([]);

        $expectedResult = [
            'foo',
            'hasActivePsvLicence' => false,
            'numberOfVehicles' => 2,
            'hasOrganisationSubmittedLicenceApplication' => true,
            'eligibleForPermits' => $isEligibleForPermits,
            'eligibleForPrompt' => $expectedEligibleForPrompt,
            'dataAccess' => [],
            'isInternal' => false,
            'canDeleteOperatorAdmin' => $canDeleteAdmin,
        ];

        $this->mockedSmServices[CacheEncryption::class]->expects('setCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $expectedResult, $userId);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(
            $expectedResult,
            $result->serialize()
        );
    }

    public function testHandleQueryInternal(): void
    {
        $userId = 1;
        $canAccessAll = true;
        $canAccessGb = true;
        $canAccessNi = true;
        $isIrfo = false;
        $trafficAreas = ['B', 'C'];
        $allowedSearchIndexes = Team::ALL_ELASTICSEARCH_INDEXES;

        $dataAccess = [
            'canAccessAll' => $canAccessAll,
            'canAccessGb' => $canAccessGb,
            'canAccessNi' => $canAccessNi,
            'trafficAreas' => $trafficAreas,
            'isIrfo' => $isIrfo,
            'allowedSearchIndexes' => $allowedSearchIndexes
        ];

        $excludedTeamsString = '1, 2, 3';
        $excludedTeamsArray = [1, 2, 3];

        $mockSystemParam = m::mock(SysParamEntity::class);
        $mockSystemParam->expects('getParamValue')->andReturn($excludedTeamsString);

        $sysParamResult = new Result($mockSystemParam);

        $this->expectedCacheCall(
            CacheEncryption::SYS_PARAM_IDENTIFIER,
            SysParamEntity::DATA_SEPARATION_TEAMS_EXEMPT,
            $sysParamResult
        );

        $mockTeam = m::mock(Team::class);
        $mockTeam->expects('canAccessAllData')->with($excludedTeamsArray)->andReturn($canAccessAll);
        $mockTeam->expects('canAccessGbData')->with($excludedTeamsArray)->andReturn($canAccessGb);
        $mockTeam->expects('canAccessNiData')->with($excludedTeamsArray)->andReturn($canAccessNi);
        $mockTeam->expects('getAllowedTrafficAreas')->with($excludedTeamsArray)->andReturn($trafficAreas);
        $mockTeam->expects('getIsIrfo')->with($excludedTeamsArray)->andReturn($isIrfo);
        $mockTeam->expects('getAllowedSearchIndexes')->with($excludedTeamsArray)->andReturn($allowedSearchIndexes);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class);
        $mockUser->expects('getId')->withNoArgs()->andReturn($userId);
        $mockUser->expects('isInternal')->withNoArgs()->andReturnTrue();
        $mockUser->shouldReceive('serialize')->andReturn(['foo']);
        $mockUser->shouldReceive('hasActivePsvLicence')->never();
        $mockUser->shouldReceive('getNumberOfVehicles')->never();
        $mockUser->shouldReceive('hasOrganisationSubmittedLicenceApplication')->never();
        $mockUser->expects('isEligibleForPermits')->never();
        $mockUser->expects('getTeam')->andReturn($mockTeam);
        $mockUser->expects('organisationCanDeleteOperatorAdmin')->never();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $this->mockedSmServices[CacheEncryption::class]->expects('hasCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $userId)
            ->andReturnFalse();

        $query = Qry::create([]);

        $expectedResult = [
            'foo',
            'hasActivePsvLicence' => false,
            'numberOfVehicles' => 0,
            'hasOrganisationSubmittedLicenceApplication' => false,
            'eligibleForPermits' => false,
            'eligibleForPrompt' => false,
            'dataAccess' => $dataAccess,
            'isInternal' => true,
            'canDeleteOperatorAdmin' => false,
        ];

        $this->mockedSmServices[CacheEncryption::class]->expects('setCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $expectedResult, $userId);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(
            $expectedResult,
            $result->serialize()
        );
    }

    public static function dpHandleQuery(): array
    {
        return [
            [
                'isSelfservePromptEnabled' => false,
                'isEligibleForPermits' => true,
                'expectedEligibleForPrompt' => false,
                'canDeleteAdmin' => false,
            ],
            [
                'isSelfservePromptEnabled' => true,
                'isEligibleForPermits' => false,
                'expectedEligibleForPrompt' => false,
                'canDeleteAdmin' => false,
            ],
            [
                'isSelfservePromptEnabled' => true,
                'isEligibleForPermits' => true,
                'expectedEligibleForPrompt' => true,
                'canDeleteAdmin' => true,
            ],
        ];
    }

    public function testHandleQueryAnon(): void
    {
        $userId = User::USER_TYPE_ANON;

        /** @var User $mockUser */
        $mockUser = m::mock(User::class);
        $mockUser->expects('getId')->withNoArgs()->andReturnNull();
        $mockUser->expects('isInternal')->withNoArgs()->andReturnFalse();
        $mockUser->shouldReceive('serialize')->andReturn(['foo']);
        $mockUser->shouldReceive('hasActivePsvLicence')->never();
        $mockUser->shouldReceive('getNumberOfVehicles')->never();
        $mockUser->shouldReceive('hasOrganisationSubmittedLicenceApplication')->never();
        $mockUser->expects('isEligibleForPermits')->never();
        $mockUser->expects('getTeam')->never();
        $mockUser->expects('organisationCanDeleteOperatorAdmin')->never();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $this->mockedSmServices[CacheEncryption::class]->expects('hasCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $userId)
            ->andReturnFalse();

        $query = Qry::create([]);

        $expectedResult = [
            'foo',
            'hasActivePsvLicence' => false,
            'numberOfVehicles' => 0,
            'hasOrganisationSubmittedLicenceApplication' => false,
            'eligibleForPermits' => false,
            'eligibleForPrompt' => false,
            'dataAccess' => [],
            'isInternal' => false,
            'canDeleteOperatorAdmin' => false,
        ];

        $this->mockedSmServices[CacheEncryption::class]->expects('setCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $expectedResult, $userId);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(
            $expectedResult,
            $result->serialize()
        );
    }

    public function testHandleQueryThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn(null);

        $query = Qry::create([]);

        $this->sut->handleQuery($query);
    }
}
