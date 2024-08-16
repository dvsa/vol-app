<?php

/**
 * RestResponseTrait
 *
 * @author Someone <someone@valtech.co.uk>
 */

namespace Dvsa\Olcs\Db\Traits;

use Laminas\Http\Response;

/**
 * RestResponseTrait
 *
 * @author Someone <someone@valtech.co.uk>
 */
trait RestResponseTrait
{
    /**
     * Create a new instance of Response
     *
     * @return Response
     */
    public function getNewResponse()
    {
        return new Response();
    }

    /**
     * Creates a response object and set's up the response body
     *
     * @param int $errorCode
     * @param string $summary
     * @param array $data
     * @return Response
     */
    public function respond($errorCode, $summary = null, $data = [])
    {
        $response = $this->getNewResponse();

        $response->setStatusCode($errorCode);

        $response->setContent(
            json_encode(
                [
                    'Response' => [
                        'Code' => $errorCode,
                        'Message' => $response->getReasonPhrase(),
                        'Summary' => $summary,
                        'Data' => $data
                    ]
                ]
            )
        );

        return $response;
    }
}
