<?php

namespace OlcsTest\Form\Model\Form\Surrender\CurrentDiscs;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Olcs\Form\Model\Form\Surrender\CurrentDiscs\CurrentDiscs;
use Zend\Form\Element\Button;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Number;
use Zend\Form\Element\Textarea;

class CurrentDiscsTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = CurrentDiscs::class;

    public function testInPossession()
    {
        $element = ['possessionSection', 'inPossession'];
        $this->assertFormElementType($element, Checkbox::class);
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
        $this->assertFormElementType($element, Checkbox::class);
    }

    public function testStolenInfoDetails()
    {
        $element = ['stolenSection', 'info', 'details'];
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementType($element, Textarea::class);
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
        $action = ['submit'];
        $this->assertFormElementType($action, Button::class);
    }

    public function dpInfoNumber()
    {
        return [
            [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'N',
                        'info' => [
                            'number' => '',
                            'details' => ''
                        ]
                    ]
                ],
                'expected' => true
            ],
            [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => true
            ],
            [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 0,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => -1,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => '',
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            [
                'section' => 'possessionSection',
                'formData' => [
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 'aa',
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            [
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
            [
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
            [
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
            [
                'section' => 'lostSection',
                'formData' => [
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => -1,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            [
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
            [
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
            [
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
            [
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
            [
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
            [
                'section' => 'stolenSection',
                'formData' => [
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => -1,
                            'details' => str_repeat('abcd ', 100)
                        ]
                    ]
                ],
                'expected' => false
            ],
            [
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
            [
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
            [
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
            [
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
            [
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
            [
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
            [
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
            [
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
