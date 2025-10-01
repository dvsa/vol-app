<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Surrender;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

final class ByLicence extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_SURRENDER];
    protected $repoServiceName = 'Surrender';
    protected $extraRepos = ['SystemParameter', 'GoodsDisc', 'PsvDisc'];

    /**
     * handleQuery
     *
     * @param QueryInterface $query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $licenceId = $query->getId();
        /** @var Surrender $surrender */
        $surrender = $this->getRepo('Surrender')->fetchOneByLicence($licenceId, Query::HYDRATE_OBJECT);

        $goodsDiscsOnLicence = $this->getRepo('GoodsDisc')->countForLicence($query->getId());
        $psvDiscsOnLicence = $this->getRepo('PsvDisc')->countForLicence($query->getId());

        return $this->result(
            $surrender,
            [
                'licence' => [
                    'correspondenceCd' => [
                        'address' => [
                            'countryCode',
                        ],
                        'phoneContacts' => [
                            'phoneContactType',
                        ]
                    ],
                    'organisation'
                ],
                'status',
                'licenceDocumentStatus',
                'communityLicenceDocumentStatus',
                'digitalSignature',
                'signatureType'
            ],
            [
                'goodsDiscsOnLicence' => $goodsDiscsOnLicence,
                'psvDiscsOnLicence' => $psvDiscsOnLicence,
                'addressLastModified' => $surrender->getLicence()->getCorrespondenceCd()->getAddress()->getLastModifiedOn(),
                'isInternationalLicence' => $surrender->getLicence()->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ]
        );
    }
}
