<?php

namespace Common\Controller\Lva\Traits;

use Common\View\Model\Section;
use Laminas\Http\Response;
use Olcs\View\Model\ViewModel;

/**
 * Crud table trait
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait CrudTableTrait
{
    use CrudActionTrait;

    /**
     * Once the CRUD entity has been saved, handle the necessary redirect
     *
     * @param string $prefix  if our actions aren't just 'add', 'edit', provide a prefix
     * @param array  $options options to pass to assemble the route, eg ['fragment' => 'hash-ref']
     *
     * @return Response
     */
    protected function handlePostSave($prefix = null, $options = [])
    {
        // we can't just opt-in to all existing route params because
        // we might have a child ID if we're editing; if so we *don't*
        // want that in the redirect or we'll end up back on the same page
        $routeParams = [
            $this->getIdentifierIndex() => $this->getIdentifier()
        ];

        if ($this->isButtonPressed('addAnother')) {
            $routeParams['action'] = ($prefix !== null ? $prefix . '-add' : 'add');
            $route = null;
            $method = 'toRoute';
        } else {
            $route = $this->getBaseRoute();
            $method = 'toRouteAjax';
        }

        $this->flashMessengerHelper->addSuccessMessage(
            'section.' . $this->params('action') . '.' . $this->section
        );

        return $this->redirect()->$method($route, $routeParams, $options);
    }

    /**
     * Generic delete functionality; usually does the trick but
     * can be overridden if not
     *
     * @psalm-suppress all
     */
    public function deleteAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            /**
             * @VOL VOL-5192 has been created to refactor this piece of code ($this->delete sometimes returns void)
             * @phpstan-ignore-next-line
             */
            $response = $this->delete();
            /**
             * @VOL VOL-5191 has been created to refactor this piece of code (location of ViewModel)
             * @phpstan-ignore-next-line
             */
            if ($response instanceof Response || $response instanceof ViewModel) {
                return $response;
            }

            if ($response === false) {
                $this->deleteFailed();
            } else {
                $this->flashMessengerHelper->addSuccessMessage(
                    'section.' . $this->params('action') . '.' . $this->section
                );
            }

            return $this->redirect()->toRouteAjax(
                $this->getBaseRoute(),
                [
                    $this->getIdentifierIndex() => $this->getIdentifier(),
                ],
                [
                    'query' => $request->getQuery()->toArray(),
                ]
            );
        }

        $form = $this->formHelper
            ->createFormWithRequest($this->getDeleteConfirmationForm(), $request);

        $params = ['sectionText' => $this->getDeleteMessage()];

        return $this->render($this->getDeleteTitle(), $form, $params);
    }

    /**
     * Called when delete fails, eg to display a flash error message
     * Override in controller for specific messages
     *
     * @return void
     */
    protected function deleteFailed()
    {
        $this->flashMessengerHelper->addErrorMessage(
            'section.' . $this->params('action') . '.' . $this->section . '-failed'
        );
    }

    /**
     * This method needs to exists for deleteAction to work, the method should be overidden, but cannot be declared
     * abstract as it's not always required, so by default we throw an exception
     *
     * @throws \BadMethodCallException
     * @return void
     */
    protected function delete()
    {
        throw new \BadMethodCallException('Delete method must be implemented');
    }

    /**
     * Which delete message to use.
     *
     * @return string The modal message key.
     */
    protected function getDeleteMessage()
    {
        return 'delete.confirmation.text';
    }

    /**
     * Which delete title to use.
     *
     * @return string The modal message key.
     */
    protected function getDeleteTitle()
    {
        return 'delete';
    }

    /**
     * Return form, this method has been overridden in Olcs\Controller\Lva\Licence\TransportManagersController which
     * returns a different form if the last TM is being deleted.
     *
     * @return string
     */
    protected function getDeleteConfirmationForm()
    {
        return 'GenericDeleteConfirmation';
    }
}
