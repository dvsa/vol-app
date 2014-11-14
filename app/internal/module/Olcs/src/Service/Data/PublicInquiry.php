<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Zend\Filter\Word\DashToCamelCase;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Service\Data\CloseableInterface;

/**
 * Class PublicInquiry
 * @package Olcs\Service
 */
class PublicInquiry extends AbstractData //implements CloseableInterface
{
    use CloseButtonTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $serviceName = 'PublicInquiry';

    /**
     * Create PublicInquiry service with injected ref data service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return PublicInquiry
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        parent::createService($serviceLocator);

        return $this;
    }

    /**
     * Fetch PublicInquiry data
     *
     * @param integer|null $id
     * @param array|null $bundle
     * @return array
     */
    public function fetchData($id = null, $bundle = null)
    {
        $id = is_null($id) ? $this->getId() : $id;
        if (is_null($this->getData($id))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data =  $this->getRestClient()->get(sprintf('/%d', $id), ['bundle' => json_encode($bundle)]);
            $this->setData($id, $data);
        }

        return $this->getData($id);
    }

    /**
     * Returns the bundle required to get a submission
     * @return array
     */
    public function getBundle()
    {
        $bundle =  array(
            'properties' => 'ALL',
        );

        return $bundle;
    }


    public function closeEntity($id)
    {
        $data = $this->fetchData($id);
        $now = date('Y-m-d h:i:s');

        $this->getRestClient()->update(
            $data['id'],
            [
                'data' => json_encode(
                    [
                        'version' => $data['version'],
                        'closedDate' => $now
                    ]
                )
            ]
        );
    }

    public function reopenEntity($id)
    {
        $data = $this->fetchData($id);
        $now = null;

        $this->getRestClient()->update(
            $data['id'],
            [
                'data' => json_encode(
                    [
                        'version' => $data['version'],
                        'closedDate' => $now
                    ]
                )
            ]
        );
    }

    /**
     * Can this entity be closed
     * @param $id
     * @return bool
     */
    public function canClose($id)
    {
        return !$this->isClosed($id);
    }

    /**
     * Is this entity closed
     * @param $id
     * @return bool
     */
    public function isClosed($id)
    {
        $submission = $this->fetchData($id);
        return (bool) isset($submission['closedDate']);
    }

    /**
     * Can this entity be reopened
     * @param $id
     * @return bool
     */
    public function canReopen($id)
    {
        return $this->isClosed($id);
    }

}
