<?php namespace Aether\View;

use Aether\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * User: mcfog
 * Date: 14-8-24
 */
class LayoutView extends TemplateView
{
    const D_LAYOUT_DEFAULT = 'aether.layout.default';

    /**
     * @var string
     */
    protected $layout;

    public function __construct(App $app, Response $response, $layout = null)
    {
        parent::__construct($app, $response);

        if (is_null($layout)) {
            $layout = $this->app->get(self::D_LAYOUT_DEFAULT);
        }

        $this->layout = $layout;
    }

    protected function renderTemplate($data, $template)
    {
        return parent::renderTemplate(
            array(
                'content' => parent::renderTemplate($data, $template)
            ),
            $this->layout
        );
    }

    /**
     * @param string $layout
     * @return $this
     *
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }
}
 