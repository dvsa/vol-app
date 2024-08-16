<?php

namespace Dvsa\Olcs\GdsVerify\Service;

use Dvsa\Olcs\Utils\Client\HttpExternalClientFactory;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Log\LoggerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RobRichards\XMLSecLibs;
use Dvsa\Olcs\GdsVerify\Data;
use Psr\Container\ContainerInterface;

/**
 * Class GdsVerify
 *
 * NOTE php mcrypt extension needs installing, for this to work
 *
 * @package Dvsa\Olcs\GdsVerify\Service
 */
class GdsVerify implements FactoryInterface
{
    public const CONFIG_KEY = 'gds_verify';
    public const CONFIG_ENTITY_ID = 'entity_identifier';
    public const CONFIG_SIGNATURE_KEY = 'signature_key';
    public const CONFIG_ENCRYPTION_KEYS = 'encryption_keys';
    public const CONFIG_MSA_METADATA_URL = 'msa_metadata_url';
    public const CONFIG_ENABLED_DEBUG_LOG = 'enable_debug_log';
    public const CONFIG_CACHE = 'cache';

    /**
     * @var array Config
     */
    private $config = [];

    /**
     * @var XMLSecLibs\XMLSecurityKey
     */
    private $signatureKey;

    /**
     * @var array of XMLSecLibs\XMLSecurityKey
     */
    private $encryptionKeys = [];

    /**
     * @var Data\Metadata\MatchingServiceAdapter
     */
    private $matchingServiceAdapterMetadata;

    /**
     * @var Data\Loader;
     */
    private $metadataLoader;

    /**
     * Setup the SAML container, required to use the simpleSAML library
     *
     * @param LoggerInterface $logger Logger
     *
     * @return Data\Container
     */
    private function getContainer(LoggerInterface $logger)
    {
        $Psrlogger = new \Olcs\Logging\Log\LaminasLogPsr3Adapter($logger);
        $container = new Data\Container($Psrlogger);
        if (!empty($this->config[self::CONFIG_ENABLED_DEBUG_LOG])) {
            $container->setDebugLog($Psrlogger);
        }

        return $container;
    }

    /**
     * Get authentication request data for use in a form
     *
     * @return array Auth request data ['url' => 'http://abc.com', 'samlRequest' => 'akshjdg762354....']]
     */
    public function getAuthenticationRequest()
    {
        // Set up an AuthnRequest
        $request = new \SAML2\AuthnRequest();

        $request->setIssuer($this->getEntityIdentifier());
        $request->setDestination($this->getMatchingServiceAdapterMetadata()->getSsoUrl());
        // Set our signing key so that request can be signed
        $request->setSignatureKey($this->getSignatureKey());

        // Set the request into a POST form
        $binding = new \Dvsa\Olcs\GdsVerify\SAML2\Binding();

        return $binding->send($request);
    }

    /**
     * Process a SAML response and return account attributes
     *
     * @param string $response Encoded SAML response
     *
     * @return Data\Attributes
     * @throws \Dvsa\Olcs\GdsVerify\Exception
     */
    public function getAttributesFromResponse($response)
    {
        $binding = new \Dvsa\Olcs\GdsVerify\SAML2\Binding();
        $samlResponse = $binding->processResponse($response);

        if (!$samlResponse->isSuccess()) {
            // If not a success message return empty attributes
            return new Data\Attributes([]);
        }

        // Get MSA signing certificate
        $msaCert = $this->getMatchingServiceAdapterSigningCertificate();

        $attributes = [];
        foreach ($samlResponse->getAssertions() as $assertion) {
            /* @var $assertion \SAML2\EncryptedAssertion */

            // decrypt the assertion
            $decryptedAssertion = $this->decrytAssertion($assertion);

            // Validate that the assertion is signed by the Matching Service Adapter
            try {
                $decryptedAssertion->validate($msaCert);
            } catch (\Exception $e) {
                throw new \Dvsa\Olcs\GdsVerify\Exception('SAML Assertion signature error', 0, $e);
            }

            $attributes = $decryptedAssertion->getAttributes();
            $attributesTransformed = [];
            // flatten array
            foreach ($attributes as $key => $value) {
                // Compliance tool uses different attribute names to Integration environment
                $key = str_replace('_', '', strtolower($key));
                $value = $value[0];
                $attributesTransformed[$key] = $value;
            }
        }

        return new Data\Attributes($attributesTransformed);
    }

    /**
     * Decrypt an assertion
     *
     * @param \SAML2\EncryptedAssertion $assertion The assertion to decrypt
     *
     * @return \SAML2\Assertion
     * @throws \Dvsa\Olcs\GdsVerify\Exception
     */
    private function decrytAssertion(\SAML2\EncryptedAssertion $assertion)
    {
        $keyNumber = 0;
        while ($encryptionKey = $this->getEncryptionKey($keyNumber)) {
            try {
                return $assertion->getAssertion($encryptionKey);
            } catch (\Exception) {
                // Swallow the exception, and try the next key
            }
            $keyNumber++;
        }

        throw new \Dvsa\Olcs\GdsVerify\Exception('Cannot decrypt the SAML Assertion');
    }

    /**
     * Get the Matching Service Adapters signing certificate
     *
     * @return XMLSecLibs\XMLSecurityKey
     */
    private function getMatchingServiceAdapterSigningCertificate()
    {
        $certificate = new XMLSecLibs\XMLSecurityKey(XMLSecLibs\XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $certificate->loadKey(
            $this->getMatchingServiceAdapterMetadata()->getSigningCertificate()
        );

        return $certificate;
    }

    /**
     * Get the Signature key to sign auth requests with
     *
     * @return XMLSecLibs\XMLSecurityKey
     * @throws \Dvsa\Olcs\GdsVerify\Exception
     */
    public function getSignatureKey()
    {
        if ($this->signatureKey instanceof XMLSecLibs\XMLSecurityKey) {
            return $this->signatureKey;
        }

        if (!empty($this->config[self::CONFIG_SIGNATURE_KEY])) {
            return $this->loadSignatureKey($this->config[self::CONFIG_SIGNATURE_KEY]);
        }

        throw new \Dvsa\Olcs\GdsVerify\Exception('Signature key is not set');
    }

    /**
     * Set the signature key
     *
     * @param XMLSecLibs\XMLSecurityKey $key Signature key
     *
     * @return void
     */
    public function setSignatureKey(XMLSecLibs\XMLSecurityKey $key)
    {
        $this->signatureKey = $key;
    }

    /**
     * Load the encryption key from a file
     *
     * @param string $keyFilename Path and filename of the key
     *
     * @return XMLSecLibs\XMLSecurityKey
     * @throws \Dvsa\Olcs\GdsVerify\Exception
     */
    public function loadSignatureKey($keyFilename)
    {
        if (!file_exists($keyFilename)) {
            throw new \Dvsa\Olcs\GdsVerify\Exception('Signature key file not found');
        }

        $key = new XMLSecLibs\XMLSecurityKey(XMLSecLibs\XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $key->loadKey($keyFilename, true);
        $this->setSignatureKey($key);

        return $key;
    }

    /**
     * Get the Encryption key to decrypt response from the hub
     *
     * @param int $indexNumber Index number of key to get
     *
     * @return XMLSecLibs\XMLSecurityKey|false
     */
    public function getEncryptionKey($indexNumber = 0)
    {
        if (
            isset($this->encryptionKeys[$indexNumber])
            && $this->encryptionKeys[$indexNumber] instanceof XMLSecLibs\XMLSecurityKey
        ) {
            return $this->encryptionKeys[$indexNumber];
        }

        if (!empty($this->config[self::CONFIG_ENCRYPTION_KEYS][$indexNumber])) {
            return $this->loadEncryptionKey($this->config[self::CONFIG_ENCRYPTION_KEYS][$indexNumber], $indexNumber);
        }

        return false;
    }

    /**
     * Set the encryption key
     *
     * @param XMLSecLibs\XMLSecurityKey $key         Encryption key
     * @param int                       $indexNumber Index number of key to set
     *
     * @return void
     */
    public function setEncryptionKey(XMLSecLibs\XMLSecurityKey $key, $indexNumber = 0)
    {
        $this->encryptionKeys[$indexNumber] = $key;
    }

    /**
     * Load the encryption key from a file
     *
     * @param string $keyFilename Path and file name of key
     * @param int    $indexNumber Index number of key to load
     *
     * @return XMLSecLibs\XMLSecurityKey
     * @throws \Dvsa\Olcs\GdsVerify\Exception
     */
    public function loadEncryptionKey($keyFilename, $indexNumber = 0)
    {
        if (!file_exists($keyFilename)) {
            throw new \Dvsa\Olcs\GdsVerify\Exception('Encryption key file not found');
        }

        $key = new XMLSecLibs\XMLSecurityKey(XMLSecLibs\XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'private']);
        $key->loadKey($keyFilename, true);
        $this->setEncryptionKey($key, $indexNumber);

        return $key;
    }

    /**
     * Get the Matching Service Adapter metadata document
     *
     * @return Data\Metadata\MatchingServiceAdapter
     * @throws \Dvsa\Olcs\GdsVerify\Exception
     */
    public function getMatchingServiceAdapterMetadata()
    {
        if ($this->matchingServiceAdapterMetadata instanceof Data\Metadata\MatchingServiceAdapter) {
            return $this->matchingServiceAdapterMetadata;
        }

        if (!empty($this->config[self::CONFIG_MSA_METADATA_URL])) {
            $this->matchingServiceAdapterMetadata = $this->getMetadataLoader()->loadMatchingServiceAdapterMetadata(
                $this->config[self::CONFIG_MSA_METADATA_URL]
            );
            return $this->matchingServiceAdapterMetadata;
        }

        throw new \Dvsa\Olcs\GdsVerify\Exception('MatchingServiceAdapter metadata not set');
    }

    /**
     * Get the Entity Identifier used in making Auth Request
     *
     * @return string
     * @throws \Exception
     */
    public function getEntityIdentifier()
    {
        if (!empty($this->config[self::CONFIG_ENTITY_ID])) {
            return $this->config[self::CONFIG_ENTITY_ID];
        }

        throw new \Dvsa\Olcs\GdsVerify\Exception('Entity identifier is not specified');
    }

    /**
     * Get Metadata loader
     *
     * @return Data\Loader
     */
    public function getMetadataLoader()
    {
        return $this->metadataLoader;
    }

    /**
     * Set Metadata loader
     *
     * @param Data\Loader $metadataLoader Metadata loader object
     *
     * @return void
     */
    public function setMetadataLoader(Data\Loader $metadataLoader)
    {
        $this->metadataLoader = $metadataLoader;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = [];
        $globalConfig = $container->get('config');
        if (isset($globalConfig[self::CONFIG_KEY])) {
            $config = $globalConfig[self::CONFIG_KEY];
        }
        $this->config = $config;
        \SAML2\Compat\ContainerSingleton::setContainer(
            $this->getContainer($container->get('Logger'))
        );

        if (!empty($this->config[self::CONFIG_CACHE]) && is_array($this->config[self::CONFIG_CACHE])) {
            $cache = $container->get('default-cache');
            $this->setMetadataLoader(new Data\Loader($cache));
        }

        if ($container->has(HttpExternalClientFactory::class)) {
            $this->getMetadataLoader()->setHttpClient(
                $container->get(HttpExternalClientFactory::class)
            );
        }
        return $this;
    }
}
