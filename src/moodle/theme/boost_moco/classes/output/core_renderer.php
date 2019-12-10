<?php

namespace theme_boost_moco\output;

use html_writer;
use custom_menu_item;
use curl;
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_boost_moco
 * @copyright  2019 TMX
 */

class core_renderer extends \theme_boost\output\core_renderer
{

    /** @var custom_menu_item language The language menu if created */
    protected $language = null;

    public function __construct(\moodle_page $page, $target)
    {
        global $SESSION, $CFG, $PAGE, $SITE;
        parent::__construct($page, $target);
        $this->session = $SESSION;
        $this->cfg = $CFG;
        $this->page = $PAGE;
        $this->site = $SITE;
    }


    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100)
    {
        $compact_logo_url = parent::get_compact_logo_url(null, 70);
        if ($compact_logo_url === false) {
            $compact_logo_url = $this->cfg->wwwroot . '/theme/boost_moco/pix/logo-blue.png';
        }
        return $compact_logo_url;
    }

    /**
     * Get class for current menu section
     *
     * @return string
     */
    public function getCurrentSection($sectionsItems)
    {
        foreach ($sectionsItems as $item) {
            if (in_array($item->url[0], array($this->page->url))) {
                return ' show';
            }
        }
        return '';
    }

    /**
     * Left TOP menu in list-group
     *
     * @return html
     */
    public function navHomePage()
    {
        $return = html_writer::start_tag('div', array('id' => 'topMenu'));
        $uriParts = explode('?', $this->page->url, 2);
        $homePageUrl = $this->cfg->wwwroot . '/my/index.php';
        $addClass = (stripos($homePageUrl, $uriParts[0]) === false ? '' : ' active');
        $return .= html_writer::start_tag('a', array('class' => 'list-group-item list-group-item-action' . $addClass, 'data-parent' => '#mocoMenu', 'href' => $homePageUrl));
        $return .= html_writer::start_tag('div', array('class' => 'm-l-0'));
        $return .= html_writer::start_tag('div', array('class' => 'media'));
        $return .= html_writer::start_tag('span', array('class' => 'media-left'));
        $return .= html_writer::tag('i', '', array('class' => 'icon fa fa-tachometer fa-fw'));
        $return .= html_writer::end_tag('span');
        $return .= html_writer::span(get_string('myhome'), 'sidebar-text');
        $return .= html_writer::end_tag('div');
        $return .= html_writer::end_tag('div');
        return $return . html_writer::end_tag('a');
    }

    /**
     * Left MOCO menu in list-group
     *
     * @return html
     */
    public function moco_dlmenu()
    {
        $service_params = array(
            'usertoken=' . $this->session->token,
            'moco_rest_key='.$this->cfg->moco_rest_key
        );
        if (!empty($this->session->lang)) {
                array_push($service_params, 'lang=' . $this->session->lang);
        }
        $service_params = implode("&", $service_params);
        $service_url = $this->cfg->mocoroot.'/index.php/api/gui/GetDlMenuForMoodle?' . $service_params;
        $curl = new curl;
        $curl_response = $curl->get($service_url);
        if ($curl_response === false) {
            return '';
        }
        $decoded_response = json_decode($curl_response);
        if ($decoded_response->code == '200') {
            $dlmenu = $decoded_response->data;
        } else {
            return '';
        }
        $return = html_writer::start_tag('div', array('class' => 'list-group m-t-1', 'id' => 'mocoMenu'));
        $parent = 0;
        foreach ($dlmenu as $section) {
            if (count((array)($section->items[0]->sectionItems)) > 1) {
                $return .= html_writer::start_tag('a', array('class' => 'list-group-item list-group-item-action', 'data-toggle' => 'collapse', 'data-parent' => '#mocoMenu', 'href' => '#sub'.$parent));
                $return .= html_writer::start_tag('div', array('class' => 'm-l-0'));
                $return .= html_writer::start_tag('div', array('class' => 'media align-items-lg-center'));
                $return .= html_writer::start_tag('span', array('class' => 'media-left'));
                if ($section->icon !== 'default') {
                    $return .= html_writer::tag('i', '', array('class' => 'icon sidebar-icon tmx-'.$section->icon));
                } else {
                    $return .= html_writer::img($section->iconSrc, '', array('class' => 'sidebar-icon sidebar-iconimg'));
                }
                $return .= html_writer::end_tag('span');
                $return .= html_writer::span($section->label, 'sidebar-text');
                $return .= html_writer::end_tag('div');
                $return .= html_writer::end_tag('div');
                $return .= html_writer::end_tag('a');

                $showCollapse = $this->getCurrentSection($section->items[0]->sectionItems);
                $return .= html_writer::start_tag('div', array('class' => 'collapse' . $showCollapse, 'id' => 'sub'.$parent));

                foreach ($section->items[0]->sectionItems as $item) {
                    $uriParts = explode('?', $this->page->url, 2);
                    $section->url = substr($section->url, -1) === '/' ? $section->url . 'index.php' : $section->url;
                    $addClass = (stripos($item->url[0], $uriParts[0]) === false ? '' : ' active');
                    $return .= html_writer::start_tag('a', array('class' => 'list-group-item list-group-item-action' . $addClass, 'href' => $item->url[0]));
                    $return .= html_writer::start_tag('div', array('class' => 'm-l-1'));
                    $return .= html_writer::start_tag('div', array('class' => 'media'));
                    $return .= html_writer::span($item->label, 'sidebar-text');
                    $return .= html_writer::end_tag('div');
                    $return .= html_writer::end_tag('div');
                    $return .= html_writer::end_tag('a');
                }
                $return .= html_writer::end_tag('div');
                $parent++;
            } else {
                $uriParts = explode('?', $this->page->url, 2);
                $section->url = substr($section->url, -1) === '/' ? $section->url . 'index.php' : $section->url;
                $addClass = (stripos($section->url, $uriParts[0]) === false ? '' : ' active');
                $return .= html_writer::start_tag('a', array('class' => 'list-group-item list-group-item-action' . $addClass, 'data-parent' => '#mocoMenu', 'href' => $section->url));
                $return .= html_writer::start_tag('div', array('class' => 'm-l-0'));
                $return .= html_writer::start_tag('div', array('class' => 'media'));
                $return .= html_writer::start_tag('span', array('class' => 'media-left'));
                if ($section->icon !== 'default') {
                    $return .= html_writer::tag('i', '', array('class' => 'icon sidebar-icon tmx-'.$section->icon));
                } else {
                    $return .= html_writer::img($section->iconSrc, '', array('class' => 'sidebar-icon sidebar-iconimg'));
                }
                $return .= html_writer::end_tag('span');
                $return .= html_writer::span($section->label, 'sidebar-text');
                $return .= html_writer::end_tag('div');
                $return .= html_writer::end_tag('div');
                $return .= html_writer::end_tag('a');
            }
        }
        return $return . html_writer::end_tag('div');
    }


    /**
     * Get GetMenuForMoodle in REST
     *
     * @return array
     */
    private function get_moco_menu()
    {
        $menu = array();
        if (!empty($this->session->token)) {
            $service_url = $this->cfg->mocoroot.'/index.php/api/gui/GetMenuForMoodle?usertoken='.$this->session->token.'&moco_rest_key='.$this->cfg->moco_rest_key.'&lang='.current_language();
            $curl = new curl;
            $curl_response = $curl->get($service_url);
            if ($curl_response === false) {
                $menu = new stdClass();
                $menu->helpItems = array();
                $menu->systemModules = array();
                $menu->roles = array();
                $menu->lang = array();
                $menu->fullname = array();
                $menu->userModules = array();
            }
            $decoded_response = json_decode($curl_response);
            if ($decoded_response->code == '200') {
                $menu = $decoded_response->data;
            }
        }
        return $menu;
    }


    /**
     * Get help menu
     *
     * @return html
     */
    public function moco_help_menu()
    {

        $menuitems = $this->get_moco_menu()->helpItems;

        $return = html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'settings dropdown'));
        $return .= html_writer::start_tag('a', array('role' => 'button', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'href' => '#'));
        $return .= html_writer::start_tag('i', array('class' => 'icon tmx-question-circle'));
        $return .= html_writer::end_tag('i');
        $return .= html_writer::end_tag('a');
        $return .= html_writer::start_tag('ul', array('role' => 'menu', 'class' => 'dropdown-menu dropdown-menu-right'));
        if (!is_array($menuitems)) {
            $menuitems = array();
        }
        foreach ($menuitems as $item) {
            $return .= html_writer::start_tag('li', array('role' => 'menuitem'));
            $return .= html_writer::start_tag('a', array('tabindex' => '-1', 'href' => $item->url));
            $return .= html_writer::span($item->name);
            $return .= html_writer::end_tag('a');
            $return .= html_writer::end_tag('li');
        }
        $return .= html_writer::end_tag('ul');
        return $return . html_writer::end_tag('li');
    }


    /**
     * Get MOCO modules menu for right dropdown
     *
     * @return html
     */
    public function moco_system_modules_menu()
    {

        $menuitems = $this->get_moco_menu()->systemModules;

        $return = html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'settings dropdown'));
        $return .= html_writer::start_tag('a', array('role' => 'button', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'href' => '#'));
        $return .= html_writer::start_tag('i', array('class' => 'icon tmx-gear'));
        $return .= html_writer::end_tag('i');
        $return .= html_writer::end_tag('a');
        $return .= html_writer::start_tag('ul', array('role' => 'menu', 'class' => 'dropdown-menu dropdown-menu-right'));
        $return .= html_writer::tag('li', get_string('administration', 'theme_moco'), array('class' => 'bold nav-header'));
        foreach ($menuitems as $item) {
            if ($item->url) {
                $return .= html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'dropdown-toggle'));
                $return .= html_writer::start_tag('a', array('tabindex' => '-1', 'href' => $this->cfg->mocoroot.'/index.php'.$item->url));
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            } else {
                $return .= html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'dropdown-toggle nav-header'));
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('li');
            }
        }
        $return .= html_writer::end_tag('ul');
        return $return . html_writer::end_tag('li');
    }


    /**
     * Get user menu
     *
     * @return html
     */
    public function moco_user_menu()
    {
        $menu = $this->get_moco_menu();
        $menuitems = $menu->roles;
        $langdata = $menu->lang;
        $username = $menu->fullname;
        if (empty($langdata) || is_null($langdata) ||!is_object($langdata)) {
            $langdata = new stdClass();
            $langdata->langItems = array();
            $langdata->label = '';
        }
        $return = html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'settings last dropdown', 'data-toggle' => "tooltip", 'data-placement' => "left", 'title' => $username));
        $return .= html_writer::start_tag('a', array('role' => 'button', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'href' => '#'));
        $return .= html_writer::start_tag('i', array('class' => 'icon tmx-user'));
        $return .= html_writer::end_tag('i');
        $return .= html_writer::end_tag('a');
        $return .= html_writer::start_tag('ul', array('role' => 'menu', 'class' => 'dropdown-menu dropdown-menu-right'));
        $return .= html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'dropdown-toggle'));
        $return .= html_writer::start_tag('a', array('tabindex' => '-1', 'href' => $this->cfg->mocoroot.'/index.php/usr/user/profile'));
        $return .= html_writer::span($username);
        $return .= html_writer::end_tag('a');
        $return .= html_writer::end_tag('li');
        $return .= html_writer::tag('li', '', array('class' => 'divider'));

        if (!is_null($langdata)) {
            $return .= html_writer::start_tag('li', array('class' => 'font-weight-bold nav-header'));
            $return .= $langdata->label;
            foreach ($langdata->langItems as $langItem) {
                $return .= html_writer::start_tag('li', array('mocoUrl' => $langItem->mocoUrl, 'class' => 'dropdown-toggle lang', 'moodleUrl' => $langItem->moodleUrl, 'lang' => $langItem->lang, 'role' => 'menuitem'));
                $return .= html_writer::start_tag('a', array('tabindex' => '-1', 'href' => 'javascript: void(0);'));
                $return .= (current_language() == $langItem->lang) ? (html_writer::tag('i', '', array('class' => 'fa fa-circle', 'style' => 'font-size:10px;'))) : '';
                $return .= $langItem->name;
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            }
            $return .= html_writer::end_tag('li');
            $return .= html_writer::tag('li', '', array('class' => 'divider'));
        }
        $return .= html_writer::tag('li', get_string('myroles', 'theme_moco'), array('class' => 'font-weight-bold nav-header'));
        foreach ($menuitems as $item) {
            $return .= html_writer::tag('li', $item, array('class' => 'nav-item'));
        }
        $return .= html_writer::tag('li', '', array('class' => 'divider'));
        $return .= html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'dropdown-toggle'));
        $return .= html_writer::start_tag('a', array('tabindex' => '-1', 'href' => $this->cfg->mocoroot.'/index.php/usr/login/logout'));
        $return .= html_writer::span(get_string('logout', 'theme_moco'));
        $return .= html_writer::end_tag('a');
        $return .= html_writer::end_tag('li');
        $return .= html_writer::end_tag('ul');
        return $return . html_writer::end_tag('li');
    }


    /**
     * Get MOCO modules menu
     *
     * @return html
     */
    public function moco_user_top_menu()
    {
        $menuitems = $this->get_moco_menu()->userModules;

        if (is_null($menuitems)) {
            $menuitems = [];
        }
        $return = html_writer::start_tag('ul', array('role' => 'menu', 'class' => 'nav navbar-nav pull-left moco-top-menu'));
        foreach ($menuitems as $item) {
            if (stripos($item->url, $this->cfg->wwwroot) !== false) {
                $return .= html_writer::start_tag('li', array('class' => 'title active'));
                $return .= html_writer::start_tag('a', array('href' => $item->url));
                $return .= html_writer::end_tag('i');
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            } elseif (stripos($item->url, 'http') !== false) { // simply link
                $return .= html_writer::start_tag('li', array('class' => 'title'));
                $return .= html_writer::start_tag('a', array('href' => $item->url));
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            } else {
                $return .= html_writer::start_tag('li', array('class' => 'title'));
                $return .= html_writer::start_tag('a', array('href' => $this->cfg->mocoroot.'/index.php'.$item->url));
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            }
        }
        return $return . html_writer::end_tag('ul');
    }


    /**
     * Get help menu
     *
     * @return html
     */
    public function moco_user_top_menu_mobile()
    {

        $menuitems = $this->get_moco_menu()->userModules;

        $return = html_writer::start_tag('li', array('role' => 'menuitem', 'class' => 'settings dropdown mobil-menu-modules'));
        $return .= html_writer::start_tag('a', array('role' => 'button', 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'href' => '#'));
        $return .= html_writer::start_tag('i', array('class' => 'icon fa fa-ellipsis-v'));
        $return .= html_writer::end_tag('i');
        $return .= html_writer::end_tag('a');
        $return .= html_writer::start_tag('ul', array('role' => 'menu', 'class' => 'dropdown-menu dropdown-menu-right'));
        if (!is_array($menuitems)) {
            $menuitems = array();
        }
        foreach ($menuitems as $item) {
            if (stripos($item->url, $this->cfg->wwwroot) !== false) {
                $return .= html_writer::start_tag('li', array('class' => 'title active'));
                $return .= html_writer::start_tag('a', array('href' => $item->url));
                $return .= html_writer::end_tag('i');
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            } elseif (stripos($item->url, 'http') !== false) { // simply link
                $return .= html_writer::start_tag('li', array('class' => 'title'));
                $return .= html_writer::start_tag('a', array('href' => $item->url));
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            } else {
                $return .= html_writer::start_tag('li', array('class' => 'title'));
                $return .= html_writer::start_tag('a', array('href' => $this->cfg->mocoroot.'/index.php'.$item->url));
                $return .= html_writer::span($item->name);
                $return .= html_writer::end_tag('a');
                $return .= html_writer::end_tag('li');
            }
        }
        $return .= html_writer::end_tag('ul');
        return $return . html_writer::end_tag('li');
    }

    /**
     * Get footer content
     *
     * @return html
     */
    public function footerContent()
    {
        return 'Copyright © '.date('Y').' Державне підприємство "ІНФОТЕХ"';
    }

    /**
     * Skip items in nav drawer
     *
     * @return array
     */
    public function customizeNavDrawer($nav)
    {
        $navOut = array();
        $navSkip = array('myhome', 'home', 'calendar', 'privatefiles', 'mycourses');
        foreach ($nav as $item) {
            if (in_array($item->key, $navSkip) || is_numeric($item->key)) {
                continue;
            }
            $navOut[] = $item;
        }
        return $navOut;
    }

}
