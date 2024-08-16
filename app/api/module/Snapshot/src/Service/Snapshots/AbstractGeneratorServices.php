<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots;

use Laminas\View\Renderer\RendererInterface;

/**
 * Abstract Generator Services
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AbstractGeneratorServices
{
    /**
     * Create service instance
     *
     * @param RendererInterface $viewRenderer
     *
     * @return AbstractGeneratorServices
     */
    public function __construct(private readonly RendererInterface $renderer)
    {
    }

    /**
     * Return the renderer service
     *
     * @return RendererInterface
     */
    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }
}
