<?php

namespace OlcsTest\Form\Model\Form\Surrender\CurrentDiscs;

use Common\Form\Elements\Custom\OlcsCheckbox;
use Common\Form\Elements\Types\HtmlTranslated;
use OlcsTest\TestHelpers\AbstractFormValidationTestCase;
use Olcs\Form\Model\Form\Surrender\CurrentDiscs\CurrentDiscs;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Textarea;

class CurrentDiscsTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = CurrentDiscs::class;

    public function testHeaderSection()
    {
        $headerSection = ['headerSection', 'header'];
        $this->assertFormElementType($headerSection, HtmlTranslated::class);
        $this->assertFormElementHtml($headerSection);
    }

    public function testPossessionInfoNumber()
    {
        $element = ['possessionSection', 'info', 'number'];
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, Number::class);
    }

    public function testLost()
    {
        $element = ['lostSection', 'lost'];
        $this->assertFormElementType($element, Checkbox::class);
    }

    public function testLostInfoNumber()
    {
        $element = ['lostSection', 'info', 'number'];
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, Number::class);
    }

    public function testLostInfoDetails()
    {
        $element = ['lostSection', 'info', 'details'];
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, Textarea::class);
    }

    public function testStolen()
    {
        $element = ['stolenSection', 'stolen'];
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testStolenInfoDetails()
    {
        $element = ['stolenSection', 'info', 'details'];
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, Textarea::class);
    }

    public function testStolenInfoNumber()
    {
        $element = ['stolenSection', 'info', 'number'];
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, Number::class);
    }

    /**
     * @dataProvider dpInfoNumber
     */
    public function testInfoNumberValidation($section, $formData, $expected)
    {
        $form = $this->getForm();

        $this->setPost($formData);
        $form->setData($formData);
        $form->setValidationGroup([$section]);

        $valid = $form->isValid();
        $this->assertEquals($expected, $valid);

        $this->clearPost();
    }

    /**
     * @dataProvider dpInfoDetails
     */
    public function testInfoDetailsValidation($section, $formData, $expected)
    {
        $form = $this->getForm();

        $this->setPost($formData);
        $form->setData($formData);
        $form->setValidationGroup([$section]);

        $valid = $form->isValid();
        $this->assertEquals($expected, $valid);

        $this->clearPost();
    }


    public function testSubmit()
    {
        $element = ['submit'];
        $this->assertFormElementActionButton($element);
        $this->assertFormElementType($element, Button::class);
    }

    public function testLostSectionLost()
    {
        $element = ['lostSection', 'lost'];
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function testPosessionSectionInPosession()
    {
        $element = ['possessionSection', 'inPossession'];
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, OlcsCheckbox::class);
    }

    public function dpInfoNumber()
    {
        return [
            'case_not_in_possession' => [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'N',
                        'info' => [
                            'number' => ''
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_in_possession_number_valid' => [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 4
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_in_possession_number_below_min' => [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 0
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_in_possession_number_empty' => [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => ''
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_in_possession_number_string' => [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 'aa'
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_not_lost' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'N',
                        'info' => [
                            'number' => '',
                            'details' => ''
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_lost_number_valid' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_lost_number_below_min' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 0,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_lost_number_empty' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => '',
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_lost_number_string' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 'aa',
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_not_stolen' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'N',
                        'info' => [
                            'number' => '',
                            'details' => ''
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_stolen_number_valid' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_stolen_number_below_min' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 0,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_stolen_number_empty' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => '',
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_stolen_number_string' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 'aa',
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
        ];
    }

    public function dpInfoDetails()
    {
        return [
            'case_lost_lost_valid' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_lost_lost_below_min' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => str_repeat('abcd ', 101)
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_lost_lost_over_max' => [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => ''
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_stolen_lost_valid' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => true
            ],
            'case_stolen_lost_below_min' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => str_repeat('abcd ', 101)
                        ]
                    ]
                ],
                'expected' => false
            ],
            'case_stolen_lost_over_max' => [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => ''
                        ]
                    ]
                ],
                'expected' => false
            ]
        ];
    }

    protected function setPost(array $data)
    {
        $_POST = $data;
    }

    protected function clearPost()
    {
        $_POST = [];
    }
}
