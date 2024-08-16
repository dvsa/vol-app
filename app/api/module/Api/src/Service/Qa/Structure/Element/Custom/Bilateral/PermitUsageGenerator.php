<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\RadioFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;

class PermitUsageGenerator implements ElementGeneratorInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return PermitUsageGenerator
     */
    public function __construct(private RadioFactory $radioFactory, private TranslateableTextGenerator $translateableTextGenerator, private OptionFactory $optionFactory, private OptionListFactory $optionListFactory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $applicationStepEntity = $context->getApplicationStepEntity();
        $options = $applicationStepEntity->getDecodedOptionSource();

        $irhpPermitApplication = $context->getQaEntity();
        $permitUsageList = $irhpPermitApplication->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getPermitUsageList();

        if (empty($permitUsageList)) {
            throw new NotFoundException('Permit usage not found');
        }

        $optionList = $this->optionListFactory->create($this->optionFactory);

        foreach ($permitUsageList as $permitUsage) {
            $optionList->add($permitUsage->getId(), $permitUsage->getDescription());
        }

        return $this->radioFactory->create(
            $optionList,
            $this->translateableTextGenerator->generate($options['notSelectedMessage']),
            $context->getAnswerValue()
        );
    }
}
