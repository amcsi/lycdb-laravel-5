<?php

namespace Lycee\Tool;

use Illuminate\Contracts\Container\Container;

/**x
 *
 * Lycee\Tool/Helper
 */
class Helper
{

    /**
     * @var Container
     */
    private $app;

    function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @see \Lycee\Tool\MarkupToHtml::convert()
     * @param string $markup
     * @return string
     */
    public function lycdbMarkupToHtml($markup)
    {
        return $this->app->make('Lycee\Tool\MarkupToHtml')->convert($markup);
    }
}