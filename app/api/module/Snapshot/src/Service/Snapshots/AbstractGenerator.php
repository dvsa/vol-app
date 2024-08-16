<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

/**
 * Abstract Generator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGenerator
{
    /** @var RendererInterface */
    private $renderer;

    /**
     * Create service instance
     *
     *
     * @return AbstractGenerator
     */
    public function __construct(AbstractGeneratorServices $abstractGeneratorServices)
    {
        $this->renderer = $abstractGeneratorServices->getRenderer();
    }

    protected function generateReadonly(array $config, $template = 'review')
    {
        $model = new ViewModel($config);
        $model->setTerminal(true);
        $model->setTemplate('layout/' . $template);

        return $this->renderer->render($model);
    }
}
