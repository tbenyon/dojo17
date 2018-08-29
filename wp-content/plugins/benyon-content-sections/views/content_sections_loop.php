<?php
if (have_rows('benyon_content_sections')): ?>

    <?php while (have_rows('benyon_content_sections')) : ?>
        <?php
            the_row();
            $section_name = get_row_layout();
            $id = get_sub_field($section_name . 'benyon_cs_section_id');
            $classes = get_sub_field($section_name . 'benyon_cs_section_classes');
            $viewpath = get_option(BENYON_CS_VIEW_OPTION_BASE . $section_name);
        ?>

            <section id="<?php echo $id; ?>" class="benyon_cs_content_section  <?php echo $section_name . ' ' . $classes; ?>">
              <div class="benyon_cs_content_section_outer_container">
                <div class="benyon_cs_content_section_inner_container">
                  <?php include $viewpath; ?>
                </div>
              </div>
            </section>

    <?php endwhile; ?>

<?php elseif (have_rows('content_sections')): ?>

    <?php while (have_rows('content_sections')) : ?>
        <?php the_row(); ?>
            <?php if (get_row_layout() == 'plain_content') : ?>

                <section class="benyon_cs_content_section">
                    <div class="benyon_cs_content_section_outer_container">
                        <div class="benyon_cs_content_section_inner_container">
                            <div class="benyon-cca-wysiwyg-container benyon-article-text-section">
                                <?php the_sub_field('content'); ?>
                            </div>
                        </div>
                    </div>
                </section>

            <?php endif; ?>
    <?php endwhile; ?>

<?php endif; ?>
