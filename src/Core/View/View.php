<?php

namespace MobileBike\Core\View;

use Twig\Environment;

class View
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function twig(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }
}
