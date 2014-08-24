<?php namespace Aether\View;

/**
 * User: mcfog
 * Date: 14-8-24
 */
class TemplateView extends AbstractView
{
    const D_TPL_EXTENSION = 'aether.template.extension';
    const D_TPL_PATH = 'aether.template.path';
    protected $template = null;

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function render($data)
    {
        $content = $this->renderTemplate($data, $this->template);
        $this->response->setContent($content);
    }

    /**
     * @param $data
     * @param $template
     * @throws \Exception
     * @return string
     */
    protected function renderTemplate($data, $template)
    {
        extract($data);

        $tplpath = sprintf(
            '%s%s%s',
            $this->app->get(self::D_TPL_PATH),
            $template,
            $this->app->get(self::D_TPL_EXTENSION)
        );

        ob_start();

        /** @noinspection PhpIncludeInspection */
        if(false === (include $tplpath)) {
            ob_end_clean();
            throw new \Exception('template file not exist: ' . $tplpath);
        }

        $content = ob_get_clean();

        return $content;
    }
}
