<?php

namespace Olcs\Service\Data;

use Common\Exception\ResourceNotFoundException;
use Common\Service\Data\AbstractDataService;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CasesDto;

/**
 * Class Cases
 *
 * @package Olcs\Service\Data
 */
class Cases extends AbstractDataService
{
    /**
     * Fetch case data
     *
     * @param int $id Id
     *
     * @return array
     * @throw ResourceNotFoundException
     */
    public function fetchData($id)
    {
        if (is_null($this->getData($id))) {
            $response = $this->handleQuery(
                CasesDto::create(['id' => $id])
            );

            if (!$response->isOk()) {
                throw new ResourceNotFoundException('Case not found');
            }

            $this->setData($id, $response->getResult());
        }

        return $this->getData($id);
    }
}
