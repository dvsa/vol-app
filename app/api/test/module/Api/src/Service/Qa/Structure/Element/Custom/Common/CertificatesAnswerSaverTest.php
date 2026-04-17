<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Common;

use Dvsa\Olcs\Api\Entity\Generic\Question as QuestionEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\BaseAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\CertificatesAnswerSaver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CertificatesAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CertificatesAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $postData = [
            'fieldset68' => [
                'qaElement' => '14'
            ]
        ];

        $qaContext = m::mock(QaContext::class);

        $baseAnswerSaver = m::mock(BaseAnswerSaver::class);
        $baseAnswerSaver->shouldReceive('save')
            ->with($qaContext, $postData, QuestionEntity::QUESTION_TYPE_BOOLEAN)
            ->once();

        $certificatesAnswerSaver = new CertificatesAnswerSaver($baseAnswerSaver);
        $certificatesAnswerSaver->save($qaContext, $postData);
    }
}
