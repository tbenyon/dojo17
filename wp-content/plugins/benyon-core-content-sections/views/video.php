<?php
  $benyon_ccs_video_id =  get_sub_field('youtube_video_id');
  $benyon_ccs_video_url =  "https://www.youtube.com/watch?v=" . $benyon_ccs_video_id;
  $benyon_ccs_image_id =  get_sub_field('poster_frame');
  $benyon_ccs_image_url =  wp_get_attachment_image_src($benyon_ccs_image_id, 'large')[0];
?>

<?php if ($benyon_ccs_video_id && $benyon_ccs_image_url) : ?>
  <div class="benyon-ccs-video-item-outer-container">
    <a class="swipebox swipebox-video benyon-play-icon-hover-trigger benyon-arrow-hover-container" rel=”youtube” href="<?php echo $benyon_ccs_video_url; ?>&rel=0">
      <div class="benyon-ccs-video-item-container">
          <div
              class="benyon-ccs-video-item"
              style="background-image: url('<?php echo $benyon_ccs_image_url; ?>')"
              <?php if (get_post_meta( $benyon_ccs_image_id, '_wp_attachment_image_alt', true)) : ?>
                aria-label="<?php echo get_post_meta( $benyon_ccs_image_id, '_wp_attachment_image_alt', true); ?>"
              <?php endif; ?>
          >
          </div>
          <div class="benyon-play-icon benyon-ccs-play-icon benyon-arrow benyon-arrow-right">
      </div>
    </a>
  </div>
<?php endif; ?>
