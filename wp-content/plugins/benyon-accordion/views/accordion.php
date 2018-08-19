<?php if( have_rows('accordion_items') ): ?>
<div class="tb-accordion">
  <?php while ( have_rows('accordion_items') ) : the_row(); ?>
    <div class="tb-accordion-item">
      <button class="tb-accordion-bar"><?php the_sub_field('accordion_item_title'); ?></button>
      <div class="tb-accordion-panel">
        <?php the_sub_field('accordion_item_body'); ?>
        </div>
    </div>
  <?php endwhile; ?>
</div>
<?php endif; ?>
