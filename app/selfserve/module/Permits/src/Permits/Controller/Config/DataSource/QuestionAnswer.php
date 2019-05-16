<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\QuestionAnswer as QuestionAnswerDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Question/Answer data source config
 */
class QuestionAnswer extends AbstractDataSource
{
    const DATA_KEY = 'questionAnswer';
    protected $dto = QuestionAnswerDto::class;
}
