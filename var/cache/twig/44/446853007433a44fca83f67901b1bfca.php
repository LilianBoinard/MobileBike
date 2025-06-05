<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* layout/header.html.twig */
class __TwigTemplate_805943c51356410403a00bd52ca2ab3d extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<header>
    <nav class=\"menubar\">
        <a href=\"./\" class=\"menubar-logo\">
            <img src=\"./assets/images/LogoMobileBike.png\" alt=\"Mobile Bike Logo\">
        </a>
        <ul class=\"menubar-links\">
            <li><a href=\"./about\">Qui sommes-nous ?</a></li>
            <li><a href=\"./services\">Services</a></li>
            <li><a href=\"./products\">Nos Vélos</a></li>
            <li><a href=\"./contact\">Contact</a></li>
        </ul>
        <a href=\"./login\" class=\"menubar-user-icon\">
            <img src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+CiAgPHBhdGggZmlsbD0iIzU1NSIgZD0iTTEyLDRBNCw0IDAgMCwxIDE2LDhBNCw0IDAgMCwxIDEyLDEyQTQsNCAwIDAsMSA4LDhBNCw0IDAgMCwxIDEyLDRNMTIsMTRDMTYuNDIsMTQgMjAsMTUuNzkgMjAsMThWMjBINFYxOEM0LDE1Ljc5IDcuNTgsMTQgMTIsMTRaIiAvPgo8L3N2Zz4=\" alt=\"User Icon\">
        </a>
        <div class=\"menubar-burger-icon\">
            <svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">
                <rect y=\"4\" width=\"24\" height=\"2\" fill=\"black\"/>
                <rect y=\"11\" width=\"24\" height=\"2\" fill=\"black\"/>
                <rect y=\"18\" width=\"24\" height=\"2\" fill=\"black\"/>
            </svg>
        </div>
    </nav>
</header>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/header.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<header>
    <nav class=\"menubar\">
        <a href=\"./\" class=\"menubar-logo\">
            <img src=\"./assets/images/LogoMobileBike.png\" alt=\"Mobile Bike Logo\">
        </a>
        <ul class=\"menubar-links\">
            <li><a href=\"./about\">Qui sommes-nous ?</a></li>
            <li><a href=\"./services\">Services</a></li>
            <li><a href=\"./products\">Nos Vélos</a></li>
            <li><a href=\"./contact\">Contact</a></li>
        </ul>
        <a href=\"./login\" class=\"menubar-user-icon\">
            <img src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+CiAgPHBhdGggZmlsbD0iIzU1NSIgZD0iTTEyLDRBNCw0IDAgMCwxIDE2LDhBNCw0IDAgMCwxIDEyLDEyQTQsNCAwIDAsMSA4LDhBNCw0IDAgMCwxIDEyLDRNMTIsMTRDMTYuNDIsMTQgMjAsMTUuNzkgMjAsMThWMjBINFYxOEM0LDE1Ljc5IDcuNTgsMTQgMTIsMTRaIiAvPgo8L3N2Zz4=\" alt=\"User Icon\">
        </a>
        <div class=\"menubar-burger-icon\">
            <svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">
                <rect y=\"4\" width=\"24\" height=\"2\" fill=\"black\"/>
                <rect y=\"11\" width=\"24\" height=\"2\" fill=\"black\"/>
                <rect y=\"18\" width=\"24\" height=\"2\" fill=\"black\"/>
            </svg>
        </div>
    </nav>
</header>", "layout/header.html.twig", "C:\\Users\\Lilian\\PhpstormProjects\\MobileBike\\src\\App\\View\\layout\\header.html.twig");
    }
}
