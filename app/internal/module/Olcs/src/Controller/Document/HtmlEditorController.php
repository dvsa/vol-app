<?php

namespace Olcs\Controller\Document;

use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractController;
use Common\Service\Helper\FormHelperService;

/**
 * HTML Editor Controller for the proof of concept
 */
class HtmlEditorController extends AbstractController
{

    protected FormHelperService $formHelper;

    /**
     * Constructor
     *
     * @param FormHelperService $formHelper Form helper service
     */
    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    /**
     * Section selection form
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $form = $this->formHelper->createForm(\Olcs\Form\Model\Form\HtmlEditor::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $selectedSections = [];

                foreach ($data['sections'] as $key => $value) {
                    if ($value) {
                        $selectedSections[] = $key;
                    }
                }

                $this->getSessionContainer()->selectedSections = $selectedSections;

                return $this->redirect()->toRoute('html-editor/edit');
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('olcs/document/html-editor/index');

        return $this->renderView($view, 'HTML Editor - Section Selection');
    }

    /**
     * Editor interface
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function editAction()
    {
        $selectedSections = $this->getSessionContainer()->selectedSections ?? [];

        if (empty($selectedSections)) {
            return $this->redirect()->toRoute('html-editor');
        }

        $sectionDefaults = $this->getSectionDefaults($selectedSections);

        $view = new ViewModel([
            'selectedSections' => $selectedSections,
            'sectionDefaults' => json_encode($sectionDefaults)
        ]);

        $view->setTemplate('olcs/document/html-editor/edit');

        return $this->renderView($view, 'HTML Editor - Edit Content');
    }

    /**
     * Display JSON result
     *
     * @return ViewModel
     */
    public function resultAction()
    {
        $editorData = $this->params()->fromPost('editor_data', '{}');

        $view = new ViewModel([
            'editorData' => $editorData
        ]);

        $view->setTemplate('olcs/document/html-editor/result');

        return $this->renderView($view, 'HTML Editor - Result');
    }

    /**
     * Get session container for this controller
     *
     * @return \Laminas\Session\Container
     */
    private function getSessionContainer()
    {
        return new \Laminas\Session\Container('htmlEditor');
    }

    /**
     * Get default content for selected sections
     *
     * @param array $selectedSections Selected sections
     *
     * @return array
     */
    private function getSectionDefaults(array $selectedSections)
    {
        // some fixture content to pre-populate the sections chosen - would probably come from the database, and would probably be editable by Admins via a UI in the final version
        $defaults = [
            'financial' => [
                'blocks' => [
                    [
                        'type' => 'header',
                        'data' => [
                            'text' => 'Financial Information',
                            'level' => 2
                        ]
                    ],
                    [
                        'type' => 'paragraph',
                        'data' => [
                            'text' => 'Default financial section text that can be edited by the caseworker. This section would typically include details about financial standing, bank statements, and other financial evidence.'
                        ]
                    ],
                    [
                        'type' => 'list',
                        'data' => [
                            'style' => 'unordered',
                            'items' => [
                                'Bank statements',
                                'Financial guarantees',
                                'Other evidence of financial standing'
                            ]
                        ]
                    ]
                ]
            ],
            'personnel' => [
                'blocks' => [
                    [
                        'type' => 'header',
                        'data' => [
                            'text' => 'Personnel Information',
                            'level' => 2
                        ]
                    ],
                    [
                        'type' => 'paragraph',
                        'data' => [
                            'text' => 'Default people section text that can be edited by the caseworker. This section would typically include details about transport managers, directors, and other key personnel.'
                        ]
                    ],
                    [
                        'type' => 'list',
                        'data' => [
                            'style' => 'unordered',
                            'items' => [
                                'Transport managers',
                                'Directors',
                                'Partners',
                                'Other key personnel'
                            ]
                        ]
                    ]
                ]
            ],
            'operatingCenters' => [
                'blocks' => [
                    [
                        'type' => 'header',
                        'data' => [
                            'text' => 'Operating Centers',
                            'level' => 2
                        ]
                    ],
                    [
                        'type' => 'paragraph',
                        'data' => [
                            'text' => 'Default operating centers text that can be edited by the caseworker. This section would typically include details about operating centers, vehicle authorizations, and environmental concerns.'
                        ]
                    ],
                    [
                        'type' => 'list',
                        'data' => [
                            'style' => 'unordered',
                            'items' => [
                                'Operating center addresses',
                                'Vehicle authorizations',
                                'Environmental concerns',
                                'Local objections'
                            ]
                        ]
                    ]
                ]
            ],
            'vehicles' => [
                'blocks' => [
                    [
                        'type' => 'header',
                        'data' => [
                            'text' => 'Vehicles Information',
                            'level' => 2
                        ]
                    ],
                    [
                        'type' => 'paragraph',
                        'data' => [
                            'text' => 'Default vehicles section text that can be edited by the caseworker. This section would typically include details about vehicles, maintenance arrangements, and safety inspections.'
                        ]
                    ],
                    [
                        'type' => 'list',
                        'data' => [
                            'style' => 'unordered',
                            'items' => [
                                'Vehicle types',
                                'Maintenance arrangements',
                                'Safety inspections',
                                'MOT history'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $result = [];
        foreach ($selectedSections as $section) {
            if (isset($defaults[$section])) {
                $result[$section] = $defaults[$section];
            }
        }

        return $result;
    }
}
