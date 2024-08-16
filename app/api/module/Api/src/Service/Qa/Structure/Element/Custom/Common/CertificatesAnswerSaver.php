<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common;

use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\BaseAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class CertificatesAnswerSaver implements AnswerSaverInterface
{
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return CertificatesAnswerSaver
     */
    public function __construct(private BaseAnswerSaver $baseAnswerSaver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $this->baseAnswerSaver->save($qaContext, $postData, Question::QUESTION_TYPE_BOOLEAN);
    }
}
