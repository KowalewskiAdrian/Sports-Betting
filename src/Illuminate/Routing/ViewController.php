<?php

namespace Illuminate\Routing;

use Illuminate\Contracts\View\Factory as ViewFactory;

class ViewController extends Controller
{
    /**
     * The view factory instance.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new view controller instance.
     *
     * @param \Illuminate\Contracts\View\Factory $view
     * @return void
     */
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    /**
     * Invoke the controller method.
     *
     * @param string $view
     * @return \Illuminate\Contracts\View\View
     */
    public function __invoke($view)
    {
        return $this->view->make($view);
    }
}
