<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Exception\BadRequestException;
use Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse;
use Dvsa\Olcs\Transfer\Query\GdsVerify\GetAuthRequest;
use Laminas\Cache\Storage\Adapter\Redis;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Mvc\MvcEvent;
use Olcs\Logging\Log\Logger;
use Olcs\Session\DigitalSignature;
use Olcs\View\Model\Dashboard;
use ZfcRbac\Exception\UnauthorizedException;
use Exception;

/**
 * GdsVerifyController Controller
 */
class GdsVerifyController extends AbstractController
{
    const CACHE_PREFIX = "verify:";

    /**
     * @var StorageInterface
     */
    private $cache;

    public function onDispatch(MvcEvent $e)
    {
        $this->cache = $this->getServiceLocator()->get(Redis::class);
        return parent::onDispatch($e);
    }

    /**
     * Display Form to initaite the GDS Verify identification process
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function initiateRequestAction()
    {
        $types = $this->getTypeOfRequest($this->params()->fromRoute());
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('VerifyRequest');
        $session = $this->handleType($types);

        $response = $this->handleQuery(GetAuthRequest::create([]));
        if ($response->isOk()) {
            $result = $response->getResult();

            $verifyRequestId = $this->getRootAttributeFromSaml($result['samlRequest'], 'ID');
            $session->setVerifyId($verifyRequestId);
            $this->whitelistUserVerifyRequest($verifyRequestId);

            Logger::debug("Created Verify request with id:" . $verifyRequestId);

            if ($result['enabled'] !== true) {
                throw new \RuntimeException('Verify is currently disabled');
            }
            $form->setAttribute('action', $result['url']);
            $form->get('SAMLRequest')->setValue($result['samlRequest']);
        }

        $this->getServiceLocator()->get('Script')->loadFile('verify-request');

        return new \Laminas\View\Model\ViewModel(array('form' => $form));
    }

    /**
     * Process the request from GDS Verify and forwards to Process Signature Action.
     *
     * This is required due to SameSite Cookies and not compromising by converting our cookies to third-party.
     *
     * @return \Laminas\Http\Response
     * @throws \UnauthorizedException
     */
    public function processResponseAction()
    {
        $samlResponse = $this->getRequest()->getPost('SAMLResponse', null);
        if (is_null($samlResponse)) {
            throw new UnauthorizedException('Missing samlResponse');
        }

        $id = $this->getRootAttributeFromSaml($samlResponse, 'InResponseTo');
        $verifyJourneyKey = $this->generateVerifyJourneyKey($id);
        if (!empty($this->cache->removeItems([$verifyJourneyKey]))) {
            throw new UnauthorizedException('Invalid verify journey id');
        }

        $key = $this->generateSamlKey($samlResponse);
        $this->cache->setItem($key, $samlResponse);

        return $this->redirect()->toRoute(
            'verify/process-signature',
            [],
            [
                'query' => [
                    'ref' => explode(':', $key)[1]
                ]
            ]
        );
    }

    /**
     * Process the GDS Verify SAML response
     *
     * @return \Laminas\Http\Response
     */
    public function processSignatureAction()
    {
        $session = new \Olcs\Session\DigitalSignature();

        Logger::debug("DigitalSignature retrieved:", $session->getArrayCopy());

        $applicationId = $session->hasApplicationId() ? $session->getApplicationId() : false;
        $continuationDetailId = $session->hasContinuationDetailId() ? $session->getContinuationDetailId() : false;
        $transportManagerApplicationId = $session->hasTransportManagerApplicationId() ? $session->getTransportManagerApplicationId() : false;
        $licenceId = $session->hasLicenceId() ? $session->getLicenceId() : false;
        $lva = $session->hasLva() ? $session->getLva() : 'application';
        $role = $session->hasRole() ? $session->getRole() : null;
        $verifyRequestId = $session->hasVerifyId() ? $session->getVerifyId() : null;

        if (empty($verifyRequestId)) {
            throw new BadRequestException("There is no `verifyId` on DigitalSignature.");
        }

        $key = $this->getRequest()->getQuery('ref');
        if (!$this->validateRedisSamlResponseReferenceKey($key)) {
            throw new BadRequestException("Query parameter 'ref' ({$key}) is not a valid SHA1.");
        }

        $samlResponse = $this->cache->getItem(static::CACHE_PREFIX . $key);
        $inResponseTo = $this->getRootAttributeFromSaml($samlResponse, 'InResponseTo');

        if (empty($inResponseTo)) {
            throw new BadRequestException("There is no `inResponseTo` in the samlResponse.");
        }

        if ($verifyRequestId !== $inResponseTo) {
            throw new UnauthorizedException("SamlResponse({$inResponseTo}) does not match SamlRequest({$verifyRequestId})");
        }

        $this->cache->removeItems([
            $this->generateActiveUserkey($this->currentUser()->getIdentity()->getUsername()),
            $key
        ]);

        $dto = ProcessSignatureResponse::create(
            ['samlResponse' => $samlResponse]
        );

        if ($applicationId) {
            $dto->setApplication($applicationId);
        }
        if ($continuationDetailId) {
            $dto->setContinuationDetail($continuationDetailId);
        }

        if ($transportManagerApplicationId) {
            $dto->setTransportManagerApplication($transportManagerApplicationId);
            $dto->setRole($role);
        }

        if ($licenceId) {
            $dto->setLicence($licenceId);
        }

        $session->getManager()->getStorage()->clear(\Olcs\Session\DigitalSignature::SESSION_NAME);
        $response = $this->handleCommand($dto);
        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('undertakings_not_signed');
        }


        if ($applicationId && !$transportManagerApplicationId) {
            return $this->redirect()->toRoute(
                'lva-application/undertakings',
                ['application' => $applicationId]
            );
        }

        if ($continuationDetailId) {
            return $this->redirect()->toRoute(
                'continuation/declaration',
                ['continuationDetailId' => $continuationDetailId]
            );
        }

        /** @var  $transportManagerApplicationId */
        if ($transportManagerApplicationId) {
            return $this->redirect()->toRoute(
                'lva-' . $lva . '/transport_manager_confirmation',
                [
                    'child_id' => $transportManagerApplicationId,
                    'application' => $applicationId,
                    'action' => 'index'
                ]
            );
        }

        if ($licenceId) {
            return $this->redirect()->toRoute(
                'licence/surrender/confirmation',
                [
                    'licence' => $licenceId,
                    'action' => 'index'
                ]
            );
        }

        throw new \RuntimeException('There was an error processing the signature response');
    }

    /**
     * verificationType
     *
     * @param array $types
     */
    private function handleType(array $types): DigitalSignature
    {
        $session = new \Olcs\Session\DigitalSignature();

        if (empty($types)) {
            throw new \RuntimeException(
                'An entity identifier needs to be present, this is used to to calculate where'
                . ' to return to after completing Verify'
            );
        }

        foreach ($types as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($session, $methodName)) {
                call_user_func([$session, $methodName], $value);
            }
        }
        Logger::debug("DigitalSignature created:", $session->getArrayCopy());

        return $session;
    }

    private function getTypeOfRequest($params): array
    {
        // remove controller and action keys from params
        $types = array_diff_assoc($params, ['controller' => self::class, 'action' => 'initiate-request']);
        return $types;
    }

    /**
     * Generate cache key for the samlResponse to be stored under
     *
     * @param string $samlResponse
     * @return string
     */
    private function generateSamlKey(string $samlResponse): string
    {
        $key = sha1($samlResponse);
        return static::CACHE_PREFIX . $key;
    }

    /**
     * Generate cache key to whitelist this verify journey
     *
     * @param string $id
     * @return string
     */
    private function generateVerifyJourneyKey(string $id): string
    {
        return static::CACHE_PREFIX . "activeJourneys:" . $id;
    }

    /**
     * Generate cache key to whitelist user for verify
     *
     * @param string $username
     * @return string
     */
    private function generateActiveUserkey(string $username): string
    {
        return static::CACHE_PREFIX . "activeUsers:" . $username;
    }

    /**
     * Extract an attribute from a SAML XML String Document
     *
     * @param $samlString
     * @param $attributeName
     * @return string
     */
    protected function getRootAttributeFromSaml(string $samlString, string $attributeName)
    {
        $samlString = base64_decode($samlString);
        $samlString = simplexml_load_string($samlString);

        if ($samlString === false) {
            throw new Exception("Unable to parse SAML XML String");
        }

        $attributes = (array) $samlString->attributes();

        if (! array_key_exists($attributeName, $attributes['@attributes'])) {
            throw new Exception("SAML XML String Document does not contain attribute '{$attributeName}' in the root.");
        }

        return (string)$attributes['@attributes'][$attributeName];
    }

    /**
     * Whitelist this users journey for verify
     *
     * @param string $verifyId
     */
    protected function whitelistUserVerifyRequest(string $verifyId): void
    {
        $activeUserKey = $this->generateActiveUserkey($this->currentUser()->getIdentity()->getUsername());
        $previousVerifyId = $this->cache->getItem($activeUserKey);
        if (!is_null($previousVerifyId)) {
            $this->cache->removeItems([
                    $this->generateVerifyJourneyKey($previousVerifyId),
                    $activeUserKey
                ]
            );
        }

        $this->cache->addItems([
            $activeUserKey => $verifyId,
            $this->generateVerifyJourneyKey($verifyId) => true
        ]);
    }

    private function validateRedisSamlResponseReferenceKey($key): bool
    {
        // Essentially, we verify the reference key is a SHA1.
        return (bool) preg_match('/^[0-9a-f]{40}$/i', $key);
    }
}
