<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GovUkAccount;

use Dvsa\GovUkAccount\Provider\GovUkAccountUser;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateApplication;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateContinuationDetail;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateSurrender;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateTmApplication;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\GovUkAccount\Data\Attributes;
use Dvsa\Olcs\Api\Service\GovUkAccount\GovUkAccountService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\ProcessAuthResponse as ProcessAuthResponseCmd;
use Olcs\Logging\Log\Logger;

class ProcessAuthResponse extends AbstractCommandHandler implements TransactionedInterface
{
    public const ERR_MISSING_KEY_CLAIM = 'Unable to verify user identity OR key claims core identity does not exist';
    public const ERR_MISSING_JOURNEY = 'Journey not matched or not specified';
    public const ERR_INSUFFICIENT_TRUST = 'We require %s level of trust, actual level of trust was %s';

    protected $repoServiceName = 'DigitalSignature';

    public function __construct(private readonly GovUkAccountService $govUkAccountService)
    {
    }

    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof ProcessAuthResponseCmd);

        try {
            $code = $command->getCode();
            $token = $command->getState();
            $error = $command->getError();
            $errorDescription = $command->getErrorDescription();

            // Ensure we process the state token first as this will contain our redirect URL even during an error
            $stateTokenClaims = $this->govUkAccountService->getStateClaimsFromToken($token);

            $this->result->setFlag('redirect_url', $stateTokenClaims['returnUrl']);
            $this->result->setFlag('redirect_url_on_error', $stateTokenClaims['returnUrlOnError'] ?? null);
            $this->result->setFlag('entity_id', $stateTokenClaims['id']);
            $this->result->setFlag('journey', $stateTokenClaims['journey']);

            if (!empty($error) || !empty($errorDescription)) {
                throw new \Exception("Response from GOV.UK Account contains error/errorDescription: " . json_encode([
                    'error' => $error,
                    'errorDescription' => $errorDescription,
                ]));
            }

            $accessToken = $this->govUkAccountService->getAccessToken($code);
            $userDetails = $this->govUkAccountService->getUserDetails($accessToken)->toArray();

            if (!isset($userDetails[GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY_DECODED])) {
                throw new \Exception(self::ERR_MISSING_KEY_CLAIM);
            }

            $vot = $userDetails[GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY_DECODED]['vot'];

            $meetsVectorOfTrust = $this->govUkAccountService->meetsVectorOfTrust($vot, GovUkAccountService::VOT_P2);

            if (!$meetsVectorOfTrust) {
                $message = sprintf(self::ERR_INSUFFICIENT_TRUST, GovUkAccountService::VOT_P2, $vot);
                throw new \Exception($message);
            }

            $userAttributes = $userDetails[GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY_DECODED]['vc']['credentialSubject'];
            $name = $this->govUkAccountService->processNames($userAttributes['name']);

            //this is done to match the format of the previous govuk verify
            $attributes = new Attributes(
                [
                    Attributes::FIRST_NAME => $name['firstName'] ?? null,
                    Attributes::SURNAME => $name['familyName'] ?? null,
                    Attributes::DATE_OF_BIRTH => $userAttributes['birthDate'][0]['value'] ?? null,
                ]
            );

            $digitalSignature = new Entity\DigitalSignature();
            $digitalSignature->addSignatureInfo($attributes, $userDetails[GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY]);
            $this->getRepo()->save($digitalSignature);

            $digitalSignatureId = $digitalSignature->getId();

            $this->result->addId('DigitalSignature', $digitalSignatureId);
            $this->result->addMessage('Digital signature created');

            $sideEffectCmd = match ($stateTokenClaims['journey']) {
                RefData::JOURNEY_NEW_APPLICATION => UpdateApplication::create(
                    [
                        'application' => $stateTokenClaims['id'],
                        'digitalSignature' => $digitalSignatureId,
                    ]
                ),
                RefData::JOURNEY_CONTINUATION => UpdateContinuationDetail::create(
                    [
                        'continuationDetail' => $stateTokenClaims['id'],
                        'digitalSignature' => $digitalSignatureId,
                    ]
                ),
                RefData::JOURNEY_SURRENDER => UpdateSurrender::create(
                    [
                        'licence' => $stateTokenClaims['id'],
                        'digitalSignature' => $digitalSignatureId,
                    ]
                ),
                RefData::JOURNEY_TM_APPLICATION => UpdateTmApplication::create(
                    [
                        'application' => $stateTokenClaims['id'],
                        'digitalSignature' => $digitalSignatureId,
                        'role' => $stateTokenClaims['role'],
                    ]
                ),
                default => throw new \Exception(self::ERR_MISSING_JOURNEY),
            };

            $this->result->merge(
                $this->handleSideEffect($sideEffectCmd)
            );
        } catch (\Exception $e) {
            Logger::logException($e, \Laminas\Log\Logger::ERR);
            $this->result->setFlag('error', $e->getMessage());
        }

        return $this->result;
    }
}
