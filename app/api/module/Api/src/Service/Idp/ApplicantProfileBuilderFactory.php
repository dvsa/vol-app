<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp;

use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicantProfileBuilderFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ApplicantProfileBuilder {
        /** @var FinancialStandingHelperService $helper */
        $helper = $container->get('FinancialStandingHelperService');

        return new ApplicantProfileBuilder($helper);
    }
}
