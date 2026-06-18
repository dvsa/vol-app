<?php

namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Interfaces\ControllerAwareInterface;
use Common\Controller\Lva\Traits\ControllerAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractControllerAwareAdapter extends AbstractAdapter implements ControllerAwareInterface
{
    use ControllerAwareTrait;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * We override the parent method here, so we can pass in the controller
     *
     * @param string|null $lva
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    protected function getLvaAdapter(string $lva = null): AbstractLvaAdapter
    {
        $adapter = parent::getLvaAdapter($lva);

        $adapter->setController($this->getController());

        return $adapter;
    }
}
