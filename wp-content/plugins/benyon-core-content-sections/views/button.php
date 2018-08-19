<div class="benyon-button-container">

  <?php
    $benyon_ccs_button_style_class = get_sub_field("button_style") == "primary" ? "primary" : "secondary";

    if (get_sub_field("hyperlink") == "internal") {
      $href = get_permalink(get_sub_field("internal_link"));
    } else {
      $href = get_sub_field("external_link");
    }
  ?>

  <a
      href="<?php echo $href; ?>"
      class="benyon-button <?php echo $benyon_ccs_button_style_class ?>"
      <?php if (get_sub_field("hyperlink") == "external") : ?>
        target="_blank"
      <?php endif; ?>
  >
    <?php the_sub_field("button_text"); ?>
  </a>

</div>
