<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Dvsa\Olcs\Transfer\Query\Licence\OperatingCentres as OcQry;

/**
 * Class Licence
 *
 * @package Olcs\Service\Data
 */
class Licence extends AbstractDataService
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * Fetch the licence data
     *
     * @param null $id licence id
     *
     * @return array|mixed|null
     * @throws DataServiceException
     */
    public function fetchLicenceData($id = null)
    {
        $id = is_null($id) ? $this->getId() : $id;

        if (empty($id)) {
            return [];
        }

        if (is_null($this->getData($id))) {
            $dtoData = LicenceQry::create(['id' => $id]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $data = $response->getResult();
            $this->setData($id, $data);
        }

        return $this->getData($id);
    }

    /**
     * Fetches an array of OperatingCentres for the licence.
     *
     * @param int|null $id Id
     *
     * @return mixed|null
     * @throws DataServiceException
     */
    public function fetchOperatingCentreData($id = null)
    {
        $id = is_null($id) ? $this->getId() : $id;

        if (is_null($this->getData('oc_' . $id))) {
            $dtoData = OcQry::create(['id' => $id, 'sort' => 'id', 'order' => 'ASC']);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $data = $response->getResult();

            $this->setData('oc_' . $id, $data);
        }

        return $this->getData('oc_' . $id);
    }

    /**
     * Set id
     *
     * @param int $id Id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
