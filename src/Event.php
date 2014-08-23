<?php namespace Aether;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * User: mcfog
 * Date: 14-8-23
 */
class Event extends GenericEvent
{
    const NOT_FOUND = 'aether.event.not-found';
    const ACCESS_DENY = 'aether.event.access-deny';
    const INTERNAL_ERROR = 'aether.event.internal-error';

    const BEFORE_DISPATCH = 'aether.event.before-dispatch';
    const BEFORE_LOGIC = 'aether.event.before-logic';
    const AFTER_LOGIC = 'aether.event.after-logic';
    const BEFORE_OUTPUT = 'aether.event.before-output';
    const BEFORE_END = 'aether.event.before-output';

    /**
     * @var App
     */
    protected $app;
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $ctx, $subject = null, array $arguments = array())
    {
        parent::__construct($subject, $arguments);
        $this->app = $ctx->getApp();
        $this->context = $ctx;
    }

    /**
     * @return \Aether\App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return \Aether\Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
 