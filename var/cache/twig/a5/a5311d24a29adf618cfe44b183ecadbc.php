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

/* pages/contact.html.twig */
class __TwigTemplate_dfeecf01cb3781f02cea8a7b4936a9f6 extends Template
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
        yield "Contact";
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
        yield "    <main class=\"contact\">
        <div class=\"contact-wrapper\">
            <h1>Écoute & disponibilité</h1>

            <p class=\"intro\">Cycles 3V FENIOUX<br>
                Pour profiter au mieux de notre disponibilité, contactez-nous par téléphone ou email et informez-nous de
                votre venue au magasin.</p>

            <div class=\"container\">
                <section class=\"left-column\">
                    <h2>Venez nous rendre visite !</h2>
                    <div class=\"contact-info\">
                        19 rue Paul Cretegny -<br>
                        ZI LES LOGES, 85400 CHASNAIS
                    </div>

                    <h2>Téléphone</h2>
                    <div class=\"contact-info\">
                        +33(0) 2 51 28 23 41<br>
                        +33(0) 6 14 48 37 35
                    </div>

                    <h2>Horaires</h2>
                    <div class=\"hours\">
                        <p>Mardi au Vendredi :<br> 9h-12h & 14h-17h</p>
                        <p>Le samedi : De 9h à 12h<br> et de 14h à 16h<br> (sauf Rendez-vous)</p>
                    </div>
                </section>

                <section class=\"right-column\">
                    <div class=\"contact-form\">
                        <h2 class=\"form-title\">Formulaire de contact</h2>
                        <form>
                            <div class=\"form-row\">
                                <div class=\"form-group\">
                                    <input type=\"text\" id=\"nom\" name=\"nom\" placeholder=\"Nom\" required>
                                </div>
                                <div class=\"form-group\">
                                    <input type=\"email\" id=\"email\" name=\"email\" placeholder=\"Email\" required>
                                </div>
                            </div>

                            <div class=\"form-row\">
                                <div class=\"form-group\">
                                    <input type=\"text\" id=\"prenom\" name=\"prenom\" placeholder=\"Prénom\" required>
                                </div>
                                <div class=\"form-group\">
                                    <input type=\"tel\" id=\"telephone\" name=\"telephone\" placeholder=\"Téléphone\">
                                </div>
                            </div>

                            <div class=\"form-group\">
                                <input type=\"text\" id=\"sujet\" name=\"sujet\" placeholder=\"Votre Sujet\" required>
                            </div>

                            <div class=\"form-group\">
                                <textarea id=\"message\" name=\"message\" placeholder=\"Votre message\" required></textarea>
                            </div>

                            <div class=\"sub-disc\">
                                <button type=\"submit\" class=\"contact-button\">Envoyer</button>
                                <p class=\"disclaimer\">En cliquant sur \"Envoyer\" vous acceptez que vos données soient
                                    traitées afin de répondre à votre demande.</p>
                            </div>
                        </form>
                    </div>
                </section>
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
        return "pages/contact.html.twig";
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

{% block title %}Contact{% endblock %}

{% block content %}
    <main class=\"contact\">
        <div class=\"contact-wrapper\">
            <h1>Écoute & disponibilité</h1>

            <p class=\"intro\">Cycles 3V FENIOUX<br>
                Pour profiter au mieux de notre disponibilité, contactez-nous par téléphone ou email et informez-nous de
                votre venue au magasin.</p>

            <div class=\"container\">
                <section class=\"left-column\">
                    <h2>Venez nous rendre visite !</h2>
                    <div class=\"contact-info\">
                        19 rue Paul Cretegny -<br>
                        ZI LES LOGES, 85400 CHASNAIS
                    </div>

                    <h2>Téléphone</h2>
                    <div class=\"contact-info\">
                        +33(0) 2 51 28 23 41<br>
                        +33(0) 6 14 48 37 35
                    </div>

                    <h2>Horaires</h2>
                    <div class=\"hours\">
                        <p>Mardi au Vendredi :<br> 9h-12h & 14h-17h</p>
                        <p>Le samedi : De 9h à 12h<br> et de 14h à 16h<br> (sauf Rendez-vous)</p>
                    </div>
                </section>

                <section class=\"right-column\">
                    <div class=\"contact-form\">
                        <h2 class=\"form-title\">Formulaire de contact</h2>
                        <form>
                            <div class=\"form-row\">
                                <div class=\"form-group\">
                                    <input type=\"text\" id=\"nom\" name=\"nom\" placeholder=\"Nom\" required>
                                </div>
                                <div class=\"form-group\">
                                    <input type=\"email\" id=\"email\" name=\"email\" placeholder=\"Email\" required>
                                </div>
                            </div>

                            <div class=\"form-row\">
                                <div class=\"form-group\">
                                    <input type=\"text\" id=\"prenom\" name=\"prenom\" placeholder=\"Prénom\" required>
                                </div>
                                <div class=\"form-group\">
                                    <input type=\"tel\" id=\"telephone\" name=\"telephone\" placeholder=\"Téléphone\">
                                </div>
                            </div>

                            <div class=\"form-group\">
                                <input type=\"text\" id=\"sujet\" name=\"sujet\" placeholder=\"Votre Sujet\" required>
                            </div>

                            <div class=\"form-group\">
                                <textarea id=\"message\" name=\"message\" placeholder=\"Votre message\" required></textarea>
                            </div>

                            <div class=\"sub-disc\">
                                <button type=\"submit\" class=\"contact-button\">Envoyer</button>
                                <p class=\"disclaimer\">En cliquant sur \"Envoyer\" vous acceptez que vos données soient
                                    traitées afin de répondre à votre demande.</p>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>
{% endblock %}", "pages/contact.html.twig", "C:\\Users\\Lilian\\PhpstormProjects\\MobileBike\\src\\App\\View\\pages\\contact.html.twig");
    }
}
