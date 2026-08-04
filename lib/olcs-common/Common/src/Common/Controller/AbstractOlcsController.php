<?php

namespace Common\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Plugin\FeaturesEnabled as FeaturesEnabledPlugin;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;

/**
 * Abstract controller
 *
 * In general, methods in this controller should be kept to a minimum.
 * It should only be used for controller functionality needing to be shared between the selfserve and internal repos
 *
 * @method FeaturesEnabledPlugin featuresEnabled(array $toggleConfig, MvcEvent $e)
 * @method Response handleQuery(QueryInterface $query)
 * @method Response handleCommand(CommandInterface $query)
 * @method \Common\Controller\Plugin\Redirect redirect()
 */
abstract class AbstractOlcsController extends AbstractActionController
{
    /**
     * @var array
     *
     * Config for feature toggles - for usage see https://wiki.i-env.net/display/olcs/Feature+toggles
     */
    protected $toggleConfig = [];

    #[\Override]
    public function onDispatch(MvcEvent $e)
    {
        if ($this instanceof ToggleAwareInterface && !$this->featuresEnabled($this->toggleConfig, $e)) {
            return $this->notFoundAction();
        }

        return parent::onDispatch($e);
    }
}
