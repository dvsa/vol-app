<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GdsVerify;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateApplication;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateContinuationDetail;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateSurrender;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateTmApplication;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\GdsVerify;
use Psr\Container\ContainerInterface;

/**
 * ProcessResponse
 */
class ProcessSignatureResponse extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'DigitalSignature';

    /** @var  \Dvsa\Olcs\GdsVerify\Service\GdsVerify */
    private $gdsVerifyService;

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse $command Command to handle
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        try {
            $attributes = $this->getGdsVerifyService()->getAttributesFromResponse($command->getSamlResponse());
        } catch (GdsVerify\Exception $e) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\RuntimeException($e->getMessage(), 0, $e);
        }

        if (!$attributes->isValidSignature()) {
            // Not sure this is the right response code, for signature not valid? But it will work
            throw new \Dvsa\Olcs\Api\Domain\Exception\RuntimeException(
                'Verify response does not qualify as a valid signature'
            );
        }
        $digitalSignature = new Entity\DigitalSignature();
        $digitalSignature->setAttributesArray($attributes->getArrayCopy())
            ->setSamlResponse(base64_decode($command->getSamlResponse()));
        $this->getRepo()->save($digitalSignature);
        $digitalSignatureId = $digitalSignature->getId();
        $this->result->addMessage('Digital signature created');

        if ($command->getApplication() && empty($command->getTransportManagerApplication())) {
            $this->result->merge(
                $this->handleSideEffect(
                    UpdateApplication::create(
                        ['application' => $command->getApplication(), 'digitalSignature' => $digitalSignatureId]
                    )
                )
            );
        }

        if ($command->getContinuationDetail()) {
            $this->result->merge(
                $this->handleSideEffect(
                    UpdateContinuationDetail::create(
                        [
                            'continuationDetail' => $command->getContinuationDetail(),
                            'digitalSignature' => $digitalSignatureId,
                        ]
                    )
                )
            );
        }

        if ($command->getTransportManagerApplication()) {
            $this->result->merge(
                $this->handleSideEffect(
                    UpdateTmApplication::create(
                        [
                            'application' => $command->getTransportManagerApplication(),
                            'digitalSignature' => $digitalSignatureId,
                            'role' => $command->getRole(),
                        ]
                    )
                )
            );
        }

        if ($command->getLicence()) {
            $this->result->merge(
                $this->handleSideEffect(
                    UpdateSurrender::create(
                        ['licence' => $command->getLicence(), 'digitalSignature' => $digitalSignatureId]
                    )
                )
            );
        }

        return $this->result;
    }

    /**
     * Get the GDS Verify service
     *
     * @return \Dvsa\Olcs\GdsVerify\Service\GdsVerify
     */
    public function getGdsVerifyService()
    {
        return $this->gdsVerifyService;
    }

    /**
     * Set the GDS Verify service
     *
     * @param \Dvsa\Olcs\GdsVerify\Service\GdsVerify $gdsVerifyService Verify Service
     *
     * @return void
     */
    public function setGdsVerifyService(\Dvsa\Olcs\GdsVerify\Service\GdsVerify $gdsVerifyService)
    {
        $this->gdsVerifyService = $gdsVerifyService;
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->setGdsVerifyService($container->get(\Dvsa\Olcs\GdsVerify\Service\GdsVerify::class));
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
