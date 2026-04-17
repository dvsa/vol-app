<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GovUkAccount;

use Dvsa\GovUkAccount\Token\AccessToken;
use Dvsa\GovUkAccount\Token\GovUkAccountUser;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateApplication;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateContinuationDetail;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateSurrender;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateTmApplication;
use Dvsa\Olcs\Api\Domain\CommandHandler\GovUkAccount\ProcessAuthResponse as Handler;
use Dvsa\Olcs\Api\Domain\Repository\DigitalSignature as DigitalSignatureRepo;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\GovUkAccount\Data\Attributes;
use Dvsa\Olcs\Api\Service\GovUkAccount\GovUkAccountService;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\ProcessAuthResponse as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Mockery as m;

class ProcessAuthResponseTest extends AbstractCommandHandlerTestCase
{
    protected $sut;
    private m\MockInterface $govUkAccountService;

    public function setUp(): void
    {
        $this->govUkAccountService = m::mock(GovUkAccountService::class);
        $this->sut = new Handler($this->govUkAccountService);
        $this->mockRepo('DigitalSignature', DigitalSignatureRepo::class);

        parent::setUp();
    }

    public function testHandleCommandMissingCoreIdentity(): void
    {
        $code = 'code';
        $stateToken = 'token';
        $accessToken = m::mock(AccessToken::class);
        $userDetailsArray = ['required key missing'];
        $stateTokenClaims = [
            'returnUrl' => 'test',
            'id' => 100,
            'journey' => 'some_journey'
        ];

        $userDetailsResourceOwner = m::mock(ResourceOwnerInterface::class);
        $userDetailsResourceOwner->expects('toArray')->withNoArgs()->andReturn($userDetailsArray);

        $this->getGovUkAccountService($stateToken, $stateTokenClaims, $code, $accessToken, $userDetailsResourceOwner);

        $cmd = Cmd::create(['code' => $code, 'state' => $stateToken]);
        $result = $this->sut->handleCommand($cmd);

        $this->assertArrayHasKey('error', $result->getFlags());
        $this->assertEquals(Handler::ERR_MISSING_KEY_CLAIM, $result->getFlag('error'));
    }

    public function testHandleCommandInsufficientTrust(): void
    {
        $vot = GovUkAccountService::VOT_P1;

        $code = 'code';
        $stateToken = 'token';
        $accessToken = m::mock(AccessToken::class);
        $userDetailsArray = [GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY_DECODED => ['vot' => $vot]];
        $stateTokenClaims = [
            'returnUrl' => 'test',
            'id' => 100,
            'journey' => 'some_journey'
        ];

        $userDetailsResourceOwner = m::mock(ResourceOwnerInterface::class);
        $userDetailsResourceOwner->expects('toArray')->withNoArgs()->andReturn($userDetailsArray);

        $this->getGovUkAccountService($stateToken, $stateTokenClaims, $code, $accessToken, $userDetailsResourceOwner);

        $this->govUkAccountService->expects('meetsVectorOfTrust')
            ->with($vot, GovUkAccountService::VOT_P2)
            ->andReturnFalse();

        $cmd = Cmd::create(['code' => $code, 'state' => $stateToken]);
        $result = $this->sut->handleCommand($cmd);

        $this->assertArrayHasKey('error', $result->getFlags());
        $this->assertEquals(sprintf(Handler::ERR_INSUFFICIENT_TRUST, GovUkAccountService::VOT_P2, $vot), $result->getFlag('error'));
    }

    public function testHandleCommandMissingJourneyConfig(): void
    {
        $id = 999;
        $digitalSignatureId = 666;
        $code = 'code';
        $stateToken = 'token';
        $jwt = 'JWT';
        $accessToken = m::mock(AccessToken::class);
        $firstName = 'first name';
        $familyName = 'family name';
        $birthDate = '1999-12-25';
        $nameInfo = ['nameInfo'];
        $processedName = ['firstName' => $firstName, 'familyName' => $familyName];
        $vot = GovUkAccountService::VOT_P2;

        $userDetailsArray = $this->getUserDetails($vot, $nameInfo, $birthDate);
        $stateTokenClaims = $this->getStateTokenClaims($id, 'not matched', 'url');

        $userDetailsResourceOwner = m::mock(ResourceOwnerInterface::class);
        $userDetailsResourceOwner->expects('toArray')->withNoArgs()->andReturn($userDetailsArray);

        $this->getGovUkAccountService($stateToken, $stateTokenClaims, $code, $accessToken, $userDetailsResourceOwner);

        $this->govUkAccountService->expects('meetsVectorOfTrust')
            ->with($vot, GovUkAccountService::VOT_P2)
            ->andReturnTrue();
        $this->govUkAccountService->expects('processNames')->with($nameInfo)->andReturn($processedName);

        $this->saveDigitalSignature($digitalSignatureId, $firstName, $familyName, $birthDate, $jwt);

        $cmd = Cmd::create(['code' => $code, 'state' => $stateToken]);
        $result = $this->sut->handleCommand($cmd);

        $this->assertArrayHasKey('error', $result->getFlags());
        $this->assertEquals(Handler::ERR_MISSING_JOURNEY, $result->getFlag('error'));
    }

    public function testHandleCommandGovUkError(): void
    {
        $code = 'code';
        $stateToken = 'token';
        $error = 'error';
        $errorDescription = 'errorDescription';
        $stateTokenClaims = [
            'returnUrl' => 'test',
            'id' => 100,
            'journey' => 'some_journey'
        ];

        $this->govUkAccountService->expects('getStateClaimsFromToken')
            ->with($stateToken)
            ->andReturn($stateTokenClaims);

        $cmd = Cmd::create(['code' => $code, 'state' => $stateToken, 'error' => $error, 'errorDescription' => $errorDescription]);
        $result = $this->sut->handleCommand($cmd);

        $this->assertArrayHasKey('error', $result->getFlags());
        $this->assertEquals("Response from GOV.UK Account contains error/errorDescription: " . json_encode([
                'error' => $error,
                'errorDescription' => $errorDescription,
            ]), $result->getFlag('error'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(string $sideEffectClass, array $sideEffectData, string $journey): void
    {
        $id = 999;
        $digitalSignatureId = 666;
        $code = 'code';
        $stateToken = 'token';
        $jwt = 'JWT';
        $accessToken = m::mock(AccessToken::class);
        $firstName = 'first name';
        $familyName = 'family name';
        $birthDate = '1999-12-25';
        $nameInfo = ['nameInfo'];
        $processedName = ['firstName' => $firstName, 'familyName' => $familyName];
        $redirectUrl = 'https://return/url';
        $vot = GovUkAccountService::VOT_P2;

        $userDetailsArray = $this->getUserDetails($vot, $nameInfo, $birthDate);
        $stateTokenClaims = $this->getStateTokenClaims($id, $journey, $redirectUrl);

        $userDetailsResourceOwner = m::mock(ResourceOwnerInterface::class);
        $userDetailsResourceOwner->expects('toArray')->withNoArgs()->andReturn($userDetailsArray);

        $this->getGovUkAccountService($stateToken, $stateTokenClaims, $code, $accessToken, $userDetailsResourceOwner);

        $this->govUkAccountService->expects('meetsVectorOfTrust')
            ->with($vot, GovUkAccountService::VOT_P2)
            ->andReturnTrue();
        $this->govUkAccountService->expects('processNames')->with($nameInfo)->andReturn($processedName);

        $this->saveDigitalSignature($digitalSignatureId, $firstName, $familyName, $birthDate, $jwt);

        $this->expectedSideEffect($sideEffectClass, $sideEffectData, $this->sideEffectResult('side effect message'));

        $cmd = Cmd::create(['code' => $code, 'state' => $stateToken]);
        $result = $this->sut->handleCommand($cmd);

        $this->assertEquals($digitalSignatureId, $result->getId('DigitalSignature'));
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
        $this->assertEquals($redirectUrl, $result->getFlag('redirect_url'));
    }

    public static function dpHandleCommand(): array
    {
        $id = 999;
        $digitalSignatureId = 666;

        return [
            'application' => [
                UpdateApplication::class,
                [
                    'application' => $id,
                    'digitalSignature' => $digitalSignatureId,
                ],
                RefData::JOURNEY_NEW_APPLICATION,
            ],
            'continuation' => [
                UpdateContinuationDetail::class,
                [
                    'continuationDetail' => $id,
                    'digitalSignature' => $digitalSignatureId,
                ],
                RefData::JOURNEY_CONTINUATION,
            ],
            'surrender' => [
                UpdateSurrender::class,
                [
                    'licence' => $id,
                    'digitalSignature' => $digitalSignatureId,
                ],
                RefData::JOURNEY_SURRENDER,
            ],
            'tm-application' => [
                UpdateTmApplication::class,
                [
                    'application' => $id,
                    'digitalSignature' => $digitalSignatureId,
                    'role' => 'role',
                ],
                RefData::JOURNEY_TM_APPLICATION,
            ],
        ];
    }

    private function getGovUkAccountService(
        string $stateToken,
        array $stateTokenClaims,
        string $code,
        m\MockInterface $accessToken,
        m\MockInterface $userDetailsResourceOwner
    ): void {
        $this->govUkAccountService->expects('getStateClaimsFromToken')
            ->with($stateToken)
            ->andReturn($stateTokenClaims);
        $this->govUkAccountService->expects('getAccessToken')->with($code)->andReturn($accessToken);
        $this->govUkAccountService->expects('getUserDetails')
            ->with($accessToken)
            ->andReturn($userDetailsResourceOwner);
    }

    private function expectedResultMessages(): array
    {
        return [
            0 => 'Digital signature created',
            1 => 'side effect message',
        ];
    }

    private function getStateTokenClaims(int $id, string $journey, string $url): array
    {
        return [
            'id' => $id,
            'journey' => $journey,
            'role' => 'role',
            'returnUrl' => $url,
        ];
    }

    private function getUserDetails(string $vot, array $nameInfo, string $birthDate): array
    {
        return [
            GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY => 'JWT',
            GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY_DECODED => [
                'vot' => $vot,
                'vc' => [
                    'credentialSubject' => [
                        'name' => $nameInfo,
                        'birthDate' => [
                            [
                                'value' => $birthDate,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function saveDigitalSignature(
        int $digitalSignatureId,
        string $firstName,
        string $familyName,
        string $birthDate,
        string $jwt
    ): void {
        $this->repoMap['DigitalSignature']->expects('save')->andReturnUsing(
            function ($digitalSignature) use ($digitalSignatureId, $firstName, $familyName, $birthDate, $jwt) {
                assert($digitalSignature instanceof DigitalSignature);

                $expectedAttributes = [
                    Attributes::FIRST_NAME => $firstName,
                    Attributes::SURNAME => $familyName,
                    Attributes::DATE_OF_BIRTH => $birthDate,
                ];

                $this->assertSame($expectedAttributes, $digitalSignature->getAttributesArray());
                $this->assertSame($jwt, $digitalSignature->getSamlResponse());
                $digitalSignature->setId($digitalSignatureId);
            }
        );
    }
}
