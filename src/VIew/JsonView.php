<?php namespace Aether\View;

use Aether\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * User: mcfog
 * Date: 14-8-23
 */
class JsonView extends AbstractView
{
    protected $encodingOptions;

    public function __construct(App $app, Response $response)
    {
        parent::__construct($app, $response);

        $this->encodingOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
    }

    public function render($data)
    {

        $data = @json_encode($data, $this->encodingOptions);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException($this->jsonLastErrorMsg());
        }

        if (
            !$this->response->headers->has('Content-Type')
            || 'text/javascript' === $this->response->headers->get('Content-Type')
        ) {
            $this->response->headers->set('Content-Type', 'application/json');
        }

        $this->response->setContent($data);
    }

    /**
     * @param int $encodingOptions
     * @return $this
     */
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = $encodingOptions;

        return $this;
    }

    private function jsonLastErrorMsg()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded.';

            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch.';

            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found.';

            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON.';

            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded.';

            default:
                return 'Unknown error.';
        }
    }
}
 