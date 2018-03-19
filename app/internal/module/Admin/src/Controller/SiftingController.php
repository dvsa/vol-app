<?php

/**
 * Sifting Controller
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\Sectors\Sectors as ListDto;
use Common\Controller\Traits\GenericRenderView;
use Zend\View\Model\ViewModel;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Client;
use Zend\Http\Request;



/**
 * Sifting Controller
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class SiftingController extends AbstractInternalController implements LeftViewProvider
{

    use GenericRenderView;

    protected $navigationId = 'admin-dashboard/admin-sifting';


    // list
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/sifting/sifting';
    protected $defaultTableSortField = 'sectorId';
    protected $defaultTableOrderField = 'ASC';
    protected $defaultTableLimit = 25;
    protected $tableName = 'admin-sifting-sector';
    protected $listDto = ListDto::class;

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Sifting');
        return parent::indexAction();
    }

    public function getLeftView()
    {
        $view = new ViewModel(
          [
            'navigationId' => 'admin-dashboard/admin-sifting',
            'navigationTitle' => 'Sifting'
          ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function postAction()
    {

        $url = 'http://192.168.0.22:8000/permits/sifting/apply';
        $request = new Request;
        $request->getHeaders()->addHeaders([
          'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
        ]);
        $request->setUri($url);
        $request->setMethod('PUT');
        $request->getPost()->set('username', 'ff');
        $client = new Client;
        $curl = new Curl;
        $client->setAdapter($curl);
        $response = $client->dispatch($request);




        $view = new ViewModel();
        $view->setTemplate('admin/sifting/run');
        $view->setVariable('curlResponse', $response->getContent());

        return $this->renderView($view, 'Sifting is running');
    }
}
