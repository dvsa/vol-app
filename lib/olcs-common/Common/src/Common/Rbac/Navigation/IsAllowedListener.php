<?php

namespace Common\Rbac\Navigation;

use Laminas\Navigation\Page\Mvc;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Guard\GuardInterface;
use LmcRbacMvc\Service\AuthorizationServiceInterface;
use LmcRbacMvc\Guard\ProtectionPolicyTrait;
use Laminas\EventManager\Event;
use Psr\Container\ContainerInterface;

class IsAllowedListener implements FactoryInterface
{
    use ProtectionPolicyTrait;

    /**
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    /**
     * Route guard rules
     * Those rules are an associative array that map a rule with one or multiple permissions
     * @var array
     */
    protected $rules = [];

    /**
     * @param \LmcRbacMvc\Service\AuthorizationServiceInterface $authorizationService
     */
    public function setAuthorizationService($authorizationService): void
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * @return \LmcRbacMvc\Service\AuthorizationServiceInterface
     */
    public function getAuthorizationService()
    {
        return $this->authorizationService;
    }

    /**
     * Set the rules (it overrides any existing rules)
     */
    public function setRules(array $rules): void
    {
        $this->rules = [];
        foreach ($rules as $key => $value) {
            if (is_int($key)) {
                $routeRegex = $value;
                $permissions = [];
            } else {
                $routeRegex = $key;
                $permissions = (array) $value;
            }

            $this->rules[$routeRegex] = $permissions;
        }
    }

    /**
     * @return bool
     */
    public function isGranted(Mvc $page)
    {
        $matchedRouteName = $page->getRoute();
        $allowedPermissions = null;
        foreach (array_keys($this->rules) as $routeRule) {
            if (fnmatch($routeRule, $matchedRouteName, FNM_CASEFOLD)) {
                $allowedPermissions = $this->rules[$routeRule];
                break;
            }
        }

        // If no rules apply, it is considered as granted or not based on the protection policy
        if (null === $allowedPermissions) {
            return $this->protectionPolicy === GuardInterface::POLICY_ALLOW;
        }

        if (in_array('*', $allowedPermissions, true)) {
            return true;
        }

        foreach ($allowedPermissions as $permission) {
            if (!$this->authorizationService->isGranted($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function accept(Event $event)
    {
        $page = $event->getParam('page');
        if (! $page instanceof Mvc) {
            return true;
        }

        $event->stopPropagation();

        return $this->isGranted($page);
    }

    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IsAllowedListener
    {
        $this->setAuthorizationService($container->get(\LmcRbacMvc\Service\AuthorizationService::class));
        $options = $container->get(\LmcRbacMvc\Options\ModuleOptions::class);
        $this->setProtectionPolicy($options->getProtectionPolicy());
        $guardsOptions = $options->getGuards();
        if (isset($guardsOptions[\LmcRbacMvc\Guard\RoutePermissionsGuard::class])) {
            $this->setRules($guardsOptions[\LmcRbacMvc\Guard\RoutePermissionsGuard::class]);
        }

        return $this;
    }
}
