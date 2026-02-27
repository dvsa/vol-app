<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter\SectionRenderer;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager for letter section renderers
 *
 * @template-extends AbstractPluginManager<SectionRendererInterface>
 */
class SectionRendererPluginManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    protected $instanceOf = SectionRendererInterface::class;

    /**
     * Aliases map renderer keys to renderer classes
     *
     * @var array<string, class-string>
     */
    protected $aliases = [
        'content-section' => ContentSectionRenderer::class,
        'issue' => IssueSectionRenderer::class,
        // Future: 'todo' => TodoSectionRenderer::class,
        'appendix' => AppendixSectionRenderer::class,
    ];

    /**
     * All renderers use the same factory
     *
     * @var array<class-string, class-string>
     */
    protected $factories = [
        ContentSectionRenderer::class => SectionRendererFactory::class,
        IssueSectionRenderer::class => SectionRendererFactory::class,
        AppendixSectionRenderer::class => SectionRendererFactory::class,
    ];
}
