<?php

use moco\ui\Html;
use gui\models\blogic\TopMenuRenderer;
use usr\models\entities\User;
use usr\models\blogic\PrivilegeManager;
use application\widgets\yiistrap\TbHtml;
use usr\models\entities\UserActiveSession;

?>
<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<?php
$menuData = null;
if (isset(Yii::app()->user->userId)) {
$menuData = TopMenuRenderer::getTopMenu();
$user = User::get(Yii::app()->user->userId);

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php
}else{
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class="login-bg">
<?php } ?>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="language" content="en"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="icon" href="<?php echo Yii::app()->theme->baseUrl; ?>/images/favicon.png" type="image/png"/>
  <!--[if IE]>
  <link rel="shortcut icon" href="<?php echo Yii::app()->theme->baseUrl; ?>/images/favicon.ico" type="image/vnd.microsoft.icon"/>
  <![endif]-->
    <?php echo Html::CSRFMetaTags(); ?>
  <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <?php
    Yii::app()->getClientScript()->registerCoreScript('placeholder');
    Yii::app()->bootstrap->register();
    $url = Yii::app()->theme->baseUrl . '/js';
    Yii::app()->getClientScript()->registerScriptFile($url . "/up.js");
    Yii::app()->getClientScript()->registerScriptFile($url . "/csrf.js");
    if (isset(Yii::app()->user->userId)) {
        $menuData = TopMenuRenderer::getTopMenu();
        Yii::app()->getClientScript()->registerScriptFile($menuData->lang->langScript);
    }
    $url = Yii::app()->theme->baseUrl . '/css';
    Yii::app()->getClientScript()->registerCustomCssFile($url . "/bootstrap-overrides.css", 'screen');
    Yii::app()->getClientScript()->registerCssFile($url . "/font-awesome.css", 'screen');
    Yii::app()->getClientScript()->registerCssFile($url . "/icomoon.css", 'screen');
    Yii::app()->getClientScript()->registerCssFile($url . "/tmx-icons.css", 'screen');
    Yii::app()->getClientScript()->registerCssFile($url . "/animate.css", 'screen');
    Yii::app()->getClientScript()->registerCustomCssFile($url . "/main.css", 'screen');
    Yii::app()->getClientScript()->registerCustomCssFile($url . "/mainBootstrap4.css", 'screen');
    ?>
</head>
<?php
echo CHtml::openTag('body', array('class' => (!isset($_COOKIE['menu']) ? 'drawer-open-left' : '') . ' drawer-ease ' . ($this->module ? $this->module->id . '-module' : '')));

$userItems = array();
$sysytemItems = array();
$title = array(
    'class' => '\\application\\widgets\\yiistrap\\TbNav',
    'htmlOptions' => array(
        'class' => 'nav navbar-nav pull-left'
    ),
    'items' => array(
        '<li class="title"></li>'
    ),
);

$menuTop = array();
$menuLeft = array();
if (isset(Yii::app()->user->userId) && $this->module && property_exists($this->module, 'menu')) {
    $menus = $this->module->menu;
    foreach ($menus as $key => $menu) {
        $submenu = array();
        $topmenu = array();
        $topmenu[] = array(
            'label' => $menu["label"],
            'class' => 'bold',
        );
        $isActive = false;
        foreach ($menu["items"][0]["sectionItems"] as $value) {
            $isActiveItem = false;
            $subUrl = $value["url"][0];
            if (preg_match('/' . str_replace('/','\/',Yii::app()->request->pathInfo) . '$/', $subUrl)) {
                $isActive = true;
                $isActiveItem = true;
            }
            $submenu[] = array(
                "label" => '<span class="sidebar-text">' . $value["label"] . '</span>',
                "url" => $subUrl,
                "active" => $isActiveItem,
            );
            $topmenu[] = array(
                "label" => $isActiveItem === true ? '<i class="moon-point"></i><span class="sidebar-text">' . $value["label"] . '</span>' : '<span class="sidebar-text">' . $value["label"] . '</span>',
                "url" => $subUrl,
                'class' => 'dropdown-toggle',
                "active" => $isActiveItem,
            );
        }
        $menuLeft[] = array(
            'label' => '<span class="sidebar-text">' . $menu["label"] . '</span>',
            'icon' => 'sidebar-icon tmx-' . $menu["icon"],
            'iconSrc' => $menu["iconSrc"],
            'items' => $submenu,
            'ulClass' => 'sidebar-child',
            'active' => $isActive,
            'url' => $menu["url"],
        );
        $menuTop[] = array(
            'icon' => 'icon tmx-' . $menu["icon"],
            'url' => '#',
            'class' => $key + 1 < count($menus) ? 'settings' : 'settings last',
            'linkOptions' => array(
                'role' => 'button',
                'caret' => false,
            ),
            'items' => $topmenu,
        );
    }
    $left = array(
        'class' => '\\application\\widgets\\yiistrap\\TbNav',
        'items' => $menuLeft,
        'encodeLabel' => false,
        'htmlOptions' => array(
            'class' => 'sidebar',
            'caret' => false,
        )
    );
}
$headerSectionMenu = array();
if (isset(Yii::app()->user->userId)) {
    if (isset($menuData->home)) {
        $title_items = array();
        foreach ($menuData->userModules as $userModule) {
            $title_items[] = '<li class="title ' . (($menuData->home->url == $userModule->url) ? "active" : "") . '"><a href="' . ((strripos($userModule->url,
                        'http') !== false) ? $userModule->url : Yii::app()->homeUrl . $userModule->url) . '">' . $userModule->name . '</a></li>';
        }
        $title = array(
            'class' => '\\application\\widgets\\yiistrap\\TbNav',
            'htmlOptions' => array(
                'class' => 'nav navbar-nav pull-left',
            ),
            'items' => $title_items,
        );
    }
    foreach ($menuData->userModules as $userModule) {
        $userItems[] = array(
            'label' => '<span>' . $userModule->name . '</span>',
            'url' => array("$userModule->url"),
            'active' => false
        );
    }
    if (isset($menuData->systemModules)) {
        $sysytemItems[] = array(
            'label' => Yii::t('main', '{57337488-7CBF-4444-8F0A-6CAAB61FE80D}'),
            'class' => 'bold',
        );
        foreach ($menuData->systemModules as $systemModule) {
            if (isset($menuData->home) && $menuData->home->name === $systemModule->name) {
                $sysytemItems[] = array(
                    'label' => '<i class="moon-point"></i><span>' . $systemModule->name . '</span>',
                    'url' => $systemModule->url !== false ? array("$systemModule->url") : $systemModule->url,
                    'active' => true,
                    'class' => 'dropdown-toggle'
                );
            } else {
                $sysytemItems[] = array(
                    'label' => '<span>' . $systemModule->name . '</span>',
                    'url' => $systemModule->url !== false ? array("$systemModule->url") : $systemModule->url,
                    'active' => false,
                    'class' => 'dropdown-toggle'
                );
            }
        }
    }
    $helpItems = array();
    foreach ($menuData->helpItems as $helpItem) {
        $helpItems[] = array(
            'label' => $helpItem->name,
            'url' => $helpItem->url,
            'class' => 'dropdown-toggle',
            'linkOptions' => array(
                'target' => 'blank',
            ),
        );
    }
}

/*$c = array(
    'class' => '\\application\\widgets\\yiistrap\\TbNav',
    'encodeLabel' => false,
    'htmlOptions' => array(
        'class' => 'nav navbar-nav pull-left settings menu',
        'caret' => false,
    ),
    'items' => array(
        array(
            'icon' => 'icon icon-bars',
            'url' => '#',
            'linkOptions' => array(
                'role' => 'button'
            ),
            'items' => $userItems,
        )
    ),
);*/
$c = '<div class="pull-left settings menu"><a id="leftMenu"><i class="icon icon-bars"></i></a></div>';

if (isset(Yii::app()->user->userId)) {
    $arrItems = array();
    if (PrivilegeManager::checkPermission('200', 'usr')) {
        $arrItems[] = array(
            //'label' => Yii::t('main', '{1C7268EA-A0A6-4916-9DD1-DCBC49A26622}'),
            'label' => $user->getFullname(),
            'url' => Yii::app()->homeUrl . '/usr/user/profile',
            'class' => 'dropdown-toggle',
        );
        $arrItems[] = TbHtml::menuDivider();
    }
    $arrItems[] = array(
        'label' => $menuData->lang->label,
        'class' => 'bold',
    );
    foreach ($menuData->lang->langItems as $langItem) {
        $arrItems[] = array(
            'icon' => (Yii::app()->language == $langItem->lang) ? 'icon moon-point' : '',
            'label' => $langItem->name,
            'url' => 'javascript: void(0);',
            'mocoUrl' => $langItem->mocoUrl,
            'class' => 'dropdown-toggle lang',
            'lang' => $langItem->lang,
            'moodleUrl' => $langItem->moodleUrl,
        );
    }
    $arrItems[] = TbHtml::menuDivider();
    $arrItems[] = array(
        'label' => Yii::t('main', '{0695DFF8-82A6-49C7-A4E1-7D2516AB65B3}'),
        'class' => 'bold',
    );
    foreach ($menuData->roles as $role) {
        $arrItem['label'] = $role;
        $arrItems[] = $arrItem;
    }
    $arrItems[] = TbHtml::menuDivider();
    $arrItems[] = array(
        'label' => Yii::t('main', '{3F4B2A7F-CC97-460B-A495-A926A3D58AEC}'),
        'url' => Yii::app()->homeUrl . '/usr/login/logout',
        'class' => 'dropdown-toggle',
    );
    $rightitems = array();
    $rightitems[] = array(
        'icon' => 'icon fa fa-ellipsis-v',
        'url' => '#',
        'class' => 'settings',
        'linkOptions' => array(
            'role' => 'button',
            'caret' => false,
        ),
        'items' => $userItems,
        'ulClass' => 'dropdown-menu-right',
    );
    $rightitems[] = array(
        'icon' => 'icon tmx-question-circle',
        'url' => '#',
        'class' => 'settings',
        'linkOptions' => array(
            'role' => 'button',
            'caret' => false,
        ),
        'items' => $helpItems,
        'ulClass' => 'dropdown-menu-right',
    );
    if (count($sysytemItems)) {
        $rightitems[] = array(
            'icon' => 'icon tmx-gear',
            'url' => '#',
            'class' => 'settings',
            'linkOptions' => array(
                'role' => 'button',
                'caret' => false,
            ),
            'items' => $sysytemItems,
            'ulClass' => 'dropdown-menu-right',
        );
    }
    $rightitems[] = array(
        'icon' => 'icon tmx-user',
        'url' => '#',
        'class' => 'settings last',
        'data-toggle' => "tooltip",
        'data-placement' => "left",
        'title' => $user->getFullname(),
        'linkOptions' => array(
            'role' => 'button',
            'caret' => false,
        ),
        'items' => $arrItems,
        'ulClass' => 'dropdown-menu-right sidebar',
    );
    $headerRightMenu = array(
        'class' => '\\application\\widgets\\yiistrap\\TbNav',
        'htmlOptions' => array(
            'class' => 'nav navbar-nav pull-right',
        ),
        'encodeLabel' => false,
        'items' => $rightitems,
    );
} else {
    $headerRightMenu = array(
        'class' => '\\application\\widgets\\yiistrap\\TbNav',
        'htmlOptions' => array(
            'class' => 'nav navbar-nav pull-right'
        ),
        'items' => array(
            array(
                'icon' => 'icon icon-sign-in',
                'url' => Yii::app()->homeUrl . '/usr/login/login',
                'class' => 'settings last',
                'linkOptions' => array(
                    'role' => 'button',
                    'rel' => 'tooltip',
                    'data-original-title' => Yii::t('main', 'Login'),
                    'data-placement' => 'bottom'
                )
            ),
        ),
    );
}

if (isset(Yii::app()->user->userId)) {

    $this->widget('\\application\\widgets\\yiistrap\\TbNavbar', array(
        'brandLabel' => '<img src="' . Yii::app()->theme->baseUrl . '/images/logo-blue-main.png" alt="logo">',
        'brandOptions' => array(
            'class' => 'navbar-brand'
        ),
        'brandItems' => array(
            isset(Yii::app()->user->userId) > 0 ? $c : ''
        ),
        'color' => TbHtml::NAVBAR_COLOR_INVERSE,
        'display' => TbHtml::NAVBAR_DISPLAY_STATICTOP,
        'htmlOptions' => array(
            'id' => 'header',
            'class' => 'col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding navbar navbar-default',
            'role' => 'banner',
            'tagOption' => 'header',
            'innerOptions' => array(
                'class' => 'navbar-header pull-left',
            ),
            'container' => false,
        ),
        'items' => array(
            $title,
            $headerSectionMenu,
            $headerRightMenu
        ),

    ));
} ?>
<?php if ($this->module && property_exists($this->module, 'menu')) { ?>
  <div class="up" id="up" title="наверх"></div>
<?php } ?>
<?php $leftNavBar = ""; ?>
<?php if (isset(Yii::app()->user->userId) && $this->module && property_exists($this->module, 'menu')) {
    $wrapperClass = "wrapper";
    $leftNavBar = $this->widget('\\application\\widgets\\yiistrap\\TbNavbar', array(
        'brandLabel' => false,
        'display' => TbHtml::NAVBAR_DISPLAY_STATICTOP,
        'htmlOptions' => array(
            'class' => 'primary-sidebar hidden-xs hidden-sm',
            'tagOption' => 'div',
            'header' => false,
            'container' => false,
        ),
        'items' => array(
            $left
        ),

    ), true);
} elseif (isset(Yii::app()->user->userId)) {
    $wrapperClass = "wrapper";
} else {
    $wrapperClass = "wrapper-full";
}
if (isset(Yii::app()->user->userId)) {
    $mc = "main-content";
} else {
    $mc = "main-content-full";
}
?>

<div class="row no-margin"></div>
<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding <?php echo $wrapperClass; ?>">
  <div class="<?php echo $mc; ?>" id="page">
      <?php
      if (isset($this->breadcrumbs) && isset(Yii::app()->user->userId) && $this->module && property_exists($this->module, 'menu')):?>
        <div class="content-control">
            <?php
            $breadcrumbsWidgetConfig = array(
                'links' => $this->breadcrumbs,
                'htmlOptions' => array(
                    'class' => 'breadcrumb',
                ),
            );
            ?>
            <?php $this->widget('\\application\\widgets\\yiistrap\\TbBreadcrumb', $breadcrumbsWidgetConfig); ?>
        </div>
      <?php endif ?>

    <div class="container-fluid">
        <div class="page-content">
            <?php echo $content; ?>
        </div>
    </div>
  </div><!-- page -->
  <div class="primary-sidebar <?= (isset($_COOKIE['menu']) ? 'closed' : '') ?>">
        <?php
        $iterator = 0;
        foreach ($menuLeft as $menu) {
            $icon = CHtml::openTag('span', array('class' => 'media-left'));
            $icon .= CHtml::openTag('i', array('class' => $menu['icon']));
            $icon .= CHtml::closeTag('i');
            $icon .= CHtml::closeTag('span');
            $label = $icon . $menu['label'];

            if (count($menu['items'])) {
                $links = '';
                $in = '';
                foreach ($menu['items'] as $menuItem) {
                    $isActive = $menuItem['active'] ? 'active' : '';
                    if ($menuItem['active']) {
                        $in = 'in';
                    }
                    $links .= CHtml::link($menuItem['label'], $menuItem['url'], array('class' => 'list-group-item list-group-item-action ' . $isActive));
                }
                echo CHtml::link($label, '#sub' . $iterator, array('data-toggle' => 'collapse', 'class' => 'list-group-item list-group-item-action'));
                echo CHtml::tag('div', array('class' => 'collapse collapse-items ' . $in, 'id' => 'sub' . $iterator), $links);
            } else {
                echo CHtml::link($label, $menu['url'], array('class' => 'list-group-item list-group-item-action'));
            }

            $iterator++;
        }
        ?>
    </div>
</div><!-- wrapper -->

<?php if (isset(Yii::app()->user->userId)) { ?>
  <div class="row no-margin"></div>
  <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 no-padding" id="footer">
    <div class="text-center">
        <?php echo Yii::t('main', 'Copyright © ' . date('Y') . ' Technomatix Ltd. | All Rights Reserved'); ?>
    </div>
  </div><!-- footer -->
<?php }
$js = 'js: $(".dropdown-menu input").click(function(){return false;});';
Yii::app()->clientScript->registerScript(__CLASS__ . '#' . $this->getId() . '0', $js, \CClientScript::POS_READY);



$token = Moco::app()->session->get('token');
$em = \Yii::app()->doctrine->getEntityManager();
$uas = $em->getRepository(UserActiveSession::getClassName())->findOneBy(array('token' => $token));


$url = Moco::app()->params['moodleUrl'] . '/lib/ajax/setuserpref.php'
            . '?sesskey=' . $uas->moodleSesskey
            . '&pref=drawer-open-nav'
            . '&value=';

echo CHtml::tag('div', array('id' => 'urlMoodleLeftMenuChandeStatus', 'class' => 'hidden'), $url);


?>
<script type="text/javascript">
    function setMobileTopMenu() {
        var fullWidth = $("#header").width() - $("#header").children('ul:last').width() - $("#header").find(".navbar-header").width();
        var menuWidth = $("#header").children('ul:first').width();

        if (menuWidth > 0) {
            $("#header").children('ul:first').data('width', menuWidth);
        } else {
            menuWidth = $("#header").children('ul:first').data('width');
        }

        if (fullWidth <= menuWidth) {
            $("#header").children('ul:first').addClass('hidden');
            $("#header").children('ul:last').children('li:first').css('visibility', 'visible');
        } else {
            $("#header").children('ul:first').removeClass('hidden');
            $("#header").children('ul:last').children('li:first').css('visibility', 'hidden');
        }
    }
    setMobileTopMenu();
    $(window).on('resize', function () {
        setMobileTopMenu();
    });
    
    $(document).ready(function () {

        var setLetPanelStatus = function(init) {
            if (init) {
                if ($(window).width() < 400) {
                    document.cookie = "menu=close; path=/;";
                    var panelStatus = 'false';
                    $(".primary-sidebar").addClass("closed"); 
                    $("body").removeClass("drawer-open-left");
                } 
            } else {
                document.cookie = "menu=; path=/; expires=" + (new Date(0)).toUTCString();
                var panelStatus = 'true';
                if ($("body").hasClass("drawer-open-left")) {
                    document.cookie = "menu=close; path=/;";
                    panelStatus = 'false';
                }
            }
            if (!!panelStatus) {
                $.ajax({
                    url: $('#urlMoodleLeftMenuChandeStatus').text() + panelStatus,
                    type: 'POST',
                    dataType: 'text'
                })
            }
        }

        setLetPanelStatus(true);

        $("#leftMenu").click(function() {
            setLetPanelStatus(false);
            $(".primary-sidebar").toggleClass("closed"); 
            $("body").toggleClass("drawer-open-left");
        });
    });
</script>
<!--[if IE]>
<script type="text/javascript">
  $(document).ready(function () {
    $('input, textarea').placeholder();
  });
</script>
<![endif]-->
</body>
</html>
