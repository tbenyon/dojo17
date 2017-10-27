<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package coderdojo2017
 */

?>

<?php get_template_part( 'template-parts/content', 'head' ); ?>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'coderdojo2017' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="site-branding">
      <div class="logo-container">
			  <?php the_custom_logo(); ?>
      </div>
		</div><!-- .site-branding -->

    <div class="title-container">
      <h1 class="site-title">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
          <span class="big">Coder Dojo Horsham</span>
          <span class="small">Coder Dojo</span>
        </a>
      </h1>
    </div>

    <div class="nav-container">
      <nav id="site-navigation" class="main-navigation">
        <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'coderdojo2017' ); ?></button>
          <?php
          wp_nav_menu( array(
              'theme_location' => 'menu-1',
              'menu_id'        => 'primary-menu',
          ) );
          ?>
      </nav>
    </div>

	</header><!-- #masthead -->

  <div class="header-spacer"></div>

	<div id="content" class="site-content">
