<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class EmissionsStandardsAnswerSaver implements AnswerSaverInterface
{
    use IrhpPermitApplicationOnlyTrait;

    public const EURO3_OR_EURO4_ANSWER = 'qanda.bilaterals.emissions-standards.euro3-or-euro4';

    /**
     * Create service instance
     *
     *
     * @return EmissionsStandardsAnswerSaver
     */
    public function __construct(private CountryDeletingAnswerSaver $countryDeletingAnswerSaver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        return $this->countryDeletingAnswerSaver->save(
            $qaContext,
            $postData,
            self::EURO3_OR_EURO4_ANSWER
        );
    }
}
