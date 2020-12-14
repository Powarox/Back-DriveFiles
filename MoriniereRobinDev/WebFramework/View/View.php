<?php

namespace MoriniereRobinDev\WebFramework\View;

abstract class View
{
    protected $parts;
    protected $router;
    protected $template;

    public function __construct($template, $router, $parts = array())
    {
        $this->template = $template;
        $this->router = $router;
        $this->parts = $parts;
    }

    // Méthode qui affiche le template html
    public function render()
    {
        $title = $this->getPart('title');
        $feedback = $this->getPart('feedback');
        $content = $this->getPart('content');
        $menu = $this->getPart('menu');

        ob_start();
        include($this->template);
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    public function setPart($key, $content)
    {
        $this->parts[$key] = $content;
    }

    public function getPart($key)
    {
        if (isset($this->parts[$key])) {
            return $this->parts[$key];
        } else {
            return null;
        }
    }
}
