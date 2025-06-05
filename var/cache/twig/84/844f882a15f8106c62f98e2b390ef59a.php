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

/* pages/about.html.twig */
class __TwigTemplate_7f5fd2cdc918cd9447cb97bd33497ab5 extends Template
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
        yield "Qui sommes-nous ?";
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
        yield "    <main class=\"about-page\">
        <img class=\"about-background\" src=\"/assets/images/page-content/Market-img.jpg\" alt=\"\">
        <h1 class=\"about-title\">Qui Sommes-Nous ?</h1>

        <div class=\"hero-section\">
            <h1 class=\"hero-section-title\">Notre Hisoire</h1>
            <p>L'équipe des Cycles JV & FENIOUX vous accompagne et vous conseille dans le choix du meilleur équipement.
                Fabricant et distributeur de vélomobiles, nous vous proposons aussi des modèles de vélocouchés, trikes
                et
                vélos spéciaux ainsi que des services complémentaires. Comptez sur notre</p>

            <div class=\"hero-section-cta\">
                <button  onclick=\"window.location.href='./services'\" class=\"cta-button\">Découvrez nos services</button>
            </div>
        </div>
        <img class=\"hero-section-chevron-icon\" src=\"/assets/images/icon/Chevrons-down.png\" alt=\"\">
        <img class=\"hero-section-background\" src=\"/assets/images/page-content/bg-img.png\" alt=\"\">

        <div class=\"team\">
            <img class=\"team-image\" src=\"/assets/images/page-content/team-image.png\" alt=\"\">

            <h3 class=\"team-section-title\">Des experts passionnés</h3>
            <p class=\"team-section-description\">Cycles JV Fenioux c'est avant tout une équipe dynamique et conviviale qui
                vous accueille dans une ambiance familiale.</p>

            <div class=\"team-cards-container\">
                <div class=\"team-card\">
                    <img src=\"/assets/images/page-content/Team-JV.jpg\" alt=\"Photo de Joël Vincent\">
                    <div class=\"info-box style-1\">
                        <h4>M. Joël VINCENT</h4>
                        <p>« Ancien coureur cycliste aux nombreuses victoires »</p>
                    </div>
                </div>

                <div class=\"team-card\">
                    <img src=\"/assets/images/page-content/Team-AMG.jpg\" alt=\"Photo de Joël Vincent\">
                    <div class=\"info-box style-2\">
                        <h4>Anne-Marie GERVAISEAU</h4>
                        <p>« Elle vous accueille, vous répond au téléphone, etc : notre secrétaire ! »</p>
                    </div>
                </div>

                <div class=\"team-card\">
                    <img src=\"/assets/images/page-content/Team-CF.jpg\" alt=\"Photo de Joël Vincent\">
                    <div class=\"info-box style-3\">
                        <h4>Christian FENIOUX</h4>
                        <p>« Pistard ayant un palmarès comptant de nombreux records mondiaux »</p>
                    </div>
                </div>
            </div>
        </div>

        <div class=\"partnership\">
            <h3 class=\"partnership-section-title\">Les produits & marques</h3>
            <img class=\"partnership-section-image\" src=\"/assets/images/page-content/partnership.png\" alt=\"\">
            <p class=\"partnership-section-desc\">
                Cycles JV & FENIOUX vous présente ses gammes variées et travaille en collaboration avec les meilleurs du
                marché. Nous avons également pour habitude de collaborer avec les assurances et les experts lorsque cela est
                nécessaire.
            </p>
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
        return "pages/about.html.twig";
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

{% block title %}Qui sommes-nous ?{% endblock %}

{% block content %}
    <main class=\"about-page\">
        <img class=\"about-background\" src=\"/assets/images/page-content/Market-img.jpg\" alt=\"\">
        <h1 class=\"about-title\">Qui Sommes-Nous ?</h1>

        <div class=\"hero-section\">
            <h1 class=\"hero-section-title\">Notre Hisoire</h1>
            <p>L'équipe des Cycles JV & FENIOUX vous accompagne et vous conseille dans le choix du meilleur équipement.
                Fabricant et distributeur de vélomobiles, nous vous proposons aussi des modèles de vélocouchés, trikes
                et
                vélos spéciaux ainsi que des services complémentaires. Comptez sur notre</p>

            <div class=\"hero-section-cta\">
                <button  onclick=\"window.location.href='./services'\" class=\"cta-button\">Découvrez nos services</button>
            </div>
        </div>
        <img class=\"hero-section-chevron-icon\" src=\"/assets/images/icon/Chevrons-down.png\" alt=\"\">
        <img class=\"hero-section-background\" src=\"/assets/images/page-content/bg-img.png\" alt=\"\">

        <div class=\"team\">
            <img class=\"team-image\" src=\"/assets/images/page-content/team-image.png\" alt=\"\">

            <h3 class=\"team-section-title\">Des experts passionnés</h3>
            <p class=\"team-section-description\">Cycles JV Fenioux c'est avant tout une équipe dynamique et conviviale qui
                vous accueille dans une ambiance familiale.</p>

            <div class=\"team-cards-container\">
                <div class=\"team-card\">
                    <img src=\"/assets/images/page-content/Team-JV.jpg\" alt=\"Photo de Joël Vincent\">
                    <div class=\"info-box style-1\">
                        <h4>M. Joël VINCENT</h4>
                        <p>« Ancien coureur cycliste aux nombreuses victoires »</p>
                    </div>
                </div>

                <div class=\"team-card\">
                    <img src=\"/assets/images/page-content/Team-AMG.jpg\" alt=\"Photo de Joël Vincent\">
                    <div class=\"info-box style-2\">
                        <h4>Anne-Marie GERVAISEAU</h4>
                        <p>« Elle vous accueille, vous répond au téléphone, etc : notre secrétaire ! »</p>
                    </div>
                </div>

                <div class=\"team-card\">
                    <img src=\"/assets/images/page-content/Team-CF.jpg\" alt=\"Photo de Joël Vincent\">
                    <div class=\"info-box style-3\">
                        <h4>Christian FENIOUX</h4>
                        <p>« Pistard ayant un palmarès comptant de nombreux records mondiaux »</p>
                    </div>
                </div>
            </div>
        </div>

        <div class=\"partnership\">
            <h3 class=\"partnership-section-title\">Les produits & marques</h3>
            <img class=\"partnership-section-image\" src=\"/assets/images/page-content/partnership.png\" alt=\"\">
            <p class=\"partnership-section-desc\">
                Cycles JV & FENIOUX vous présente ses gammes variées et travaille en collaboration avec les meilleurs du
                marché. Nous avons également pour habitude de collaborer avec les assurances et les experts lorsque cela est
                nécessaire.
            </p>
        </div>
    </main>

{% endblock %}", "pages/about.html.twig", "C:\\Users\\Lilian\\PhpstormProjects\\MobileBike\\src\\App\\View\\pages\\about.html.twig");
    }
}
