<?php namespace Aether\View;

use Aether\App;
use Aether\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * User: mcfog
 * Date: 14-8-23
 */
abstract class AbstractView
{
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app, Response $response)
    {
        $this->response = $response;
        $this->app = $app;
    }

    /**
     * @param Context $ctx
     * @return static
     */
    public static function instance(Context $ctx)
    {
        return new static($ctx->getApp(), $ctx->getResponse());
    }

    abstract public function render($data);
}
