<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterType as LetterTypeMapper;
use Admin\Form\Model\Form\Letter\LetterType as LetterTypeForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterType\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterType\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterTypeController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-type';
    protected $defaultTableSortField = 'name';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterTypeForm::class;
    protected $addFormClass = LetterTypeForm::class;
    protected $mapperClass = LetterTypeMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Type';
    protected $editContentTitle = 'Edit Letter Type';

    protected $deleteModalTitle = 'Remove Letter Type';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter type?';
    protected $deleteSuccessMessage = 'The letter type has been removed';

    protected $addSuccessMessage = 'Letter type created successfully';
    protected $editSuccessMessage = 'Letter type updated successfully';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/letter-management',
                'navigationTitle' => 'Letter Management',
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function addAction()
    {


        $formClass = $this->addFormClass;
        $defaultDataProvider = new \Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData($this->defaultData);
        $createCommand = $this->createCommand;
        $mapperClass = $this->mapperClass;

        $defaultDataProvider->setParams($this->plugin('params'));

        $form = $this->getForm($formClass);
        $initialData = $mapperClass::mapFromResult($defaultDataProvider->provideParameters());

        $form->setData($initialData);

        $request = $this->getRequest();

        if ($request->isPost()) {
            error_log('Processing POST request');
            $postData = (array)$this->params()->fromPost();
            error_log('POST data received: ' . json_encode($postData));

            $form->setData($postData);

            $hasProcessed = $this->formHelperService->processAddressLookupForm($form, $request);
            error_log('Address lookup processed: ' . ($hasProcessed ? 'YES' : 'NO'));

            error_log('Checking conditions:');
            error_log('  - hasProcessed: ' . ($hasProcessed ? 'true' : 'false'));
            error_log('  - persist: ' . ($this->persist ? 'true' : 'false'));
            error_log('  - isPost: ' . ($request->isPost() ? 'true' : 'false'));
            error_log('  - isValid: ' . ($form->isValid() ? 'true' : 'false'));

            if (!$hasProcessed && $this->persist && $request->isPost() && $form->isValid()) {
                error_log('All conditions met - attempting to save');

                $data = \Laminas\Stdlib\ArrayUtils::merge($initialData, $form->getData());
                error_log('Merged data: ' . json_encode($data));

                $commandData = $this->mapFromForm($mapperClass, $data);
                error_log('Command data: ' . json_encode($commandData));

                error_log('Creating command: ' . $createCommand);

                // Create the command object to inspect it
                $commandObject = $createCommand::create($commandData);
                error_log('Command object class: ' . get_class($commandObject));
                error_log('Command object data: ' . json_encode($commandObject->getArrayCopy()));

                $response = $this->handleCommand($commandObject);

                error_log('Command response: ' . ($response->isOk() ? 'OK' : 'ERROR'));
                error_log('Response isClientError: ' . ($response->isClientError() ? 'YES' : 'NO'));
                error_log('Response isServerError: ' . ($response->isServerError() ? 'YES' : 'NO'));
                error_log('Response full result: ' . json_encode($response->getResult()));
                if (!$response->isOk()) {
                    error_log('Command errors: ' . json_encode($response->getResult()));
                    if ($response->isClientError()) {
                        error_log('Client error details: ' . json_encode($response->getResult()));
                    }
                    if ($response->isServerError()) {
                        error_log('Server error details: ' . json_encode($response->getResult()));
                    }
                }

                if ($response->isOk()) {
                    $this->flashMessengerHelperService->addSuccessMessage($this->addSuccessMessage);
                    return $this->redirectToRoute('admin-dashboard/admin-letter-type', [], [], true);
                }
            } else {
                error_log('Conditions not met, re-displaying form');
                if (!$form->isValid()) {
                    error_log('Form validation errors: ' . json_encode($form->getMessages()));
                }
            }
        }

        $this->placeholder()->setPlaceholder('form', $form);
        $this->placeholder()->setPlaceholder('contentTitle', $this->addContentTitle);

        return $this->viewBuilder()->buildViewFromTemplate($this->editViewTemplate);
    }
}
