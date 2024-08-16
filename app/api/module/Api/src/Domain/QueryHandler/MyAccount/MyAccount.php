<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\MyAccount;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SysParamRepo;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Olcs\Logging\Log\Logger;

/**
 * MyAccount
 */
class MyAccount extends AbstractQueryHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    protected $extraRepos = ['SystemParameter'];

    /**
     * Handle my account query
     *
     * @param QueryInterface $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        $user = $this->getCurrentUser();

        if ($user === null) {
            throw new NotFoundException('No user currently logged in');
        }

        $userId = $user->getId();

        if ($userId === null) {
            $userId = User::USER_TYPE_ANON;
        }

        if ($this->cacheService->hasCustomItem(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $userId)) {
            Logger::debug('retrieving user account for ' . $userId . ' from cache');
            return $this->cacheService->getCustomItem(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $userId);
        }

        $isEligibleForPermits = false;
        $isEligibleForPrompt = false;
        $hasActivePsv = false;
        $hasSubmittedLicenceApplication = false;
        $numVehicles = 0;

        $dataAccess = [];
        $isInternal = $user->isInternal();

        if ($isInternal) {
            $teamDataExclusionsParam = $this->getCacheById(
                CacheEncryption::SYS_PARAM_IDENTIFIER,
                SystemParameter::DATA_SEPARATION_TEAMS_EXEMPT
            );

            $teamsExcluded = explode(",", (string) $teamDataExclusionsParam->getObject()->getParamValue());

            $team = $user->getTeam();

            $dataAccess = [
                'canAccessAll' => $team->canAccessAllData($teamsExcluded),
                'canAccessGb' => $team->canAccessGbData($teamsExcluded),
                'canAccessNi' => $team->canAccessNiData($teamsExcluded),
                'trafficAreas' => $team->getAllowedTrafficAreas($teamsExcluded),
                'isIrfo' => $team->getIsIrfo($teamsExcluded),
                'allowedSearchIndexes' => $team->getAllowedSearchIndexes($teamsExcluded),
            ];
        } elseif ($userId !== User::USER_TYPE_ANON) {
            $isEligibleForPermits = $user->isEligibleForPermits();

            if ($isEligibleForPermits) {
                //for now we need to leave this, as selfserve users don't have access to system param query
                $systemParameterRepo = $this->getRepo('SystemParameter');
                assert($systemParameterRepo instanceof SysParamRepo);
                $isEligibleForPrompt = $systemParameterRepo->isSelfservePromptEnabled();
            }

            $hasActivePsv = $user->hasActivePsvLicence();
            $numVehicles = $user->getNumberOfVehicles();
            $hasSubmittedLicenceApplication = $user->hasOrganisationSubmittedLicenceApplication();
        }

        $result = $this->result(
            $user,
            [
                'team',
                'transportManager',
                'partnerContactDetails',
                'localAuthority',
                'contactDetails' => [
                    'person' => ['title'],
                    'address' => ['countryCode'],
                    'phoneContacts' => ['phoneContactType']
                ],
                'organisationUsers' => [
                    'organisation',
                ],
                'roles' => ['role']
            ],
            [
                'hasActivePsvLicence' => $hasActivePsv,
                'numberOfVehicles' => $numVehicles,
                'hasOrganisationSubmittedLicenceApplication' => $hasSubmittedLicenceApplication,
                'eligibleForPermits' => $isEligibleForPermits,
                'eligibleForPrompt' => $isEligibleForPrompt,
                'dataAccess' => $dataAccess,
                'isInternal' => $isInternal,
            ]
        );

        $this->cacheService->setCustomItem(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $result->serialize(), $userId);
        return $result;
    }
}
