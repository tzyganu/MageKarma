<?php if ( !post_password_required() ) : ?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h3 class="comments-title">Comments</h3>

		<ul class="commentlist">
			<?php wp_list_comments( 'avatar_size=80&type=comment' ); ?>
		</ul>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		<nav id="comment-nav-below" class="navigation" role="navigation">
			<h1 class="assistive-text section-heading"><?php _e( 'Comment navigation' ); ?></h1>
			<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;' ) ); ?></div>
		</nav>
		<?php endif ?>

	<?php endif ?>

	<?php

    comment_form(array(
        'fields' => array(
            'author'    => '<input name="author" placeholder="Name *" value="' . esc_attr( $commenter['comment_author'] ) . '"/>',
            'email'     => '<input name="email" placeholder="Email *" value="' . esc_attr(  $commenter['comment_author_email'] ) .'"/>'
        ),
        'comment_field' => '<textarea id="comment" name="comment" placeholder="Comment"></textarea>',
        'comment_notes_after' => ''
    ));

    ?>

</div>

<?php endif ?>