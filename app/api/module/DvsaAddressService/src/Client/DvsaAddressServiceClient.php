<?php

namespace Dvsa\Olcs\DvsaAddressService\Client;

use Dvsa\Olcs\DvsaAddressService\Client\Mapper\AddressMapper;
use Dvsa\Olcs\DvsaAddressService\Exception\ServiceException;
use Dvsa\Olcs\DvsaAddressService\Exception\ValidationException;
use Dvsa\Olcs\DvsaAddressService\Model\Address;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use InvalidArgumentException;

class DvsaAddressServiceClient
{
    protected const URI_SEARCH = '/search';

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @return Address[]
     * @throws ValidationException
     * @throws ServiceException|GuzzleException
     */
    public function lookupAddress(string $query): array
    {
        try {
            $response = $this->client->get(static::URI_SEARCH, [
                'query' => ['query' => $query]
            ]);

            $json = json_decode($response->getBody(), true);

            return AddressMapper::mapAddressDataArrayToObjects($json);
        } catch (ClientException | ServerException $exception) {
            match ($exception->getResponse()->getStatusCode()) {
                400, 422 => throw new ValidationException('User input bad or invalid postcode: ' . $exception->getMessage()),
                default => throw new ServiceException(
                    'There was a uncaught client/server exception when communicating with the DVSA Address Service API - ' . $exception->getResponse()->getStatusCode() . ' - ' . $exception->getResponse()->getReasonPhrase(),
                    0,
                    $exception
                )
            };
        } catch (ConnectException | RequestException $exception) {
            throw new ServiceException(
                'There was an error when communicating with the DVSA Address Service API',
                0,
                $exception
            );
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new ServiceException(
                'There was an error when JSON decoding the response from DVSA Address Service API',
                0,
                $invalidArgumentException
            );
        }
    }

    private function validateQueryStringIsPostcodeOrUprn(string $query): void
    {
        // Basic empty check before running RegEx
        if (empty($query)) {
            throw new ValidationException('Query cannot be empty');
        }

        // Validate query is a string a-Z, 0-9, or space between 1 and 12 characters
        if (!preg_match('/^[a-zA-Z0-9 ]{1,12}$/', $query)) {
            throw new ValidationException('Query must be a valid UK Postcode or UPRN');
        }
    }
}
