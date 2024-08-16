<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GovUkAccount;

use Dvsa\Olcs\Api\Service\GovUkAccount\GovUkAccountService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GetGovUkAccountRedirectFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GetGovUkAccountRedirect
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GetGovUkAccountRedirect
    {
        return (new GetGovUkAccountRedirect($container->get(GovUkAccountService::class)))->__invoke($container, $requestedName, $options);
    }
}
