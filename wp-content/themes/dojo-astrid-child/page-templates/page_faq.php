<?php

/*

Template Name: FAQ

*/
	get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

      <div class="tb-accordion">
        <div class="tb-accordion-item">
          <button class="tb-accordion-bar">Section 1</button>
          <div class="tb-accordion-panel">
            <p>Lorem ipsum...</p>
          </div>
        </div>

        <div class="tb-accordion-item">
          <button class="tb-accordion-bar">Section 2</button>
          <div class="tb-accordion-panel">
            <p>Lorem ipsum...</p>
          </div>
        </div>

        <div class="tb-accordion-item">
          <button class="tb-accordion-bar">Section 3</button>
          <div class="tb-accordion-panel">
            <p>Lorem ipsum...</p>
          </div>
        </div>
      </div>

<!--			--><?php //while ( have_posts() ) : the_post(); ?>
<!---->
<!--				--><?php //get_template_part( 'template-parts/content', 'page' ); ?>
<!---->
<!--			--><?php //endwhile; // end of the loop. ?>


		</main><!-- #main -->
	</div><!-- #primary -->

<?php
  if ( astrid_blog_layout() == 'list' ) :
      get_sidebar();
  endif;
?>

<?php get_footer(); ?>
