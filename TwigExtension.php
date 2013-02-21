<?php

//  +--------------------------------------------------+
//  | Copyright (c) Ad ticket GmbH                     |
//  | All rights reserved.                             |
//  +--------------------------------------------------+
//  | This source code is protected by international   |
//  | copyright law and may not be distributed without |
//  | written permission by                            |
//  |   AD ticket GmbH                                 |
//  |   Kaiserstraße 69                                |
//  |   D-60329 Frankfurt am Main                      |
//  |                                                  |
//  |   phone: +49 (0)69 407 662 0                     |
//  |   fax:   +49 (0)69 407 662 50                    |
//  |   mail:  info@adticket.de                        |
//  |   web:   www.ADticket.de                         |
//  +--------------------------------------------------+

/**
 * @author Markus Tacker <m@coderbyheart.de>
 * @package AdTicket:Elvis:CoreBundle
 * @category Twig
 */

namespace Adticket\TabBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Eine Twig-Extension um Aktions-Links zu rendern
 *
 * @author Markus Tacker <m@coderbyheart.de>
 * @package AdTicket:Elvis:CoreBundle
 * @category Twig
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $after;

    /**
     * @var string
     */
    private $before;

    /**
     * @param Helper
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'adt_tabs_start' => new \Twig_Function_Method($this, 'startTabs', array('is_safe' => array('html'))),
            'adt_tabs_end' => new \Twig_Function_Method($this, 'endTabs', array('is_safe' => array('html'))),
            'adt_tab' => new \Twig_Function_Method($this, 'tab', array('is_safe' => array('html'))),
        );
    }

    /**
     * Beginnt mit einem Tab
     *
     * @return string
     */
    public function startTabs($before = null, $after = null)
    {
        if ($before === null) $before = '<div class="tabs"><ul>';
        if ($after === null) $after = '</ul></div>';

        // Inhalte aus vorigen Aufrufen rendern

        if ($this->started) {
            return '<div class="error">Nested tabs not supported.</div>';
        }
        $this->started = true;
        $this->before = $before;
        $this->after = $after;
        ob_start();

        return '';
    }

    /**
     * Beendet ein Tab
     *
     * @return string
     */
     public function endTabs()
     {
        $data = ob_get_contents();
        ob_end_clean();
        $this->started = false;
        if (trim($data) === '') return '<!-- Tabs not rendered. -->';
        $before = $this->before;
        $after = $this->after;

        return "\n" . '<!-- Tab start -->' . "\n" . $before . $data . $after . "\n" . '<!-- Tab end -->';
    }

    /**
     * Rendert einen Tab
     *
     * @return string
     */
    public function tab($route, $routeParams, $label, $template = null)
    {
        if (is_object($routeParams)) {
            if (method_exists($routeParams, 'getId')) {
                $routeParams = array('id' => $routeParams->getId());
            } else {
                // TODO: throwing an exception
                throw new \Exception('Invalid route params given, expected object with getId()-method or array.');
            }
        }
        if ($template === null) $template = '<li><a class="{class}" href="{uri}">{label}</a></li>';
        $tParams = array(
            'class' => $this->container->get('request')->get('_route') == $route ? 'active' : '',
            'label' => $label,
            'uri' => $this->container->get('router')->generate($route, $routeParams),
        );
        foreach($tParams as $k => $v)
            $template = str_replace('{' . $k . '}', $v, $template);

        return $template;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adt_tabs_twig_extension';
    }
}
