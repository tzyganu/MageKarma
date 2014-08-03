<form method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ) ?>">
    <div class="search-wrapper">
        <input class="search-field" placeholder="<?php _e('Search for a review ..') ?>" value="<?php echo get_search_query() ?>" name="s" />
    </div>
    <input type="submit" class="search-submit" value="<?php _e( 'Search' ) ?>" />
    <div class="search-helper"><?php _e('(e.g. Mage_ImportExport, Fooman_Speedster)') ?></div>
</form>