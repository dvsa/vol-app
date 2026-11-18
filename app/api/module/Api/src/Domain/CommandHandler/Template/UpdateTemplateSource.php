<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Template;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Template\UpdateTemplateSource as UpdateTemplateSourceCmd;
use Exception;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\MarkdownConverter;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Update template source
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class UpdateTemplateSource extends AbstractCommandHandler
{
    public const FORMAT_MARKDOWN = 'md';

    protected $repoServiceName = 'Template';

    /** @var TwigRenderer */
    private $twigRenderer;

    /** md goes to Notify without HTML escaping, so it is checked that way */
    private TwigRenderer $plainTextTwigRenderer;

    private ?MarkdownConverter $markdownValidator = null;

    /**
     * Handle command
     *
     * @param UpdateTemplateSourceCmd|CommandInterface $command command
     *
     * @return Result
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $templateRepo = $this->getRepo();
        $template = $templateRepo->fetchUsingId($command);
        $source = $command->getSource();

        $isMarkdown = $template->getFormat() === self::FORMAT_MARKDOWN;
        $renderer = $isMarkdown ? $this->plainTextTwigRenderer : $this->twigRenderer;

        $testDatasets = $template->getDecodedTestData();
        if (!is_array($testDatasets)) {
            throw new ValidationException(['Template test data is not valid JSON']);
        }
        foreach ($testDatasets as $datasetName => $datasetValues) {
            // Test data must be {"<set name>": {<variables>}}, not a flat list of variables.
            if (!is_array($datasetValues)) {
                throw new ValidationException(
                    [sprintf('Template test data set %s is not a set of variables', $datasetName)]
                );
            }
            try {
                $rendered = $renderer->renderString($source, $datasetValues);
            } catch (Throwable $e) {
                throw new ValidationException(
                    [sprintf(
                        'Unable to render template content with dataset %s: %s',
                        $datasetName,
                        $e->getMessage()
                    )]
                );
            }

            if ($isMarkdown) {
                $this->assertMarkdownSafe($datasetName, $rendered);
            }
        }

        $template->setSource($source);
        $templateRepo->save($template);

        $this->result->addMessage('Template source updated');

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $fullContainer = $container;

        $this->twigRenderer = $container->get('TemplateTwigRenderer');
        $this->plainTextTwigRenderer = $container->get('TemplatePlainTextTwigRenderer');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }

    private function assertMarkdownSafe(string $datasetName, string $rendered): void
    {
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $rendered) === 1) {
            throw new ValidationException([sprintf(
                'Markdown template contains control characters (dataset %s)',
                $datasetName,
            )]);
        }

        $converter = $this->getMarkdownValidator();
        try {
            $converter->convert($rendered);
        } catch (Exception $e) {
            throw new ValidationException([sprintf(
                'Invalid Markdown in template (dataset %s): %s',
                $datasetName,
                $e->getMessage(),
            )]);
        }
    }

    private function getMarkdownValidator(): MarkdownConverter
    {
        if ($this->markdownValidator !== null) {
            return $this->markdownValidator;
        }

        $environment = new Environment([
            'disallowed_raw_html' => [
                // Strip any raw HTML so templates can't bypass Notify's rendering.
                'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'iframe', 'noembed', 'noframes', 'script', 'plaintext'],
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());

        return $this->markdownValidator = new MarkdownConverter($environment);
    }
}
