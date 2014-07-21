<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
    <script type="text/javascript" src="<?php bloginfo('template_directory') ?>/js/common.js"></script>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <?php wp_head() ?>
</head>
<body <?php body_class() ?>>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-52963888-1', 'auto');
    ga('send', 'pageview');

</script>
<div class="main-container">
<div class="header">
    <div class="left"><a href="<?php echo home_url('/') ?>" class="logo">Mage<strong>Karma</strong></a></div>
    <h1 class="left"><?php bloginfo('description') ?></h1>
    <div class="right menu-container">
        <a href="" onclick="toggleMenu('menu-button', 'header-menu'); return false;" id="menu-button"><img src="<?php bloginfo('template_directory') ?>/images/open.png" alt=""/></a>
        <?php wp_nav_menu( array( 'theme_location' => 'menu', 'container' => '', 'items_wrap' => '<ul id="header-menu">%3$s</ul>' ) ) ?>
    </div>
</div>
