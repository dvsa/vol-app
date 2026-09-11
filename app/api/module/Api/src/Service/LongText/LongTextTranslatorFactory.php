<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\LongText;

use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LongTextTranslatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LongTextTranslator
    {
        /** @var LongTextRepo $repository */
        $repository = $container->get('RepositoryServiceManager')->get('LongText');

        return new LongTextTranslator(
            $container->get('translator'),
            $repository,
            $container->get(LongTextConverterService::class),
        );
    }
}
