<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Address\Service\AddressServiceAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForLicence;
use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForOrganisation;
use Dvsa\Olcs\Api\Domain\Command\Cache\Generate;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\DocumentGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\FileProcessorAwareInterface;
use Dvsa\Olcs\Api\Domain\HandlerEnabledTrait;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Domain\SlaCalculatorAwareInterface;
use Dvsa\Olcs\Api\Domain\SubmissionGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareInterface;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Domain\TransExchangeAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslationLoaderAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Api\Domain\UploaderAwareInterface;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Dvsa\Olcs\Api\Service\Document\NamingServiceAwareInterface;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessorInterface;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Dvsa\Olcs\Api\Service\Publication\PublicationGenerator;
use Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Dvsa\Olcs\Queue\Service\Queue;
use Dvsa\Olcs\Queue\Service\QueueInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption as CacheEncryptionService;
use Laminas\ServiceManager\Exception\ExceptionInterface as LaminasServiceException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

/**
 * Abstract Command Handler
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractCommandHandler implements CommandHandlerInterface, FactoryInterface
{
    use HandlerEnabledTrait;

    /**
     * The name of the default repo
     */
    protected $repoServiceName;

    /**
     * Tell the factory which repositories to lazy load
     */
    protected $extraRepos = [];

    /**
     * Store the instantiated repos
     *
     * @var RepositoryInterface[]
     */
    private $repos = [];

    private CommandHandlerManager $commandHandler;

    private QueryHandlerManager $queryHandler;

    private RepositoryServiceManager $repoManager;

    /**
     * @var NamingService
     */
    private $documentNamingService;

    /**
     * @var IdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var array
     */
    protected $toggleConfig = [];

    /**
     * Laminas ServiceManager masks exceptions behind a simple 'service not created'
     * message so here we inspect the 'previous exception' chain and log out
     * what the actual errors were, before rethrowing the original execption.
     *
     * @param \Exception $e exception
     *
     * @return void
     * @throws \Exception rethrows original Exception
     */
    private function logServiceExceptions(\Exception $e)
    {
        $rethrow = $e;

        do {
            Logger::warn(static::class . ': ' . $e->getMessage());
            $e = $e->getPrevious();
        } while ($e);

        throw $rethrow;
    }

    /**
     * Warnings suppressed as by design this is just a series of 'if' conditions
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function applyInterfaces(ContainerInterface $mainServiceLocator): void
    {
        if ($this instanceof ToggleRequiredInterface || $this instanceof ToggleAwareInterface) {
            $toggleService = $mainServiceLocator->get(ToggleService::class);
            $this->setToggleService($toggleService);
        }

        if ($this instanceof AuthAwareInterface) {
            $this->setAuthService($mainServiceLocator->get(AuthorizationService::class));
            $this->setUserRepository($mainServiceLocator->get('RepositoryServiceManager')->get('User'));
        }

        if ($this instanceof DocumentGeneratorAwareInterface) {
            $this->setDocumentGenerator($mainServiceLocator->get('DocumentGenerator'));
        }

        if ($this instanceof PublicationGeneratorAwareInterface) {
            $this->setPublicationGenerator($mainServiceLocator->get(PublicationGenerator::class));
        }

        if ($this instanceof SubmissionGeneratorAwareInterface) {
            $this->setSubmissionGenerator($mainServiceLocator->get(SubmissionGenerator::class));
            $this->setSubmissionConfig($mainServiceLocator->get('config')['submissions']['sections']['configuration']);
        }

        if ($this instanceof \Dvsa\Olcs\Api\Domain\EmailAwareInterface) {
            $this->setTemplateRendererService(
                $mainServiceLocator->get(\Dvsa\Olcs\Email\Service\TemplateRenderer::class)
            );
        }

        if ($this instanceof \Dvsa\Olcs\Api\Domain\CpmsAwareInterface) {
            $this->setCpmsService($mainServiceLocator->get('CpmsHelperService'));
        }

        if ($this instanceof \Dvsa\Olcs\Api\Domain\TranslatorAwareInterface) {
            $translator = $mainServiceLocator->get('translator');
            $this->setTranslator($translator);
        }

        if ($this instanceof UploaderAwareInterface) {
            $this->setUploader($mainServiceLocator->get('FileUploader'));
        }

        if ($this instanceof NamingServiceAwareInterface) {
            $this->setNamingService($mainServiceLocator->get('DocumentNamingService'));
        }

        if ($this instanceof TransExchangeAwareInterface) {
            $this->setTransExchange($mainServiceLocator->get(TransExchangeClient::class));
        }

        if ($this instanceof ConfigAwareInterface) {
            $config = $mainServiceLocator->get('config') ?? [];
            $this->setConfig($config);
        }

        if ($this instanceof FileProcessorAwareInterface) {
            $this->setFileProcessor($mainServiceLocator->get(FileProcessorInterface::class));
        }

        if ($this instanceof QueueInterface) {
            $this->setQueueService($mainServiceLocator->get(Queue::class));
            $this->setMessageBuilderService($mainServiceLocator->get(MessageBuilder::class));
            $messageQueueConfig = $mainServiceLocator->get('config')['message_queue'] ?? [];
            $this->setQueueConfig($messageQueueConfig);
        }

        if ($this instanceof CacheAwareInterface) {
            /** @var CacheEncryptionService $cacheEncryptionService */
            $cacheEncryptionService = $mainServiceLocator->get(CacheEncryptionService::class);
            $this->setCache($cacheEncryptionService);
        }

        if ($this instanceof TranslationLoaderAwareInterface) {
            $translationLoader = $mainServiceLocator->get('TranslatorPluginManager')->get(TranslationLoader::class);
            $this->setTranslationLoader($translationLoader);
        }

        if ($this instanceof TranslatorAwareInterface) {
            $translator = $mainServiceLocator->get('translator');
            $this->setTranslator($translator);
        }

        if ($this instanceof SlaCalculatorAwareInterface) {
            $slaCalculator = $mainServiceLocator->get(SlaCalculatorInterface::class);
            $this->setSlaCalculator($slaCalculator);
        }
    }

    /**
     * get repository
     *
     * @param string|null $name name of repository
     *
     * @return RepositoryInterface
     * @throws RuntimeException
     */
    protected function getRepo($name = null)
    {
        if ($name === null) {
            $name = $this->repoServiceName;
        }

        if (!in_array($name, $this->extraRepos)) {
            throw new RuntimeException('You have not injected the ' . $name . ' repository');
        }

        // Lazy load repository
        if (!isset($this->repos[$name])) {
            $this->repos[$name] = $this->repoManager->get($name);
        }

        return $this->repos[$name];
    }

    /**
     * get command handler
     *
     * @return \Dvsa\Olcs\Api\Domain\CommandHandlerManager
     */
    protected function getCommandHandler()
    {
        return $this->commandHandler;
    }

    /**
     * get query handler
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandlerManager
     */
    protected function getQueryHandler()
    {
        return $this->queryHandler;
    }

    /**
     * Get IdentityProvider
     *
     * @return IdentityProviderInterface
     */
    protected function getIdentityProvider()
    {
        return $this->identityProvider;
    }

    /**
     * Wrapper to call a query
     *
     * @param QueryInterface $query query
     *
     * @return Result
     */
    protected function handleQuery(QueryInterface $query)
    {
        try {
            $result = $this->getQueryHandler()->handleQuery($query, false);
        } catch (DisabledHandlerException $e) {
            $result = new Result();
            $result->addMessage($e->getMessage());
        }

        return $result;
    }

    /**
     * Runs the command to generate a specified cache
     *
     * @param string      $cacheId
     * @param string|null $uniqueId
     *
     * @return Result
     * @throws DisabledHandlerException
     */
    protected function generateCache($cacheId, $uniqueId = null): Result
    {
        $params = [
            'id' => $cacheId,
            'uniqueId' => $uniqueId,
        ];

        $cmd = Generate::create($params);

        return $this->handleSideEffect($cmd);
    }

    /**
     * Clear the caches for this licence id
     *
     * @param int $licenceId
     *
     * @return Result
     */
    protected function clearLicenceCacheSideEffect($licenceId)
    {
        return $this->handleSideEffect(
            ClearForLicence::create(
                ['id' => $licenceId]
            )
        );
    }

    /**
     * Clear the caches for this organisation id
     *
     * @param int $orgId
     *
     * @return Result
     */
    protected function clearOrganisationCacheSideEffect($orgId)
    {
        return $this->handleSideEffect(
            ClearForOrganisation::create(
                ['id' => $orgId]
            )
        );
    }

    /**
     * Wrapper to call another command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function handleSideEffect(CommandInterface $command)
    {
        try {
            $result = $this->getCommandHandler()->handleCommand($command, false);
        } catch (DisabledHandlerException $e) {
            $result = new Result();
            $result->addMessage($e->getMessage());
        }

        return $result;
    }

    /**
     * Wrapper to call an array of other commands
     *
     * @param array $commands array of commands
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function handleSideEffects(array $commands)
    {
        $result = new Result();

        foreach ($commands as $command) {
            $result->merge($this->handleSideEffect($command));
        }

        return $result;
    }

    /**
     * Wrapper to call another command as a system user
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function handleSideEffectAsSystemUser(CommandInterface $command)
    {
        $identityProvider = $this->getIdentityProvider();
        $identityProvider->setMasqueradedAsSystemUser(true);
        $result = $this->getCommandHandler()->handleCommand($command, false);
        $identityProvider->setMasqueradedAsSystemUser(false);

        return $result;
    }

    /**
     * Wrapper to call an array of other commands as a system user
     *
     * @param array $commands commands
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function handleSideEffectsAsSystemUser(array $commands)
    {
        $identityProvider = $this->getIdentityProvider();
        $identityProvider->setMasqueradedAsSystemUser(true);
        $result = $this->handleSideEffects($commands);
        $identityProvider->setMasqueradedAsSystemUser(false);

        return $result;
    }

    /**
     * Proxy to another command, using all data from the original command
     *
     * @param CommandInterface $originalCommand       original command
     * @param string           $proxyCommandClassName proxy class name
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function proxyCommand($originalCommand, $proxyCommandClassName)
    {
        $dtoData = $originalCommand->getArrayCopy();

        return $this->handleSideEffect($proxyCommandClassName::create($dtoData));
    }

    /**
     * Proxy to another command as a system user, using all data from the original command
     *
     * @param CommandInterface $originalCommand       original command
     * @param string           $proxyCommandClassName proxy command class name
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function proxyCommandAsSystemUser($originalCommand, $proxyCommandClassName)
    {
        $identityProvider = $this->getIdentityProvider();
        $identityProvider->setMasqueradedAsSystemUser(true);
        $result = $this->proxyCommand($originalCommand, $proxyCommandClassName);
        $identityProvider->setMasqueradedAsSystemUser(true);

        return $result;
    }

    /**
     * Returns collection of entityClass objects.
     *
     * @param string $entityClass  class name
     * @param array  $referenceIds reference ids
     *
     * @return ArrayCollection
     */
    protected function buildArrayCollection($entityClass = '', $referenceIds = [])
    {
        $collection = new ArrayCollection();

        if (!empty($referenceIds)) {
            foreach ($referenceIds as $referenceId) {
                $collection->add($this->getRepo()->getReference($entityClass, $referenceId));
            }
        }

        return $collection;
    }

    /**
     * Generates a variable from a given command, or if not present in the command, returns a default value.
     *
     * @param CommandInterface $command      command
     * @param string           $variableName variable name
     * @param mixed            $defaultValue default value
     *
     * @return mixed
     */
    protected function extractCommandVariable($command, $variableName, mixed $defaultValue = null)
    {
        $commandVariable = $defaultValue;

        $methodName = 'get' . ucfirst($variableName);
        if (method_exists($command, $methodName)) {
            $commandVariable = $command->$methodName();
        }

        return $commandVariable;
    }

    /**
     * Returns a ref data reference for the provided key, or null if the key is null or doesn't exist
     *
     * @param string|null $refDataKey ref data key
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData|null
     */
    protected function refDataOrNull($refDataKey): ?RefDataEntity
    {
        if ($refDataKey === null) {
            return null;
        }

        return $this->refData($refDataKey);
    }

    /**
     * For required fields we can skip the null refdata check
     *
     *
     * @return RefDataEntity|null
     */
    protected function refData(string $refDataKey): ?RefDataEntity
    {
        return $this->getRepo()->getRefdataReference($refDataKey);
    }

    /**
     * @param string|null $value ('Y' or 'N')
     *
     * @return bool|null
     */
    protected function yesNoToBoolOrNull(?string $value): ?bool
    {
        if ($value === null || ($value !== 'Y' && $value !== 'N')) {
            return null;
        }
        return $value === 'Y' ? true : false;
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->result = new Result();

        try {
            $this->applyInterfaces($container);
        } catch (LaminasServiceException $e) {
            $this->logServiceExceptions($e);
        }
        $this->repoManager = $container->get('RepositoryServiceManager');
        if ($this->repoServiceName !== null) {
            $this->extraRepos[] = $this->repoServiceName;
        }
        $this->commandHandler = $container->get('CommandHandlerManager');
        $this->queryHandler = $container->get('QueryHandlerManager');
        $this->identityProvider = $container->get(IdentityProviderInterface::class);
        if ($this instanceof TransactionedInterface) {
            return new TransactioningCommandHandler($this, $container->get('TransactionManager'));
        }
        return $this;
    }

    public function setNamingService(NamingService $service)
    {
        $this->documentNamingService = $service;
    }

    /**
     * @return NamingService
     */
    public function getNamingService()
    {
        return $this->documentNamingService;
    }

    /**
     * @param $command
     */
    public function determineEntityFromCommand(array $data)
    {
        if (!empty($data['case'])) {
            return $this->getRepo()->getReference(Cases::class, $data['case']);
        }

        if (!empty($data['application'])) {
            return $this->getRepo()->getReference(Application::class, $data['application']);
        }

        if (!empty($data['transportManager'])) {
            return $this->getRepo()->getReference(TransportManager::class, $data['transportManager']);
        }

        if (!empty($data['busReg'])) {
            return $this->getRepo()->getReference(BusReg::class, $data['busReg']);
        }

        if (!empty($data['licence'])) {
            return $this->getRepo()->getReference(Licence::class, $data['licence']);
        }

        if (!empty($data['irfoOrganisation'])) {
            return $this->getRepo()->getReference(Organisation::class, $data['irfoOrganisation']);
        }

        if (!empty($data['continuationDetail'])) {
            return $this->getRepo()->getReference(ContinuationDetail::class, $data['continuationDetail']);
        }

        if (!empty($data['surrender'])) {
            return $this->getRepo()->getReference(Surrender::class, $data['surrender']);
        }

        if (!empty($data['irhpApplication'])) {
            return $this->getRepo()->getReference(IrhpApplication::class, $data['irhpApplication']);
        }

        return null;
    }

    /**
     * Note this is only intended for internal users, selfserve users don't have these access permissions
     *
     * Takes an array of traffic areas that will have come from a transfer object.
     * If empty or "all" is selected then return all traffic areas the user has access to
     *
     * @see TrafficAreas
     * @see TrafficAreasOptional
     */
    public function modifyTrafficAreaQueryBasedOnUser(QueryInterface $query): QueryInterface
    {
        $trafficAreas = $query->getTrafficAreas();

        if (empty($trafficAreas) || in_array('all', $trafficAreas)) {
            /**
             * reports have an "other" field which we will need to preserve
             * this will be ignored by anything which doesn't support it via an "in" query
             */
            $additional = ['other'];

            $newData = [
                'trafficAreas' => array_merge($this->getInternalUserTrafficAreas(), $additional),
            ];

            $query->exchangeArray($newData);
        }

        return $query;
    }

    /**
     * get user traffic areas (this data exists for internal users only)
     */
    public function getInternalUserTrafficAreas(): array
    {
        return $this->getUserData()['dataAccess']['trafficAreas'];
    }

    /**
     * gets a copy of the user account data - majority of the time this will come straight from the myaccount cache
     * if the cache doesn't exist we'll have a query handler result instead that will need to be serialized
     *
     * @return array
     */
    public function getUserData(): array
    {
        $accountInfo = $this->getQueryHandler()->handleQuery(MyAccount::create([]));

        if ($accountInfo instanceof \Dvsa\Olcs\Api\Domain\QueryHandler\Result) {
            return $accountInfo->serialize();
        }

        return $accountInfo;
    }
}
