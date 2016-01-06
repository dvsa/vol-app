<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractData;
use Common\Service\Data\LicenceServiceTrait;
use Dvsa\Olcs\Transfer\Query\RefData\RefDataList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Service\Data\ListDataTrait;

/**
 * Class ImpoundingLegislation
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ImpoundingLegislation extends AbstractData implements ListDataInterface, ServiceLocatorAwareInterface
{
    use LicenceServiceTrait,
        ServiceLocatorAwareTrait,
        ListDataTrait;

    /**
    * @param mixed $context
    * @param bool $useGroups
    * @return array|void
    */
    public function fetchListOptions($context, $useGroups = false)
    {
        $context = empty($context)? $this->getLicenceContext() : $context;

        //decide which ref data category we need
        if (empty($context)) {
            $data = $this->fetchListData('impound_legislation_goods_gb');
        } elseif ($context['goodsOrPsv'] == 'lcat_psv') {
            $data = $this->fetchListData('impound_legislation_psv_gb');
        } elseif ($context['isNi'] == 'Y') {
            $data = $this->fetchListData('impound_legislation_goods_ni');
        } else {
            $data = $this->fetchListData('impound_legislation_goods_gb');
        }

        if (!is_array($data)) {
            return [];
        }

        return $this->formatData($data);
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $category
     * @return array
     */
    public function fetchListData($category)
    {
        if (is_null($this->getData($category))) {

            $languagePreferenceService = $this->getServiceLocator()->get('LanguagePreference');
            $params = [
                'refDataCategory' => $category,
                'language' => $languagePreferenceService->getPreference()
            ];
            $dtoData = RefDataList::create($params);

            $response = $this->handleQuery($dtoData);
            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }
            $this->setData($category, false);
            if (isset($response->getResult()['results'])) {
                $this->setData($category, $response->getResult()['results']);
            }
        }

        return $this->getData($category);
    }

    protected function handleQuery($dtoData)
    {
        $annotationBuilder = $this->getServiceLocator()->get('TransferAnnotationBuilder');
        $queryService = $this->getServiceLocator()->get('QueryService');

        $query = $annotationBuilder->createQuery($dtoData);
        return $queryService->send($query);
    }
}
