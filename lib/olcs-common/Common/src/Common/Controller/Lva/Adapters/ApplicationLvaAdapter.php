<?php

namespace Common\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;

class ApplicationLvaAdapter extends AbstractLvaAdapter
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    #[\Override]
    public function getIdentifier()
    {
        $id = $this->getController()->params('application');

        if ($id === null) {
            throw new \Exception("Can't get the application id from this controller");
        }

        return $id;
    }
}
