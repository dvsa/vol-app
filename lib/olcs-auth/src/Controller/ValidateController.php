<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Controller;

use ArrayAccess;
use Common\Rbac\JWTIdentityProvider;
use Dvsa\Olcs\Auth\ControllerFactory\ValidateControllerFactory;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see ValidateControllerFactory
 * ValidateController have action to validate is user session is active
 */
class ValidateController extends AbstractActionController
{
    public function __construct(private IdentityProviderInterface $identityProvider)
    {
    }

    /**
     * Validate is user session (token) is valid (active)
     */
    #[\Override]
    public function indexAction(): JsonModel
    {
        /** @var JWTIdentityProvider $identityProvider */
        $identityProvider = $this->identityProvider;
        /** @var ArrayAccess<string, mixed> $respBody */
        $respBody = $identityProvider->validateToken();
        return new JsonModel($respBody);
    }
}
