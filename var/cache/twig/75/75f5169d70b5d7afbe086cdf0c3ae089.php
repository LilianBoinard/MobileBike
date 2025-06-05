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

/* layout/footer.html.twig */
class __TwigTemplate_77fa48b44d07fbe703e013589f8ca5b9 extends Template
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
        yield "<footer>
    <div class=\"footer-container\">
        <div class=\"footer-question\">Des Questions ?</div>
        <button onclick=\"window.location.href='./contact'\" class=\"contact-button\">Contactez-nous !</button>
    </div>
    <div class=\"footer-copyright\">
        @2025 Mobile Bike
    </div>
</footer>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/footer.html.twig";
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
        return new Source("<footer>
    <div class=\"footer-container\">
        <div class=\"footer-question\">Des Questions ?</div>
        <button onclick=\"window.location.href='./contact'\" class=\"contact-button\">Contactez-nous !</button>
    </div>
    <div class=\"footer-copyright\">
        @2025 Mobile Bike
    </div>
</footer>", "layout/footer.html.twig", "C:\\Users\\Lilian\\PhpstormProjects\\MobileBike\\src\\App\\View\\layout\\footer.html.twig");
    }
}
