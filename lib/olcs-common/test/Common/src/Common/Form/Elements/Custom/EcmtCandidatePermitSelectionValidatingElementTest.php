<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\EcmtCandidatePermitSelectionValidatingElement;
use Common\Form\Elements\Validators\EcmtCandidatePermitSelectionValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Form\Element\Hidden;
use Laminas\Validator\Callback;

/**
 * EcmtCandidatePermitSelectionValidatingElementTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class EcmtCandidatePermitSelectionValidatingElementTest extends MockeryTestCase
{
    public const string ELEMENT_NAME = 'elementName123';

    private $ecmtCandidatePermitSelectionValidatingElement;

    #[\Override]
    protected function setUp(): void
    {
        $this->ecmtCandidatePermitSelectionValidatingElement = new EcmtCandidatePermitSelectionValidatingElement(
            self::ELEMENT_NAME
        );
    }

    public function testGetInputSpecification(): void
    {
        $expectedInputSpecification = [
            'name' => self::ELEMENT_NAME,
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => Callback::class,
                    'options' => [
                        'messages' => [
                            Callback::INVALID_VALUE => 'permits.page.irhp.candidate-permit-selection.error'
                        ]
                    ],
                ],
            ],
        ];

        $actual = $this->ecmtCandidatePermitSelectionValidatingElement->getInputSpecification();

        // The callback is a closure which cannot be reliably compared for equality;
        // assert it delegates to the validator, then compare the rest of the structure.
        $callback = $actual['validators'][0]['options']['callback'];
        $this->assertIsCallable($callback);
        $this->assertTrue($callback('notused', ['candidate-1' => '1']));
        $this->assertFalse($callback('notused', ['candidate-1' => '0']));
        unset($actual['validators'][0]['options']['callback']);

        $this->assertEquals($expectedInputSpecification, $actual);
    }

    public function testInstanceOf(): void
    {
        $this->assertInstanceOf(
            Hidden::class,
            $this->ecmtCandidatePermitSelectionValidatingElement
        );
    }
}
