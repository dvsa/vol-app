<?php

namespace Olcs\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Ebsr implements FactoryInterface
{
    /**
     * @var \Zend\InputFilter\Input
     */
    protected $validationChain;

    /**
     * @var Data\EbsrPack
     */
    protected $dataService;

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
     * @param \Olcs\Service\Data\EbsrPack $dataService
     * @return $this
     */
    public function setDataService($dataService)
    {
        $this->dataService = $dataService;
        return $this;
    }

    /**
     * @return \Olcs\Service\Data\EbsrPack
     */
    public function getDataService()
    {
        return $this->dataService;
    }

    /**
     * @param $data
     * @return array
     */
    public function processPackUpload($data)
    {
        $packs = $this->validatePacks($data);

        if (!count($packs)) {
            return ['errors' =>['No packs were found in your upload, please verify your file and try again']];
        }

        try {
            $packResults = $this->getDataService()->sendPackList($packs);
        } catch (\RuntimeException $e) {
            return ['errors' => [$e->getMessage()]];
        }

        return $this->handleResult($packResults);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setValidationChain($serviceLocator->get('Olcs\InputFilter\EbsrPackInput'));
        $this->setDataService($serviceLocator->get('DataServiceManager')->get('Olcs\Service\Data\EbsrPack'));

        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    protected function validatePacks($data)
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

        return $packs;
    }

    /**
     * @param $packResults
     * @return array
     */
    protected function handleResult($packResults)
    {
        $packs = $packResults['valid'] + $packResults['errors'];

        $message = sprintf('%d %s successfully submitted for processing', $packs, ($packs > 1) ? ' packs' : ' pack');

        $validMessage = sprintf(
            '<br />%d %s validated successfully',
            $packResults['valid'],
            ($packResults['valid'] > 1) ? 'packs' : 'pack'
        );

        $errorMessage = sprintf(
            '<br />%d %s contained errors',
            $packResults['errors'],
            ($packResults['errors'] > 1) ? ' packs' : ' pack'
        );

        $result = [
            'success' =>
                $message . ($packResults['valid'] ? $validMessage : '') . ($packResults['errors'] ? $errorMessage : '')
        ];


        foreach ($packResults['messages'] as $pack => $errors) {
            $result['errors'][] = $pack . ': ' . implode(' ', $errors);
        }

        return $result;
    }
}
