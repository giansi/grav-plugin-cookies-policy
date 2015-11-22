<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

class CookiesPolicyPlugin extends Plugin
{
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
        ]);
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * if enabled on this page, load the JS + CSS theme.
     */
    public function onTwigSiteVariables()
    {
        $type = strtolower($this->config->get('plugins.cookiespolicy.type'));
        if (empty($type)){
            throw new \InvalidArgumentException('The Cookie Policy type variable value must be defined. At the moment it is empty. If you are overriding the default configuration, please define the type variable too with a valid value.');
        }

        if (!preg_grep("/" . $type . "/i", array(
            "bar",
            "dialog",
        ))){
            throw new \InvalidArgumentException(sprintf('The Cookie Policy type variable value must be one of "bar" or "dialog". You gave "%s"', $type));
        }

        $this->grav['assets']->addJs('plugin://cookiespolicy/assets/js/cookiechoices.js');
        $this->grav['assets']->addCss('plugin://cookiespolicy/assets/css/cookiechoices_' . $type . '.css', -999);

        $twig = $this->grav['twig'];
        $twig->twig_vars['cookiespolicy_cookie_type'] = $type;
        $twig->twig_vars['cookiespolicy_url'] = $this->config->get('plugins.cookiespolicy.url');

        $twig->twig_vars['cookiespolicy_markup'] = $twig->twig->render('partials/cookiespolicy.html.twig', array(
            'cookiespolicy_cookie_type' => $twig->twig_vars['cookiespolicy_cookie_type'],
            'cookiespolicy_url' => $twig->twig_vars['cookiespolicy_url']
        ));
    }
}
