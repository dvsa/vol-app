<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class ApplicationStatus extends AbstractListDataService
{
    /** @var  int */
    protected $orgId;

    /**
     * Fetch list data
     *
     * @param array $context Parameters
     *
     * @return array
     * @throw DataServiceException
     */
    public function fetchListData($context = null)
    {
        $key = 'application-status-' . $this->getOrgId();

        $data = (array)$this->getData($key);
        if (0 !== count($data)) {
            return $data;
        }

        $response = $this->handleQuery(
            TransferQry\DataService\ApplicationStatus::create(
                [
                    'organisation' => $this->getOrgId(),
                ]
            )
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();

        $this->setData($key, ($result['results'] ?? null));

        return $this->getData($key);
    }

    /**
     * Get Organisation identifier
     *
     * @return int
     */
    public function getOrgId()
    {
        return $this->orgId;
    }

    /**
     * Set Organisation identifier
     *
     * @param int $orgId Organisation id
     *
     * @return $this
     */
    public function setOrgId($orgId)
    {
        $this->orgId = $orgId;
        return $this;
    }
}
