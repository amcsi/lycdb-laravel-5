<?php namespace Lycee\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Lycee\Card\FetchService;

class SearchController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @param FetchService $fetchService
     * @param ViewFactory $viewFactory
     * @param Request $request
     * @return Response
     */
	public function index(FetchService $fetchService, ViewFactory $viewFactory, Request $request)
	{
        $options = array ();
        $options['template'] = true;
        $options['pref_lang'] = 'en'; // prefer english

        $requestVars = $request->all();
        $results = $fetchService->getByRequest($requestVars);

        $vars = [];
        $vars['cards'] = $results;

        return $viewFactory->make('search', $vars);
    }
}
