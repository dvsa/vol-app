<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class GenericAnswerSaver implements AnswerSaverInterface
{
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return GenericAnswerSaver
     */
    public function __construct(private BaseAnswerSaver $baseAnswerSaver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function save(QaContext $qaContext, array $postData)
    {
        $this->baseAnswerSaver->save($qaContext, $postData);
    }
}
