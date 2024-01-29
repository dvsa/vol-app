<?php

namespace Olcs\Controller;

use Common\Controller\Traits as CommonTraits;
use Common\Controller\Traits\GenericMethods;
use Common\Controller\Traits\GenericRenderView;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Common\Util\FlashMessengerTrait;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Traits as OlcsTraits;

/**
 * Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 *
 * @method \Olcs\Mvc\Controller\Plugin\Placeholder placeholder()
 * @method \Common\Service\Cqrs\Response handleQuery(\Dvsa\Olcs\Transfer\Query\QueryInterface $query)
 * @method \Common\Service\Cqrs\Response handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $query)
 * @method \Common\Controller\Plugin\Redirect redirect()
 */
class AbstractController extends LaminasAbstractActionController
{
    use CommonTraits\ViewHelperManagerAware;
    use OlcsTraits\ListDataTrait;
    use GenericRenderView;
    use GenericMethods;
    use FlashMessengerTrait;

    protected ScriptFactory $scriptFactory;
    protected FormHelperService $formHelper;
    protected TableFactory $tableFactory;
    protected HelperPluginManager $viewHelperManager;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager
    ) {
        $this->scriptFactory = $scriptFactory;
        $this->formHelper = $formHelper;
        $this->tableFactory = $tableFactory;
        $this->viewHelperManager = $viewHelperManager;
    }

    /**
     * Gets a variable from the route
     *
     * @param string $param   route parameter name
     * @param mixed  $default default value if null
     *
     * @return mixed
     */
    public function fromRoute($param, $default = null)
    {
        return $this->params()->fromRoute($param, $default);
    }

    /**
     * Gets a variable from postdata
     *
     * @param string $param   post parameter name
     * @param mixed  $default default value if null
     *
     * @return mixed
     */
    public function fromPost($param, $default = null)
    {
        return $this->params()->fromPost($param, $default);
    }

    /**
     * Proxies to the get query or get param.
     *
     * @param mixed $name    parameter name
     * @param mixed $default default value if null
     *
     * @return mixed
     */
    public function getQueryOrRouteParam($name, $default = null)
    {
        if ($queryValue = $this->params()->fromQuery($name, $default)) {
            return $queryValue;
        }

        if ($queryValue = $this->params()->fromRoute($name, $default)) {
            return $queryValue;
        }

        return $default;
    }

    /**
     * Sets the table filters.
     *
     * @param mixed $filters parameter name
     *
     * @return void
     */
    public function setTableFilters($filters)
    {
        $this->placeholder()->setPlaceholder('tableFilters', $filters);
    }
}
