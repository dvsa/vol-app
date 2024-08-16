<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Fee;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Query\Fee\FeeTypeList as FeeTypeListQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Fee Type List
 */
class FeeTypeList extends AbstractQueryHandler
{
    /**
     * @var \Dvsa\Olcs\Api\Service\FeesHelperService
     */
    protected $feesHelper;

    protected $repoServiceName = 'FeeType';

    protected $extraRepos = ['IrfoGvPermit', 'IrfoPsvAuth'];

    /**
     * Handle query
     *
     * @param FeeTypeListQry $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\FeeType $repo */
        $repo = $this->getRepo();

        $feeTypes = $repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT);

        // get fee types where traffic area *is not* specified
        $genericFeetypes = $this->filterDuplicates($feeTypes, null);

        // get fee types where traffic area *is* specified
        $specificFeeTypes = [];

        $trafficArea = $this->getTrafficArea($query);
        if ($trafficArea !== null) {
            $specificFeeTypes = $this->filterDuplicates($feeTypes, $trafficArea);
        }

        // merge fee types together so that fee types with specified traffic area will overwrite non specified
        $filteredFeeTypes = $specificFeeTypes + $genericFeetypes;

        $valueOptions = [
            'irfoGvPermit' => $this->getIrfoGvPermitValueOptions($query),
            'irfoPsvAuth' => $this->getIrfoPsvAuthValueOptions($query),
        ];

        /** @var \Dvsa\Olcs\Api\Entity\Fee\FeeType $ft */
        foreach ($filteredFeeTypes as $ft) {
            $valueOptions['feeType'][$ft->getId()] = $ft->getDescription();
        }

        $showQuantity = false;
        $showVatRate = false;
        if ($query->getCurrentFeeType()) {
            $feeType = $this->getRepo()->fetchById($query->getCurrentFeeType());
            $showQuantity = $feeType->isShowQuantity();
            $showVatRate = ((float) $feeType->getVatRate() > 0);
        }

        return [
            'result' => $this->resultList($filteredFeeTypes),
            'count' => count($filteredFeeTypes),
            'valueOptions' => $valueOptions,
            'showQuantity' => $showQuantity,
            'showVatRate' => $showVatRate
        ];
    }

    /**
     * Get a Traffic area from the query params
     *
     * @param FeeTypeListQry $query query
     *
     * @return TrafficArea|null
     */
    private function getTrafficArea(FeeTypeListQry $query)
    {
        /* @var $licence Licence */
        $licence = $this->getRepo()->getReference(Licence::class, $query->getLicence());
        if ($licence) {
            return $licence->getTrafficArea();
        }

        /* @var $application Application */
        $application = $this->getRepo()->getReference(Application::class, $query->getApplication());
        if ($application) {
            return $application->getLicence()->getTrafficArea();
        }

        return null;
    }

    /**
     * This is in lieu of being able to do proper groupwise max in the
     * repository method using Doctrine
     *
     * @param \ArrayIterator $feeTypes    Array of fee types
     * @param TrafficArea    $trafficArea TrafficArea to get fee types for, default to null
     *
     * @return array
     */
    private function filterDuplicates($feeTypes, TrafficArea $trafficArea = null)
    {
        $filtered = [];

        /* @var $ft \Dvsa\Olcs\Api\Entity\Fee\FeeType */
        foreach ($feeTypes as $ft) {
            if ($ft->getTrafficArea() !== $trafficArea) {
                continue;
            }

            // group by irfoFeeTypeId and feeTypeId
            $ifroType = $ft->getIrfoFeeType();
            $key = ($ifroType ? $ifroType->getId() . '|' : '') . $ft->getFeeType()->getId();

            if (
                !isset($filtered[$key])
                || $ft->getEffectiveFrom() > $filtered[$key]->getEffectiveFrom()
            ) {
                $filtered[$key] = $ft;
            }
        }

        return $filtered;
    }

    /**
     * Get IRFO GV Permit value options
     *
     * @param FeeTypeListQry $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function getIrfoGvPermitValueOptions(FeeTypeListQry $query)
    {
        $valueOptions = [];
        if ($query->getOrganisation() !== null) {
            $organisation = $this->getRepo()->getReference(OrganisationEntity::class, $query->getOrganisation());

            /** @var \Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit $repo */
            $repo = $this->getRepo('IrfoGvPermit');

            /** @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit $irfoGvPermit */
            $irfoGvPermits = $repo->fetchByOrganisation($organisation);

            foreach ($irfoGvPermits as $irfoGvPermit) {
                $valueOptions[$irfoGvPermit->getId()] = sprintf(
                    '%d (%s)',
                    $irfoGvPermit->getId(),
                    $irfoGvPermit->getIrfoGvPermitType()->getDescription()
                );
            }
        }
        return $valueOptions;
    }

    /**
     * Get IRFO PSV auth value options
     *
     * @param FeeTypeListQry $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function getIrfoPsvAuthValueOptions(FeeTypeListQry $query)
    {
        $valueOptions = [];
        if ($query->getOrganisation() !== null) {
            $organisation = $this->getRepo()->getReference(OrganisationEntity::class, $query->getOrganisation());

            /** @var \Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth $repo */
            $repo = $this->getRepo('IrfoPsvAuth');

            /** @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth $irfoPsvAuth */
            $irfoPsvAuths = $repo->fetchByOrganisation($organisation);

            foreach ($irfoPsvAuths as $irfoPsvAuth) {
                $valueOptions[$irfoPsvAuth->getId()] = sprintf(
                    '%d (%s)',
                    $irfoPsvAuth->getId(),
                    $irfoPsvAuth->getIrfoPsvAuthType()->getDescription()
                );
            }
        }
        return $valueOptions;
    }
}
