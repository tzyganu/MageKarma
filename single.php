<?php get_header() ?>

    <div class="content">

        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post() ?>

                <h2><?php the_title() ?></h2>

                <div class="stats-area">
                    <div class="score"><?php echo get_meta_value('Score', 'grade') ?></div>
                    <ul>
                        <li><?php echo get_meta_value('Pricing', 'price') ?></li>
                        <li><?php echo get_meta_value('Reviewed Version') ?></li>
                        <li><?php echo get_meta_value('Developer') ?></li>
                        <li><?php echo get_meta_value('Support', 'grade') ?></li>
                        <li><?php echo get_meta_value('Locale') ?></li>
                        <li><?php echo get_meta_value('Test') ?></li>
                    </ul>
                    <ul>
                        <li><?php echo get_meta_value('MagentoConnect', 'url') ?></li>
                        <li><?php echo get_meta_value('Modman', 'yes-no') ?></li>
                        <li><?php echo get_meta_value('Composer', 'yes-no') ?></li>
                        <li><?php echo get_meta_value('GitHub', 'url') ?></li>
                        <li><?php echo get_meta_value('Core Hacks', 'zero-must') ?></li>
                        <li><?php echo get_meta_value('Class Rewrites', 'zero-may') ?></li>
                    </ul>
                </div>

                <?php the_content() ?>

                <?php if ($overrides = get_post_meta(get_the_ID(), 'Overrides')) : ?>
                    <h3>Overrides:</h3>
                    <ul>
                        <?php foreach ($overrides as $override) : ?>
                            <li><?php echo $override ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>

                <div class="author-area">
                    <div class="left">
                        <h3>Reviewer</h3>
                        <div class="left"><?php echo get_avatar( get_the_author_meta( 'ID' ), 80 ) ?></div>
                        <div class="left bio">
                            <strong><a href="<?php echo get_author_posts_url(get_the_author_meta( 'ID' )) ?>"><?php the_author() ?></a></strong>
                            <?php the_author_meta('description') ?>
                        </div>
                    </div>

                    <?php query_posts( array('author' => get_the_author_meta( 'ID' ), 'post__not_in' => array( get_the_ID() ) ) ) ?>

                    <?php if (have_posts()) : ?>
                        <div class="left">
                            <h3>Other reviews by <?php the_author() ?></h3>
                            <ul>
                                <?php while (have_posts()) : the_post() ?>
                                    <li><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title() ?></a></li>
                                <?php endwhile ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <?php wp_reset_query(); ?>

                </div>

                <?php comments_template( '/comments.php', true ) ?>

            <?php endwhile ?>
        <?php else : ?>

            <p><?php _e('Sorry, no reviews matched your criteria.'); ?></p>

        <?php endif ?>

    </div>

    <a href="<?php echo home_url('/') ?>" class="back-link">Back to search</a>

<?php get_footer() ?>