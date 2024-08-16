<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\ApplicationLicenceList;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByOrganisation as ByOrganisationQuery;

class ByOrganisation extends AbstractQueryHandler implements ToggleRequiredInterface, AuthAwareInterface
{
    use ToggleAwareTrait;
    use AuthAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];

    protected $extraRepos = [Repository\Application::class, Repository\Licence::class];

    /**
     * Fetch a list of licences and applications associated with an organisation.
     *
     * If an organisation is not defined in the query, we fall back to the current identity's organisation.
     *
     * @param ByOrganisationQuery $query
     */
    public function handleQuery(QueryInterface $query): array
    {
        $licenceRepository = $this->getLicenceRepository();
        $applicationRepository = $this->getApplicationRepository();

        $orgId = (int)($query->getOrganisation() ?: $this->getCurrentOrganisation()->getId());

        $licences = $licenceRepository->fetchByOrganisationIdAndStatuses(
            $orgId,
            [Licence::LICENCE_STATUS_VALID, Licence::LICENCE_STATUS_SUSPENDED, Licence::LICENCE_STATUS_CURTAILED],
        );

        /** @var Application[] $applications */
        $applications = $applicationRepository->fetchByOrganisationIdAndStatuses(
            $orgId,
            [Entity::APPLICATION_STATUS_UNDER_CONSIDERATION, Entity::APPLICATION_STATUS_UNDER_CONSIDERATION],
        );

        $results = array_fill_keys(['licences', 'applications'], []);

        // Sort by app / licence
        // Select uses label => id

        foreach ($licences as $licence) {
            if (empty($licence['licNo'])) {
                continue;
            }
            $results['licences'][$licence['id']] = $licence['licNo'];
        }

        foreach ($applications as $application) {
            $licence = '';
            if ($application->getLicence() && $application->getLicence()->getLicNo()) {
                $licence = $application->getLicence()->getLicNo() . ' / ';
            }
            $results['applications'][$application->getId()] = $licence . $application->getId();
        }

        return [
            'result' => $results
        ];
    }

    public function getLicenceRepository(): Repository\Licence
    {
        return $this->getRepo(Repository\Licence::class);
    }

    public function getApplicationRepository(): Repository\Application
    {
        return $this->getRepo(Repository\Application::class);
    }
}
