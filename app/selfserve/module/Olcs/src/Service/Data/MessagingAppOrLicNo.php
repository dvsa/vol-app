<?php

declare(strict_types=1);

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

class MessagingAppOrLicNo extends AbstractListDataService
{
    public const PREFIX_APPLICATION = 'A';
    public const PREFIX_LICENCE = 'L';

    /**
     * @throws DataServiceException
     */
    public function fetchListData($context = null): array
    {
        $data = (array)$this->getData('licapp');

        if (count($data) !== 0) {
            return $data;
        }

        $response = $this->handleQuery(
            TransferQry\Messaging\ApplicationLicenceList\ByOrganisation::create([])
        );

        if (!$response->isOk()) {
            throw new DataServiceException('Unknown Error - ' . json_encode($response));
        }

        $result = $response->getResult();

        $this->setData('licapp', ($result['results'] ?? null));

        return $this->getData('licapp');
    }

    #[\Override]
    public function formatDataForGroups(array $data): array
    {
        $optionData = [
            [
                'label' => 'Licence',
                'options' => []
            ],
            [
                'label' => 'Application',
                'options' => []
            ]
        ];

        $optionData[0]['options'] = $data['licences'];
        $optionData[1]['options'] = $data['applications'];

        $this->prefixArrayKey($optionData[0]['options'], static::PREFIX_LICENCE);
        $this->prefixArrayKey($optionData[1]['options'], static::PREFIX_APPLICATION);

        return $optionData;
    }

    private function prefixArrayKey(array &$array, string $prefix): void
    {
        foreach ($array as $k => $v) {
            $array[$prefix . $k] = $v;
            unset($array[$k]);
        }
    }
}
