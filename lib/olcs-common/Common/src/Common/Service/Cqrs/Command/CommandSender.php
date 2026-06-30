<?php

namespace Common\Service\Cqrs\Command;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Psr\Container\ContainerInterface;

class CommandSender implements FactoryInterface
{
    /**
     * @var TransferAnnotationBuilder
     */
    private $annotationBuilder;

    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * @return \Common\Service\Cqrs\Response
     */
    public function send(CommandInterface $command)
    {
        $command = $this->annotationBuilder->createCommand($command);
        return $this->commandService->send($command);
    }

    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CommandSender
    {
        $this->commandService = $container->get('CommandService');
        $this->annotationBuilder = $container->get('TransferAnnotationBuilder');
        return $this;
    }
}
