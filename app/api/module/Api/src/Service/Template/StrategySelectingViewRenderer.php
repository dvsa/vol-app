<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Twig\Loader\LoaderInterface as TwigLoader;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

class StrategySelectingViewRenderer
{
    /**
     * Create service instance
     *
     *
     * @return StrategySelectingViewRenderer
     */
    public function __construct(private readonly RendererInterface $legacyViewRenderer, private readonly TwigRenderer $twigRenderer, private readonly TwigLoader $twigLoader)
    {
    }

    /**
     * Render the view using the editable twig template if available, otherwise fall back on the legacy template
     * within the filesystem
     *
     * @param string $locale
     * @param string $format
     * @param string $template
     *
     * @return string
     */
    public function render($locale, $format, $template, array $variables)
    {
        $templatePath = implode('/', [$locale, $format, $template]);

        if ($this->twigLoader->exists($templatePath)) {
            return $this->twigRenderer->render($templatePath, $variables);
        }

        $templateView = new ViewModel();
        $templateView->setTemplate($templatePath);
        $templateView->setVariables($variables);

        return $this->legacyViewRenderer->render($templateView);
    }
}
