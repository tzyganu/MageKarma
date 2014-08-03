<?php

add_action( 'init', function() {
    global $wp_post_types;

    $labels = &$wp_post_types['post']->labels;
    $labels->name = 'Reviews';
    $labels->singular_name = 'Review';
    $labels->add_new = 'Add Review';
    $labels->add_new_item = 'Add Review';
    $labels->edit_item = 'Edit Review';
    $labels->new_item = 'Review';
    $labels->view_item = 'View Review';
    $labels->search_items = 'Search Reviews';
    $labels->not_found = 'No Reviews found';
    $labels->not_found_in_trash = 'No Reviews found in Trash';
    $labels->all_items = 'All Reviews';
    $labels->menu_name = 'Reviews';
    $labels->name_admin_bar = 'Reviews';
} );

add_action( 'admin_menu', function() {
    global $menu, $submenu;

    $menu[5][0] = 'Reviews';
    $submenu['edit.php'][5][0] = 'Reviews';
    $submenu['edit.php'][10][0] = 'Add Review';
    $submenu['edit.php'][16][0] = 'Review Tags';
    echo '';
} );

add_action( 'init', function() {
    register_nav_menu( 'menu', __( 'Main Navigation' ) );
} );

function get_meta_value($key, $type = '', $value_wrapper = 'span', $display_label = true)
{
    $value = trim(get_post_meta(get_the_ID(), $key, true));
    $classes = array();

    if ($type == 'grade') {
        $classes[] = preg_replace('/[^a-z0-9]/ism', '_', strtolower($value));
    } elseif ($type == 'yes-no') {
        if (preg_match('/yes/i', $value)) {
            $value = 'Yes';
            $classes[] = 'passed';
        } else {
            $value = 'No';
            $classes[] = 'failed';
        }
    } elseif ($type == 'price') {
        $classes[] = preg_match('/free/i', $value) ? 'passed' : 'failed';
    } elseif ($type == 'url') {
        if (preg_match('/^https?:\/\//i', $value)) {
            $value = ' - <a href="' . $value . '" target="_blank">link</a>';
        } else {
            $value = 'No';
        }
    } elseif (preg_match('/^zero-/i', $type)) {
        if ($value == 0) {
            $classes[] = 'passed';
        } elseif ($type == 'zero-must' && (int) $value > 0) {
            $classes[] = 'failed';
        }
    }

    if ($value == '') {
        $value = '-';
    }

    return ($display_label ? $key . ': ' : '') . '<' . $value_wrapper . (empty($classes) ? '' : ' class="' . implode(' ', $classes) . '"') . '>' . $value . '</' . $value_wrapper . '>';
}
