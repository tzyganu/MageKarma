<?php get_header() ?>

<div class="content">
    <?php if (have_posts()) : ?>

        <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
        <?php if (is_category()) : ?>
            <h2><?php echo single_cat_title() ?></h2>
        <?php elseif( is_tag() ) : ?>
            <h2><?php printf(__('Reviews tagged: %s'), single_tag_title('', false) ) ?></h2>
        <?php elseif (is_day()) : ?>
            <h2><?php printf(_c('Archive for %s|Daily archive page'), get_the_time(__('F jS, Y'))) ?></h2>
        <?php elseif (is_month()) : ?>
            <h2><?php printf(_c('Archive for %s|Monthly archive page'), get_the_time(__('F, Y'))) ?></h2>
        <?php elseif (is_year()) : ?>
            <h2><?php printf(_c('Archive for %s|Yearly archive page'), get_the_time(__('Y'))) ?></h2>
        <?php elseif (is_author()) : ?>
            <h2><?php _e('Author Archive') ?></h2>
        <?php elseif (isset($_GET['paged']) && !empty($_GET['paged'])) : ?>
            <h2><?php _e('Blog Archives') ?></h2>
        <?php endif ?>

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

        <?php
        if ( is_category() ) { // If this is a category archive
            printf("<h2 class='center'>".__("Sorry, but there aren't any posts in the %s category yet.").'</h2>', single_cat_title('',false));
        } else if ( is_date() ) { // If this is a date archive
            echo('<h2>'.__("Sorry, but there aren't any posts with this date.").'</h2>');
        } else if ( is_author() ) { // If this is a category archive
            $userdata = get_userdatabylogin(get_query_var('author_name'));
            printf("<h2 class='center'>".__("Sorry, but there aren't any posts by %s yet.")."</h2>", $userdata->display_name);
        } else {
            echo("<h2 class='center'>".__('No reviews found.').'</h2>');
        }
        ?>

    <?php endif ?>
</div>

<?php get_footer(); ?>
