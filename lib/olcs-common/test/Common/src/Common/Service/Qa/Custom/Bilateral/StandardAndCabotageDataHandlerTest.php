<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageDataHandler;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageIsValidHandler;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageSubmittedAnswerGenerator;
use Common\Service\Qa\Custom\Common\WarningAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;

/**
 * StandardAndCabotageDataHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageDataHandlerTest extends MockeryTestCase
{
    private $qaForm;

    private $standardAndCabotageSubmittedAnswerGenerator;

    private $standardAndCabotageIsValidHandler;

    private $warningAdder;

    private $standardAndCabotageDataHandler;

    #[\Override]
    protected function setUp(): void
    {
        $this->qaForm = m::mock(QaForm::class);

        $this->standardAndCabotageSubmittedAnswerGenerator = m::mock(
            StandardAndCabotageSubmittedAnswerGenerator::class
        );

        $this->standardAndCabotageIsValidHandler = m::mock(StandardAndCabotageIsValidHandler::class);

        $this->warningAdder = m::mock(WarningAdder::class);

        $this->standardAndCabotageDataHandler = new StandardAndCabotageDataHandler(
            $this->standardAndCabotageSubmittedAnswerGenerator,
            $this->standardAndCabotageIsValidHandler,
            $this->warningAdder
        );
    }

    public function testSetDataIsValidDoNothing(): void
    {
        self::expectNotToPerformAssertions();

        $this->standardAndCabotageIsValidHandler->shouldReceive('isValid')
            ->with($this->qaForm)
            ->andReturnTrue();

        $this->standardAndCabotageDataHandler->setData($this->qaForm);
    }

    public function testSetDataIsNotValidAddWarning(): void
    {
        $this->standardAndCabotageIsValidHandler->shouldReceive('isValid')
            ->with($this->qaForm)
            ->andReturnFalse();

        $submittedAnswer = 'submitted_answer';

        $this->standardAndCabotageSubmittedAnswerGenerator->shouldReceive('generate')
            ->with($this->qaForm)
            ->andReturn($submittedAnswer);

        $warningVisibleElement = m::mock(Hidden::class);
        $warningVisibleElement->shouldReceive('setValue')
            ->with($submittedAnswer)
            ->once();

        $questionFieldset = m::mock(Fieldset::class);
        $questionFieldset->shouldReceive('get')
            ->with('warningVisible')
            ->andReturn($warningVisibleElement);

        $this->qaForm->shouldReceive('getQuestionFieldset')
            ->withNoArgs()
            ->andReturn($questionFieldset);

        $this->warningAdder->shouldReceive('add')
            ->with($questionFieldset, 'qanda.bilaterals.standard-and-cabotage.warning')
            ->once();

        $this->standardAndCabotageDataHandler->setData($this->qaForm);
    }
}
