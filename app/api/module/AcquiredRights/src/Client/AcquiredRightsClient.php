<?php

namespace Dvsa\Olcs\AcquiredRights\Client;

use Dvsa\Olcs\AcquiredRights\Exception\MapperParseException;
use Dvsa\Olcs\AcquiredRights\Exception\ReferenceNotFoundException;
use Dvsa\Olcs\AcquiredRights\Exception\ServiceException;
use Dvsa\Olcs\AcquiredRights\Client\Mapper\ApplicationReferenceMapper;
use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Uuid;

class AcquiredRightsClient
{
    /**
     * Important:
     * If the base_uri contains a resource (domain.com/ci/ for example) must not prefix this variable with a /.
     */
    protected const URI_BY_REFERENCE_PATTERN = 'ref-vol-lookup/%s';

    public function __construct(protected Client $httpClient)
    {
    }

    /**
     * @return ApplicationReference
     * @throws ReferenceNotFoundException
     * @throws ServiceException
     * @throws MapperParseException
     */
    public function fetchByReference(string $reference): ApplicationReference
    {
        try {
            $response = $this->httpClient->get($this->generateUri($reference));
            $data = json_decode($response->getBody(), true);
            return ApplicationReferenceMapper::createFromResponseArray($data);
        } catch (ClientException $clientException) {
            if ($clientException->getCode() === 404) {
                throw new ReferenceNotFoundException();
            }
            throw new ServiceException(
                'There was a client exception when communicating with the Acquired Rights API',
                0,
                $clientException
            );
        } catch (ConnectException | RequestException $exception) {
            throw new ServiceException(
                'There was an error when communicating with the Acquired Rights API',
                0,
                $exception
            );
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new ServiceException(
                'There was an error when JSON decoding the response from Acquired Rights API',
                0,
                $invalidArgumentException
            );
        } catch (MapperParseException $mapperParseException) {
            throw new MapperParseException(
                'There was an error mapping the response to ApplicationReferenceModel',
                0,
                $mapperParseException
            );
        }
    }

    protected function generateUri(string $reference): UriInterface
    {
        return new Uri(sprintf(static::URI_BY_REFERENCE_PATTERN, $reference));
    }
}
