<?php

/**
 * Check if the current user is the site administrator by comparing emails.
 *
 * This function checks if the logged-in user's email matches the 'admin_email' 
 * option set in the WordPress settings.
 *
 * @since  1.0.0
 *
 * @return bool|void True if the user email matches the admin email, otherwise void.
 */
function igs_cs_is_user_admin() {

  if ( ! is_user_logged_in() )
    return;

  if ( ! $user = wp_get_current_user() )
    return;

  return $user->user_email === get_option('admin_email');

}

/**
 * Print data in a pre-formatted block for debugging purposes.
 *
 * This function outputs data using print_r within <pre> tags. It is visible 
 * only to administrators and when the request is not an admin-side request.
 *
 * @since  1.0.0
 *
 * @param  mixed $array The data to be printed.
 * @return void
 */
function igs_cs_print($array) {
  
  if ( ! igs_cs_is_user_admin() ) 
    return;

  if ( ! IGS_CS()->is_request('admin') ) {
    echo '<pre class="ta-l">';
    print_r($array);
    echo '</pre>';
  }

}

/**
 * Dump variable information for debugging purposes.
 *
 * This function outputs variable details using var_dump. It is visible 
 * only to administrators and when the request is not an admin-side request.
 *
 * @since  1.0.0
 *
 * @param  mixed $vars The variables to be dumped.
 * @return void
 */
function igs_cs_var_dump($vars) {
  
  if ( ! igs_cs_is_user_admin() ) 
    return;

  if ( ! IGS_CS()->is_request('admin') ) {
    var_dump($vars);
  }

}

/**
 * Implode and escape HTML attributes for output.
 *
 * @since 1.0.0
 * @param array $raw_attrs Attribute name value pairs.
 * @return string
 */
function igs_cs_html_attributes( $raw_attrs = array() ) {

  if ( empty( $raw_attrs ) )
    return;

	$attributes = array();

	foreach ( $raw_attrs as $name => $value ) {

    if ( ! empty( $value ) ) {
      if ( $name === 'attr' ) {
        $attributes[] = esc_attr( $value );
      } else {
        if ( is_array( $value ) ) {
          $attributes[] = esc_attr( $name ) . '="' . implode(' ', $value ) . '"';
        } else {
          $attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
        }
      }
    }
	}
	return implode( ' ', $attributes );
}


/*
* Get Pagination
*/
function igs_cs_get_pagination( $igs_query, $attrs = array() ) {

  if ( $igs_query->max_num_pages < 2 )
    return;

  IGS_Assets::enqueue_style( 'igs-pagination', IGS_Assets::get_asset_url( 'css/igs-pagination.css'), true );

  $big      = 999999999;
  $paged    = get_query_var('paged');

  $links = paginate_links([
    'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link($big) ) ),
    'format'    => '/page/%#%',
    'current'   => max(1, $paged),
    'total'     => $igs_query->max_num_pages,
    'mid_size'  => 1,
    'prev_text' => __('Prev', 'igs-client-system'),
    'next_text' => __('Next', 'igs-client-system'),
  ]);

  $links = str_replace('<a class="prev', '<a rel="prev"  class="prev', $links);
  $links = str_replace('<a class="next', '<a rel="next"  class="next', $links);

  do_action( 'igs_cs_before_pagination' );

  $default_attts = array(
    'class' => 'pagination d-f ai-c jc-c gx-5'
  );
  $attrs = array_merge_recursive(  $default_attts, $attrs );

  return apply_filters(
    'igs_cs_pagination',
    wp_sprintf(
      '<div %2$s>%1$s</div>',
      $links,
      igs_html_attributes($attrs)
    ),
    $links
  );

}

/*
* Get Link
*/
function igs_cs_the_pagination( $igs_query ) {

  echo igs_cs_get_pagination( $igs_query );

}
