<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Service\Nr\InputFilter\CgrInputFactory;
use Dvsa\Olcs\Api\Service\Nr\Mapping\CgrResponseXml;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck\Generator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class CheckReputeProcessDocumentFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CheckReputeProcessDocument
    {
        $fileUploader = $container->get('FileUploader');
        $cgrInputFilter = $container->get(CgrInputFactory::class);
        $cgrXmlMapping = $container->get(CgrResponseXml::class);
        $snapshotGenerator = $container->get(Generator::class);

        $checkReputeHandler = new CheckReputeProcessDocument(
            $fileUploader,
            $cgrInputFilter,
            $cgrXmlMapping,
            $snapshotGenerator
        );

        return $checkReputeHandler->__invoke($container, $requestedName, $options);
    }
}
