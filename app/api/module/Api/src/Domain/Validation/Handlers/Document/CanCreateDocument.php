<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\Document\CreateDocument as CreateDocumentDto;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadDto;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * Can Create a Document
 */
class CanCreateDocument extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const EXTENSIONS_KEY_EXTERNAL = 'external';
    public const EXTENSIONS_KEY_INTERNAL = 'internal';

    /**
     * @var bool
     */
    private $valid;

    private $allowedExtensions = [];

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (isset($config['allow_file_upload']['extensions'])) {
            $this->setAllowedExtensions($config['allow_file_upload']['extensions']);
        }

        return parent::__invoke($container, $requestedName, $options);
    }

    /**
     * Validate DTO
     *
     * @param CreateDocumentDto|UploadDto $dto The DTO being validated
     *
     * @return bool
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     */
    public function isValid($dto)
    {
        if ($this->validateExtension($dto->getFilename()) === false) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                [\Dvsa\Olcs\Api\Domain\CommandHandler\Document\Upload::ERR_MIME => 'Invalid extension']
            );
        }

        if ($this->isInternalUser() || $this->isSystemUser()) {
            return true;
        }

        //only the upload version of the DTO is used for EBSR packs, so we need a method_exists here
        if (method_exists($dto, 'getIsEbsrPack') && $dto->getIsEbsrPack()) {
            $this->setIsValid($this->canUploadEbsr($this->getCurrentOrganisation()->getId()));
        }

        if ($dto->getLicence()) {
            $this->setIsValid($this->canAccessLicence($dto->getLicence()));
        }

        if ($dto->getApplication()) {
            $this->setIsValid($this->canAccessApplication($dto->getApplication()));
        }

        if ($dto->getCase()) {
            $this->setIsValid($this->canAccessCase($dto->getCase()));
        }

        if ($dto->getTransportManager()) {
            $this->setIsValid($this->canAccessTransportManager($dto->getTransportManager()));
        }

        if ($dto->getOperatingCentre()) {
            $this->setIsValid($this->canAccessOperatingCentre($dto->getOperatingCentre()));
        }

        if ($dto->getBusReg()) {
            $this->setIsValid($this->canAccessBusReg($dto->getBusReg()));
        }

        if ($dto->getIrfoOrganisation()) {
            $this->setIsValid($this->canAccessOrganisation($dto->getIrfoOrganisation()));
        }

        if ($dto->getSubmission()) {
            $this->setIsValid($this->canAccessSubmission($dto->getSubmission()));
        }

        if ($dto->getContinuationDetail()) {
            $this->setIsValid($this->canAccessContinuationDetail($dto->getContinuationDetail()));
        }

        if ($dto->getMessagingConversation()) {
            $this->setIsValid($this->canAccessConversation($dto->getMessagingConversation()));
        }

        if (!$dto->getMessagingConversation() && $dto->getCorrelationId()) {
            $this->setIsValid($this->getUser()->getRelatedOrganisation()->getIsMessagingFileUploadEnabled());
        }

        return $this->getIsValid();
    }

    /**
     * Set whether the result of the validation
     *
     * @param bool $valid The result of one of the validations
     *
     * @return void
     */
    private function setIsValid($valid)
    {
        if ($this->valid === null) {
            $this->valid = $valid;
        }
        // this is ANDed, as if more than one property is validated, they must all be true
        $this->valid = $this->valid && $valid;
    }

    /**
     * Get the result of the validation
     *
     * @return bool
     */
    private function getIsValid()
    {
        return $this->valid === true;
    }

    /**
     * Set allowed extensions
     *
     * @param array $extensions Array of allowed extensions
     *
     * @return void
     */
    public function setAllowedExtensions(array $extensions)
    {
        $this->allowedExtensions = $extensions;
    }

    /**
     * Validate a filename extension against the configurable allow list
     *
     * @param string $filename File name to check
     *
     * @return bool
     */
    private function validateExtension($filename)
    {
        $key = ($this->isInternalUser() || $this->isSystemUser())
            ? self::EXTENSIONS_KEY_INTERNAL
            : self::EXTENSIONS_KEY_EXTERNAL;

        $allowedExtensions = isset($this->allowedExtensions[$key])
            ? explode(',', (string) $this->allowedExtensions[$key])
            : [];

        $extension = '';
        if (strrpos($filename, '.') !== false) {
            $extension = substr($filename, strrpos($filename, '.') + 1);
        }

        foreach ($allowedExtensions as $ext) {
            if (trim(strtolower($ext)) == trim(strtolower($extension))) {
                return true;
            }
        }

        return false;
    }
}
