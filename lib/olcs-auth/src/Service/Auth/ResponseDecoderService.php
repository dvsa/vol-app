<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Laminas\Http\Response;

/**
 * Response Decoder Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResponseDecoderService
{
    /**
     * Decode a response content
     *
     * @param Response $response Response
     *
     * @return array
     * @throws Exception\RuntimeException
     */
    public function decode(Response $response)
    {
        $content = $response->getContent();

        $decoded = json_decode($content, true);

        if ($decoded === null) {
            throw new Exception\RuntimeException('Unable to JSON decode response body: ' . json_last_error_msg());
        }

        $decoded['status'] = $response->getStatusCode();

        return $decoded;
    }
}
