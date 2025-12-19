<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareTrait;
use Dvsa\Olcs\Api\Service\Nr\InrClient;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\SendResponse as SendResponseCmd;
use Laminas\Http\Response;
use Laminas\Http\Client\Adapter\Exception\RuntimeException as AdapterRuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\InrClientException;

final class SendResponse extends AbstractCommandHandler implements UploaderAwareInterface
{
    use UploaderAwareTrait;

    protected $repoServiceName = 'ErruRequest';

    protected $extraRepos = [
        'Document'
    ];

    public function __construct(private readonly InrClient $inrClient)
    {
    }

    /**
     * SendResponse
     *
     * @param CommandInterface $command the command
     *
     * @throws InrClientException
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var ErruRequestEntity $erruRequest
         * @var SendResponseCmd $command
         */
        $erruRequest = $this->getRepo()->fetchUsingId($command);

        /** @var File $xmlFile */
        $xmlFile = $this->getUploader()->download($erruRequest->getResponseDocument()->getIdentifier());

        //here is where we would expect the response from national register.
        try {
            $responseCode = $this->inrClient->makeRequestReturnStatusCode($xmlFile->getContent());
        } catch (AdapterRuntimeException $e) {
            $this->updateStatus($erruRequest, ErruRequestEntity::FAILED_CASE_TYPE);
            throw new InrClientException('Sending MSI response: There was an error sending the INR response ' . $e->getMessage());
        }

        $this->inrClient->close();

        if ($responseCode !== Response::STATUS_CODE_202) {
            $this->updateStatus($erruRequest, ErruRequestEntity::FAILED_CASE_TYPE);
            throw new InrClientException('Sending MSI response: INR Http response code was ' . $responseCode);
        }

        $this->updateStatus($erruRequest, ErruRequestEntity::SENT_CASE_TYPE);

        $result = new Result();
        $result->addMessage('Msi Response sent');
        $result->addId('Erru request', $erruRequest->getId());

        return $result;
    }

    /**
     * Sets the erru request status to the specified status key
     *
     * @param ErruRequestEntity $erruRequest erru request entity
     * @param string            $statusKey   erru request status key
     *
     * @return ErruRequestEntity
     */
    private function updateStatus(ErruRequestEntity $erruRequest, $statusKey)
    {
        $erruStatus = $this->getRepo()->getRefdataReference($statusKey);
        $erruRequest->setMsiType($erruStatus);
        $this->getRepo('ErruRequest')->save($erruRequest);

        return $erruRequest;
    }
}
