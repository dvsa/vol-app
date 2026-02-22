<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Template;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Template\UpdateTemplateSource as UpdateTemplateSourceCmd;
use Exception;
use Psr\Container\ContainerInterface;

/**
 * Update template source
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class UpdateTemplateSource extends AbstractCommandHandler
{
    protected $repoServiceName = 'Template';

    /** @var TwigRenderer */
    private $twigRenderer;

    /**
     * Handle command
     *
     * @param UpdateTemplateSourceCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $templateRepo = $this->getRepo();
        $template = $templateRepo->fetchUsingId($command);
        $source = $command->getSource();

        $testDatasets = $template->getDecodedTestData();
        foreach ($testDatasets as $datasetName => $datasetValues) {
            try {
                $this->twigRenderer->renderString($source, $datasetValues);
            } catch (Exception $e) {
                throw new ValidationException(
                    [sprintf(
                        'Unable to render template content with dataset %s: %s',
                        $datasetName,
                        $e->getMessage()
                    )]
                );
            }
        }

        $template->setSource($source);
        $templateRepo->save($template);

        $this->result->addMessage('Template source updated');

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->twigRenderer = $container->get('TemplateTwigRenderer');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
