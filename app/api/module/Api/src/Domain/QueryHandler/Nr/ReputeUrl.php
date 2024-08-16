<?php

/**
 * Repute Url
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Nr;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification as TmQualificationEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\NationalRegisterAwareInterface;
use Dvsa\Olcs\Api\Domain\NationalRegisterAwareTrait;

/**
 * Repute Url
 */
class ReputeUrl extends AbstractQueryHandler implements NationalRegisterAwareInterface
{
    use NationalRegisterAwareTrait;

    protected $repoServiceName = 'TransportManager';

    public const DATE_FORMAT = 'd/m/Y';
    public const FIELD_CA = 'Traffic Commissioner';
    public const FIELD_TARGET = 'ZZ';
    public const FIELD_QUAL_UNKNOWN = 'Unknown';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $repo TransportManagerRepo */
        $repo = $this->getRepo();

        /* @var $transportManager TransportManagerEntity */
        $transportManager = $repo->fetchUsingId($query);

        if (!$transportManager->hasReputeCheckData()) {
            return ['reputeUrl' => null];
        }

        $person = $transportManager->getHomeCd()->getPerson();

        /** @var ArrayCollection $qualificationArrayCollection */
        $qualificationArrayCollection = $transportManager->getMostRecentQualification();

        /** @var TmQualificationEntity $qualification */
        $qualification = $qualificationArrayCollection->current();
        $countryCode = $qualification->getCountryCode()->getId();
        $serialNo = $qualification->getSerialNo();

        $queryVars = [
            'CA' => self::FIELD_CA,
            'GivenName' => $person->getForename(),
            'FamilyName' => $person->getFamilyName(),
            'DateOfBirth' => date(self::DATE_FORMAT, strtotime((string) $person->getBirthDate())),
            'PlaceOfBirth' => $person->getBirthPlace(),
            'CPCNo' => ($serialNo ?: self::FIELD_QUAL_UNKNOWN),
            'CPCIssueDate' => date(self::DATE_FORMAT, strtotime($qualification->getIssuedDate())),
            'CPCCountry' => ($countryCode === 'GB' ? 'UK' : $countryCode),
            'Target' => self::FIELD_TARGET
        ];

        return ['reputeUrl' => $this->getNationalRegisterConfig()['repute_url']['uri'] . http_build_query($queryVars)];
    }
}
