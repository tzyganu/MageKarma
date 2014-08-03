<?php get_header() ?>

    <div class="content">
        <h2><?php printf( __( 'Reviews tagged: %s' ), '<span>' . single_tag_title( '', false ) . '</span>' ); ?></h2>

        <?php if (have_posts()) : ?>

            <?php while (have_posts()) : the_post() ?>

                <div class="search-result">
                    <a href="<?php the_permalink() ?>"><?php the_title() ?></a>
                    <?php _e('reviewed by') ?> <?php the_author() ?>
                    <div class="score">
                        <?php echo get_meta_value('Score', 'grade') ?>
                    </div>
                </div>

            <?php endwhile ?>

            <div class="nav-previous left"><?php next_posts_link( 'Previous' ); ?></div>
            <div class="nav-next right"><?php previous_posts_link( 'Next' ); ?></div>

        <?php else : ?>

            <p><?php _e('Sorry, no reviews matched your criteria.'); ?></p>

        <?php endif ?>

    </div>

    <a href="<?php echo home_url('/') ?>" class="back-link">Back to search</a>

<?php get_footer() ?>