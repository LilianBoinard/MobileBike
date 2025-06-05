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

/* pages/login.html.twig */
class __TwigTemplate_f9c8d041874ba194113ceb03c9981a4d extends Template
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

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "layout/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("layout/base.html.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "Connexion";
        yield from [];
    }

    // line 5
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 6
        yield "    <main class=\"login-page\">
        <h1>Vous connecter à votre espace membre</h1>
        <div class=\"login-container\">
            <form class=\"login-form\" action=\"login.php\" method=\"POST\">
                <h2>Connexion</h2>
                <div class=\"form-group\">
                    <label for=\"username\">Utilisateur</label>
                    <input type=\"text\" id=\"username\" name=\"username\" required />
                </div>
                <div class=\"form-group\">
                    <label for=\"password\">Mot de passe</label>
                    <input type=\"password\" id=\"password\" name=\"password\" required />
                </div>
                <button type=\"submit\">Se connecter</button>
            </form>
        </div>
    </main>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/login.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  70 => 6,  63 => 5,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'layout/base.html.twig' %}

{% block title %}Connexion{% endblock %}

{% block content %}
    <main class=\"login-page\">
        <h1>Vous connecter à votre espace membre</h1>
        <div class=\"login-container\">
            <form class=\"login-form\" action=\"login.php\" method=\"POST\">
                <h2>Connexion</h2>
                <div class=\"form-group\">
                    <label for=\"username\">Utilisateur</label>
                    <input type=\"text\" id=\"username\" name=\"username\" required />
                </div>
                <div class=\"form-group\">
                    <label for=\"password\">Mot de passe</label>
                    <input type=\"password\" id=\"password\" name=\"password\" required />
                </div>
                <button type=\"submit\">Se connecter</button>
            </form>
        </div>
    </main>
{% endblock %}", "pages/login.html.twig", "C:\\Users\\Lilian\\PhpstormProjects\\MobileBike\\src\\App\\View\\pages\\login.html.twig");
    }
}
