<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\RefData as CommonRefData;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as OcQry;

/**
 * Service Class Application
 *
 * @package Common\Service\Data
 */
class Application extends AbstractDataService
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * Wrapper method to match interface.
     *
     * @param int|null $id Id
     *
     * @return array
     */
    public function fetchData($id = null)
    {
        return $this->fetchApplicationData($id);
    }

    /**
     * Fetches application data
     *
     * @param int|null $id Id
     *
     * @return array
     */
    public function fetchApplicationData($id = null)
    {
        $id = is_null($id) ? $this->getId() : $id;

        if (is_null($this->getData($id))) {
            $dtoData = ApplicationQry::create(['id' => $id]);
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
     * Can this entity have cases
     *
     * @param int $id Id
     *
     * @return bool
     */
    public function canHaveCases($id)
    {
        $application = $this->fetchApplicationData($id);
        return !(empty($application['status'])
            || ((is_array($application['status']) ? $application['status']['id'] :
                    $application['status']) === CommonRefData::APPLICATION_STATUS_NOT_SUBMITTED)
            || empty($application['licence']) || empty($application['licence']['licNo']));
    }

    /**
     * Fetches an array of OperatingCentres for the application.
     *
     * @param int|null $id Id
     *
     * @return array
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
     * Set Id
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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
