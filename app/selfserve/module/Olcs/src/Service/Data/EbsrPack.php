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
     * @var \Zend\InputFilter\Input
     */
    protected $validationChain;

    /**
     * @var string
     */
    protected $serviceName = 'ebsr\pack';

    /**
     * @param \Zend\InputFilter\Input $validationChain
     * @return $this
     */
    public function setValidationChain($validationChain)
    {
        $this->validationChain = $validationChain;
        return $this;
    }

    /**
     * @return \Zend\InputFilter\Input
     */
    public function getValidationChain()
    {
        return $this->validationChain;
    }

    /**
     * Temp stub method
     * @return array
     */
    public function fetchPackList()
    {
        return [
            [
                'status' => 'Recieved',
                'filename' => 'PB000679.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Processing',
                'filename' => 'PB000678.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Distributing',
                'filename' => 'PB000677.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Complete',
                'filename' => 'PB000676.zip',
                'submitted' => '2014-10-07'
            ]

        ];
    }

    /**
     * @param $data
     * @return string|array
     */
    public function processPackUpload($data)
    {
        $validator = $this->getValidationChain();

        $dir = new \FilesystemIterator(
            $data['fields']['file']['extracted_dir'],
            \FilesystemIterator::CURRENT_AS_PATHNAME
        );

        $packs = [];

        foreach ($dir as $ebsrPack) {
            $validator->setValue($ebsrPack);
            if ($validator->isValid()) {
                $newName = preg_replace('#/zip[a-z0-9]+/#i', '/ebsr/', $ebsrPack);
                $packs[] = $newName;
                rename($ebsrPack, $newName);
            }
        }

        if (!count($packs)) {
            return 'No packs were found in your upload, please verify your file and try again';
        }

        return $this->sendPackList($packs);
    }

    /**
     * @param $packs
     * @return array
     */
    public function sendPackList($packs)
    {
        $result = $this->getRestClient()->post('notify', ['operatorId' => 1, 'packs' => $packs]);

        if (!$result) {
            return 'Failed to submit packs for processing, please try again';
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

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $this->setValidationChain($serviceLocator->get('Olcs\InputFilter\EbsrPackInput'));

        return $this;
    }
}
