<?php

namespace Olcs\Service;

use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\ProcessPacks;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Service\Cqrs\Command\CommandSender;

/**
 * Class Ebsr
 * @package Olcs\Service
 */
class Ebsr implements FactoryInterface
{
    /**
     * @var \Zend\InputFilter\Input
     */
    private $validationChain;

    /**
     * @var Data\EbsrPack
     */
    private $dataService;

    /*
     * @var array
     */
    private $filenameMap;

    /**
     * @var CommandSender
     */
    private $commandSender;

    /**
     * @return CommandSender
     */
    public function getCommandSender()
    {
        return $this->commandSender;
    }

    /**
     * @param CommandSender $commandSender
     */
    public function setCommandSender($commandSender)
    {
        $this->commandSender = $commandSender;
    }

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
    public function processPackUpload($data, $submissionType)
    {
        $packs = $this->validatePacks($data);

        if (!count($packs)) {
            return ['errors' =>['No packs were found in your upload, please verify your file and try again']];
        }

        $dtoData = [
            'packs' => $packs,
            'submissionType' => $submissionType
        ];

        $response = $this->getCommandSender()->send(ProcessPacks::create($dtoData));

        if ($response->isOk()) {
            $result = $response->getResult();

            $packResults = [
                'valid' => $result['id']['valid'],
                'errors' => $result['id']['errors'],
                'error_messages' => (array)$result['id']['error_messages']
            ];

            return $this->handleResult($packResults);
        }

        return ['errors' => 'unknown error occurred'];
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
        $this->setCommandSender($serviceLocator->get('CommandSender'));

        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    private function validatePacks($data)
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

                $dtoData = [
                    'content' => base64_encode(file_get_contents($ebsrPack)),
                    'category' => 3,
                    'subCategory' => 36,
                    'filename' => basename($ebsrPack),
                    'description' => 'EBSR pack file',
                    'isExternal' => true
                ];

                $response = $this->getCommandSender()->send(Upload::create($dtoData));

                $documentId = $response->getResult()['id']['document'];

                $packs[] = $documentId;
                $this->filenameMap[$documentId] = $response->getResult()['id']['identifier'];
            }
        }

        return $packs;
    }

    /**
     * @param $packResults
     * @return array
     */
    private function handleResult($packResults)
    {
        $packs = $packResults['valid'] + $packResults['errors'];

        $message = sprintf('%d %s submitted for processing', $packs, ($packs > 1) ? ' packs' : ' pack');

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

        foreach ($packResults['error_messages'] as $message) {
            $result['errors'][] = $message;
        }

        return $result;
    }
}
