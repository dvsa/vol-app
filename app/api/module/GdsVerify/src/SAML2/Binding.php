<?php

namespace Dvsa\Olcs\GdsVerify\SAML2;

/**
 * Use our own binding so that we can send info from API to front end
 */
class Binding extends \SAML2\Binding
{
    /**
     * Create the SAML auth request data
     *
     * @param \SAML2\Message $message SAML Auth request message
     *
     * @return array ['url' => SSO URL, 'SAMLRequest' => Encoded SAML auth request]
     */
    public function send(\SAML2\Message $message)
    {
        $msgStr = $message->toSignedXML();
        $msgStr = $msgStr->ownerDocument->saveXML($msgStr);
        \SAML2\Utils::getContainer()->debugMessage($msgStr, 'SAML Request');
        $msgStr = base64_encode($msgStr);

        $post = [
            'url' => $message->getDestination(),
            'samlRequest' => $msgStr,
        ];

        return $post;
    }

    /**
     * No op
     *
     * @return void
     */
    public function receive()
    {
    }

    /**
     * Process a SAML Response string
     *
     * @param string $samlResponse SAML response message
     *
     * @return \SAML2\Response
     */
    public function processResponse($samlResponse)
    {
        $samlResponse = base64_decode($samlResponse);
        \SAML2\Utils::getContainer()->debugMessage($samlResponse, 'SAML Response');

        $document = \SAML2\DOMDocumentFactory::fromString($samlResponse);
        $xml = $document->firstChild;

        $samlResponse = \SAML2\Message::fromXML($xml);

        return $samlResponse;
    }
}
