<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class EbsrPack
 * @package Olcs\Service\Data
 */
class EbsrPack extends AbstractData
{
    /**
     * @var string
     */
    protected $serviceName = 'ebsr\pack';

    /**
     * Temp stub method
     * @param array $params
     * @param null $bundle
     * @return array
     */
    public function fetchList($params = [], $bundle = null)
    {
        if ($this->getData('list') === null) {

            $this->setData('list', false);
            $operatorId = 1;
            $data = $this->getRestClient()->get(sprintf('list/%d', $operatorId), $params);

            if (isset($data['Results'])) {
                $this->setData('list', $data['Results']);
            }
        }

        return $this->getData('list');
    }

    /**
     * @param $packs
     * @throws \RuntimeException
     * @return array
     */
    public function sendPackList($packs)
    {
        $result = $this->getRestClient()->post('notify', ['organisationId' => 75, 'packs' => $packs]);

        if (!$result) {
            throw new \RuntimeException('Failed to submit packs for processing, please try again');
        }

        $return = ['valid' => 0, 'errors' => 0, 'messages' => []];

        foreach ($result as $pack => $errors) {
            if (empty($errors)) {
                $return['valid'] += 1;
            } else {
                $return['errors'] += 1;
                $return['messages'][$pack] = $errors;
            }
        }

        return $return;
    }
}
