<?php $benyon_ccs_hyperlink_type = get_sub_field("hyperlink"); ?>

<?php if ($benyon_ccs_hyperlink_type == "internal" && get_sub_field("internal_link")) : ?>
  <a href="<?php echo get_permalink(get_sub_field("internal_link")); ?>">
<?php elseif ($benyon_ccs_hyperlink_type == "external" && get_sub_field("external_link")) : ?>
  <a target="_blank" href="<?php the_sub_field("external_link"); ?>">
<?php endif; ?>

  <img
    src="<?php echo wp_get_attachment_image_src(get_sub_field('image'), 'large')[0]; ?>"
    <?php if (get_post_meta( get_sub_field('image'), '_wp_attachment_image_alt', true)) : ?>
      alt="<?php echo get_post_meta( get_sub_field('image'), '_wp_attachment_image_alt', true); ?>"
    <?php endif; ?>
  >

<?php if ($benyon_ccs_hyperlink_type == "internal" && get_sub_field("internal_link") != "" || $benyon_ccs_hyperlink_type == "external" && get_sub_field("external_link") != "") : ?>
  </a>
<?php endif; ?>
