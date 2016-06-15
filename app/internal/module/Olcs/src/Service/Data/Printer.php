<?php

/**
 * Printer data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractDataService;
use Dvsa\Olcs\Transfer\Query\Printer\PrinterList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * Printer data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Printer extends AbstractDataService implements ListDataInterface
{
    /**
     * Format data
     *
     * @param array $data data
     *
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['printerName'];
        }

        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param int  $category  category
     * @param bool $useGroups use group
     *
     * @return array
     */
    public function fetchListOptions($category, $useGroups = false)
    {
        $data = $this->fetchListData();

        if (!$data) {
            return [];
        }

        return $this->formatData($data);
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @return array
     */
    public function fetchListData()
    {
        if (is_null($this->getData('Printer'))) {
            $dtoData = PrinterList::create([]);

            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }
            $this->setData('Printer', false);
            $result = $response->getResult();
            if (isset($result['results'])) {
                $this->setData('Printer', $result['results']);
            }
        }

        return $this->getData('Printer');
    }
}
