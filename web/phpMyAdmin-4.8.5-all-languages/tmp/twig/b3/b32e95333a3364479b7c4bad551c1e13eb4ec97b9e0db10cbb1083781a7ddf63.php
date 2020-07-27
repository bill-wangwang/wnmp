<?php

/* config/form_display/form_bottom.twig */
class __TwigTemplate_ce45b8248597cdb7ea4f02b2750b54cbfd200551fb4c0677721aa796a28d9b9c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "</form>
";
    }

    public function getTemplateName()
    {
        return "config/form_display/form_bottom.twig";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "config/form_display/form_bottom.twig", "C:\\Users\\bill\\Desktop\\wnmp\\web\\phpMyAdmin-4.8.5-all-languages\\templates\\config\\form_display\\form_bottom.twig");
    }
}
