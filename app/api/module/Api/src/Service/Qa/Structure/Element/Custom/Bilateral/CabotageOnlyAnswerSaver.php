<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class CabotageOnlyAnswerSaver implements AnswerSaverInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return CabotageOnlyAnswerSaver
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
            Answer::BILATERAL_CABOTAGE_ONLY
        );
    }
}
