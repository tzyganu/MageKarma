<?php get_header() ?>

    <div class="home-content">

        <?php get_search_form() ?>

        <?php
        /**
         * Recent reviews
         */
        ?>
        <?php query_posts( array ( 'category_name' => 'magento-extension-review', 'posts_per_page' => 5 ) ) ?>
        <?php if (have_posts()) : ?>
            <div class="home-block block-recent">
                <h2><?php _e('Recent reviews') ?></h2>
                <ul>
                    <?php while (have_posts()) : the_post() ?>
                        <li>
                            <div class="left"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></div>
                            <div class="right">by <?php the_author() ?></div>
                        </li>
                    <?php endwhile ?>
                </ul>
            </div>
        <?php endif ?>
        <?php wp_reset_query() ?>

        <?php
        /**
         * Best extensions
         */
        ?>
        <?php

        query_posts( array (
            'meta_query' => array(
                array( 'key' => 'Score',  'value' => 'Very Good', 'compare' => 'LIKE' ),
                array( 'key' => 'Score',  'value' => 'Excellent', 'compare' => 'LIKE' ),
                'relation'  => 'OR'
            ),
            'orderby'           => 'meta_value',
            'posts_per_page'    => 5
        ) );

        ?>
        <?php if (have_posts()) : ?>
            <div class="home-block block-top">
                <h2><?php _e('Best extensions') ?></h2>
                <ul>
                    <?php while (have_posts()) : the_post() ?>
                        <li>
                            <div class="left"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></div>
                            <div class="right"><?php echo get_meta_value('Score', 'grade', 'span', false) ?></div>
                        </li>
                    <?php endwhile ?>
                </ul>
            </div>
        <?php endif ?>
        <?php wp_reset_query() ?>

        <?php
        /**
         * Top reviewers
         */
        ?>
        <div class="home-block block-reviewers">
            <h2><?php _e('Top reviewers') ?></h2>
            <?php $authors = get_users( array( 'orderby' => 'post_count', 'order' => 'DESC', 'number' => 5, 'count_total' => true ) ) ?>
            <ul>
                <?php foreach ($authors as $author) : $num_posts = count_user_posts($author->ID) ?>
                    <li>
                        <div class="left"><?php echo get_avatar( $author->ID, 46 ) ?></div>
                        <div class="left text"><a href="<?php echo get_author_posts_url($author->ID) ?>"><?php echo $author->display_name ?></a></div>
                        <div class="right text"><?php echo $num_posts . ' ' . __($num_posts > 1 ? 'reviews' : 'review') ?></div>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>

        <?php
        /**
         * Tags cloud
         */
        ?>
        <div class="home-block block-tags">
            <h2><?php _e('Tags') ?></h2>
            <?php

            wp_tag_cloud( array(
                'orderby'   => 'count',
                'order'     => 'DESC',
                'number'    => 20,
                'smallest'  => 1,
                'largest'   => 1,
                'unit'      => 'em'
            ) );

            ?>
        </div>
    </div>

<?php get_footer() ?>