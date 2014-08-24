<?php namespace Aether;

use FastRoute\Dispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * User: mcfog
 * Date: 14-8-23
 */

/**
 * runtime context include the Request, the Response and the App
 *
 * @package Aether
 */
class Context extends \ArrayObject
{
    protected $app;
    protected $request;
    protected $response;

    public function __construct(App $app, Request $request, Response $response = null)
    {
        if (is_null($response)) {
            $response = new Response();
        }

        $this->app = $app;
        $this->request = $request;
        $this->response = $response;

        parent::__construct(array(), \ArrayObject::ARRAY_AS_PROPS);
    }

    public static function current(App $app)
    {
        return new self($app, Request::createFromGlobals());
    }


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    public function triggerEvent($name, $subject = null, array $args = array())
    {
        $event = new Event($this, $subject, $args);
        $this->app->getEventDispatcher()->dispatch($name, $event);
    }
}
