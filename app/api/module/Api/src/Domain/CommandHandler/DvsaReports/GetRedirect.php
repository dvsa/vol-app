<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DvsaReports;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Http\Client;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\Json\Json;
use Olcs\Logging\Log\Logger;

/**
 * GetRedirect
 */
class GetRedirect extends AbstractCommandHandler implements AuthAwareInterface, ConfigAwareInterface
{
    use AuthAwareTrait;
    use ConfigAwareTrait;

    protected $httpClient;

    /**
     * Constructor.
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $currentUser = $this->getCurrentUser();
        $postDataJson = Json::encode([
            'operators' => $command->getOlNumbers(),
            'operator_name' => $currentUser->getRelatedOrganisationName(),
            'refresh_token' => $command->getRefreshToken()
        ]);

        $config =  $this->getConfig();
        $topReportConfig = $config['top-report-link'];

        $adapter = new Curl();
        if (!empty($topReportConfig['proxy'])) {
            $adapter->setCurlOption(CURLOPT_PROXY, $topReportConfig['proxy']);
        }

        $this->httpClient->setAdapter($adapter);
        $this->httpClient->setUri($topReportConfig['targetUrl']);
        $this->httpClient->setMethod('POST');
        $this->httpClient->setRawBody($postDataJson);
        $this->httpClient->setHeaders([
            'Content-Type' => 'application/json',
            'x-api-key' => $topReportConfig['apiKey'],
            'Authorization' => 'Bearer ' . $command->getJwt()
        ]);

        $edhApiResult = $this->httpClient->send();
        $resultBody = json_decode((string) $edhApiResult->getContent(), true);

        if (!isset($resultBody['redirectUrl'])) {
            Logger::err('TOPS Report API Error Message: ' . $resultBody['message']);
            throw new RuntimeException('An error occurred while trying to redirect to the Reports page');
        }

        return $this->result->addMessage($resultBody['redirectUrl']);
    }
}
