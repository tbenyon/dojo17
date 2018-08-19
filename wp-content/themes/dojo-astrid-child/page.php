<?php get_header(); ?>

<div id="primary" class="content-area">
  <main id="main" class="site-main" role="main">
      <?php
        if (class_exists('BenyonContentSections\Content_Sections')) :
            BenyonContentSections\Content_Sections::instance()->get_content_sections_view();
        endif;
      ?>
  </main>
</div>

<?php
get_sidebar();
get_footer();
