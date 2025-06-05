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

/* pages/products.html.twig */
class __TwigTemplate_2fc511dbbee2290cf82358d328178049 extends Template
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
        yield "Nos Velos";
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
        yield "    <main class=\"content\">

        <div class=\"banner\">
            <img src=\"./assets/images/page-content/velos_banner.jpg\" alt=\"Catalogue de vélos\" class=\"banner-image\">
            <div class=\"banner-title\">Notre Catalogue de Vélos</div>
        </div>

        <div class=\"catalogue\">
            <div class=\"catalogue-head\">
                <p class=\"catalogue-head-text\">Affichage de 1-32 sur 6 résultats</p>
                <div class=\"catalogue-head-tools\">
                    <div class=\"catalogue-head-tool\">
                        <p class=\"catalogue-filter-button\">Filtrer</p>
                        <i class=\"fa-solid fa-filter\"></i>
                    </div>
                    <div class=\"catalogue-head-tool\">
                        <p class=\"catalogue-sort-button\">Trier</p>
                        <i class=\"fa-solid fa-sliders\"></i>
                    </div>
                </div>

            </div>
            <div class=\"catalogue-items-container\">
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/700-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">700</h3>
                        <p class=\"card-price\">à partir de 3 950,00€</p>
                        <p class=\"card-description\">Rapide, efficace. Avec une position basse du corp le Catrike 700
                            génère puissance et vitesse</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/5.5.9-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">5,5,9</h3>
                        <p class=\"card-price\">à partir de 3 550,00€</p>
                        <p class=\"card-description\">Performance, longue distance, il a tout pour lui. Ce trike
                            confortable vous emmenera loin sans que vous ne vous rendiez compte des kilomètres.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/Velomobile-ALFA-7.png\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">ALFA-7</h3>
                        <p class=\"card-price\">à partir de 10 900,00€</p>
                        <p class=\"card-description\">Sportif utilisable tous les jours</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/1-Alpha-9-e1655297877502.jpeg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">ALPHA 9</h3>
                        <p class=\"card-price\">à partir de 11 900,00€</p>
                        <p class=\"card-description\">Un « visage » plus harmonieusement modelé et une meilleure
                            visibilité vers l’avant.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/EC-Velo.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">EC-Vélo</h3>
                        <p class=\"card-price\">à partir de 6 400,00€</p>
                        <p class=\"card-description\">Vélomobile pratique et polyvalent</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/DF-XL-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">DF XL</h3>
                        <p class=\"card-price\">à partir de 9 800,00€</p>
                        <p class=\"card-description\">Modèle conçu par Daniel FENN et Ymte SIJBRANDIJ en 2013.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/dumont-3-1.png\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">DUMONT</h3>
                        <p class=\"card-price\">à partir de 4 950,00€</p>
                        <p class=\"card-description\">Le Dumont allie l’équilibre parfait entre performances et
                            confort.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/DSCF8379-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">BÜLK</h3>
                        <p class=\"card-price\">à partir de 9 750,00€</p>
                        <p class=\"card-description\">Met en œuvre une nouvelle utilisation de l’espace et un nouveau
                            concept aérodynamique.</p>
                    </div>
                </div>
            </div>
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
        return "pages/products.html.twig";
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

{% block title %}Nos Velos{% endblock %}

{% block content %}
    <main class=\"content\">

        <div class=\"banner\">
            <img src=\"./assets/images/page-content/velos_banner.jpg\" alt=\"Catalogue de vélos\" class=\"banner-image\">
            <div class=\"banner-title\">Notre Catalogue de Vélos</div>
        </div>

        <div class=\"catalogue\">
            <div class=\"catalogue-head\">
                <p class=\"catalogue-head-text\">Affichage de 1-32 sur 6 résultats</p>
                <div class=\"catalogue-head-tools\">
                    <div class=\"catalogue-head-tool\">
                        <p class=\"catalogue-filter-button\">Filtrer</p>
                        <i class=\"fa-solid fa-filter\"></i>
                    </div>
                    <div class=\"catalogue-head-tool\">
                        <p class=\"catalogue-sort-button\">Trier</p>
                        <i class=\"fa-solid fa-sliders\"></i>
                    </div>
                </div>

            </div>
            <div class=\"catalogue-items-container\">
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/700-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">700</h3>
                        <p class=\"card-price\">à partir de 3 950,00€</p>
                        <p class=\"card-description\">Rapide, efficace. Avec une position basse du corp le Catrike 700
                            génère puissance et vitesse</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/5.5.9-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">5,5,9</h3>
                        <p class=\"card-price\">à partir de 3 550,00€</p>
                        <p class=\"card-description\">Performance, longue distance, il a tout pour lui. Ce trike
                            confortable vous emmenera loin sans que vous ne vous rendiez compte des kilomètres.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/Velomobile-ALFA-7.png\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">ALFA-7</h3>
                        <p class=\"card-price\">à partir de 10 900,00€</p>
                        <p class=\"card-description\">Sportif utilisable tous les jours</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/1-Alpha-9-e1655297877502.jpeg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">ALPHA 9</h3>
                        <p class=\"card-price\">à partir de 11 900,00€</p>
                        <p class=\"card-description\">Un « visage » plus harmonieusement modelé et une meilleure
                            visibilité vers l’avant.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/EC-Velo.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">EC-Vélo</h3>
                        <p class=\"card-price\">à partir de 6 400,00€</p>
                        <p class=\"card-description\">Vélomobile pratique et polyvalent</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/DF-XL-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">DF XL</h3>
                        <p class=\"card-price\">à partir de 9 800,00€</p>
                        <p class=\"card-description\">Modèle conçu par Daniel FENN et Ymte SIJBRANDIJ en 2013.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/dumont-3-1.png\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">DUMONT</h3>
                        <p class=\"card-price\">à partir de 4 950,00€</p>
                        <p class=\"card-description\">Le Dumont allie l’équilibre parfait entre performances et
                            confort.</p>
                    </div>
                </div>
                <div class=\"card\">
                    <div class=\"card-header\">
                        <img src=\"./assets/images/velos/DSCF8379-1.jpg\" alt=\"\"></img>
                    </div>
                    <div class=\"card-content\">
                        <h3 class=\"card-model-title\">BÜLK</h3>
                        <p class=\"card-price\">à partir de 9 750,00€</p>
                        <p class=\"card-description\">Met en œuvre une nouvelle utilisation de l’espace et un nouveau
                            concept aérodynamique.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
{% endblock %}", "pages/products.html.twig", "C:\\Users\\Lilian\\PhpstormProjects\\MobileBike\\src\\App\\View\\pages\\products.html.twig");
    }
}
