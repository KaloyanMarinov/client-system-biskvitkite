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

  echo '<pre class="ta-l">';
  print_r($array);
  echo '</pre>';

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

    if ( $name === 'attr' ) {
      $attributes[] = esc_attr( $value );
    } else {
      if ( is_array( $value ) ) {
        $attributes[] = $name . '="' . esc_attr(implode(' ', $value )) . '"';
      } else {
        $attributes[] = $name . '="' . esc_attr( $value ) . '"';
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

  $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;

  $links = paginate_links([
    'base'      => admin_url( 'admin.php?page=' . $_GET['page'] . '%_%' ),
    'format'    => '&paged=%#%',
    'current'   => max(1, $paged),
    'total'     => $igs_query->max_num_pages,
    'mid_size'  => 2,
    'prev_text' => '<svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.97285 0.742436C7.95952 0.685823 7.91862 0.580958 7.88196 0.509404C7.84531 0.437849 7.76186 0.327474 7.6965 0.264171C7.63114 0.200841 7.50365 0.114756 7.41318 0.0728373C7.27347 0.00813643 7.2136 -0.00324106 7.01566 -0.00266534C6.83288 -0.00214445 6.75011 0.0119198 6.63184 0.0625838C6.49704 0.120321 6.14762 0.46011 3.33681 3.26695C1.2039 5.39687 0.170717 6.44708 0.124604 6.53212C0.0872366 6.60104 0.0408222 6.73459 0.0214664 6.82881C-0.00715572 6.96825 -0.00715573 7.03207 0.0214664 7.17151C0.0408222 7.26576 0.0872366 7.39928 0.124604 7.4682C0.170717 7.55324 1.2039 8.60345 3.33681 10.7334C6.14762 13.5402 6.49704 13.88 6.63184 13.9377C6.75036 13.9885 6.83249 14.0024 7.01566 14.0027C7.21412 14.0031 7.27405 13.9916 7.4196 13.9251C7.52657 13.8762 7.63755 13.7967 7.71624 13.7126C7.78538 13.6386 7.87086 13.5138 7.90617 13.4351C7.94145 13.3564 7.97866 13.2281 7.9888 13.1499C7.99944 13.0682 7.9933 12.9401 7.97435 12.8484C7.95626 12.7606 7.91697 12.6396 7.88706 12.5792C7.85167 12.5078 6.87995 11.5158 5.10103 9.73487L2.36934 7.00016L5.10103 4.26545C6.88691 2.4776 7.85145 1.49277 7.88687 1.42108C7.91664 1.36077 7.95489 1.24603 7.97183 1.16614C7.98877 1.08623 8.00139 0.981364 7.99988 0.933112C7.99834 0.884833 7.9862 0.79905 7.97285 0.742436Z" fill="currentColor"/></svg>',
    'next_text' => '<svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.0271541 13.2576C0.0404781 13.3142 0.0813822 13.419 0.118037 13.4906C0.154692 13.5622 0.238145 13.6725 0.303504 13.7358C0.368862 13.7992 0.496345 13.8852 0.586817 13.9272C0.726527 13.9919 0.786402 14.0032 0.984343 14.0027C1.16712 14.0021 1.24989 13.9881 1.36816 13.9374C1.50296 13.8797 1.85238 13.5399 4.66319 10.7331C6.7961 8.60313 7.82928 7.55292 7.8754 7.46788C7.91276 7.39896 7.95918 7.26541 7.97853 7.17119C8.00716 7.03175 8.00716 6.96793 7.97853 6.82849C7.95918 6.73424 7.91276 6.60072 7.8754 6.5318C7.82928 6.44676 6.7961 5.39655 4.66319 3.26663C1.85238 0.459789 1.50296 0.119999 1.36816 0.0622615C1.24964 0.0114879 1.16751 -0.00241189 0.984342 -0.0027409C0.785881 -0.00309661 0.72595 0.00841805 0.580401 0.0749283C0.473425 0.12381 0.362447 0.203316 0.283764 0.287427C0.214622 0.361366 0.12914 0.486217 0.0938283 0.5649C0.0585444 0.643583 0.0213414 0.771915 0.0111976 0.850077C0.000560367 0.931803 0.00670148 1.05989 0.0256457 1.15165C0.04374 1.23935 0.0830265 1.36045 0.112937 1.42076C0.148331 1.49218 1.12005 2.48424 2.89897 4.26513L5.63066 6.99984L2.89897 9.73455C1.11309 11.5224 0.14855 12.5072 0.113129 12.5789C0.0833561 12.6392 0.0451114 12.754 0.0281685 12.8339C0.0112257 12.9138 -0.00138559 13.0186 0.000122267 13.0669C0.00165754 13.1152 0.0138027 13.201 0.0271541 13.2576Z" fill="currentColor"/></svg>',
  ]);

  $links = str_replace('<a class="prev', '<a rel="prev"  class="prev', $links);
  $links = str_replace('<a class="next', '<a rel="next"  class="next', $links);

  do_action( 'igs_cs_before_pagination' );

  $default_attts = array(
    'class' => 'pagination d-f ai-c jc-c gx-5 mt-30'
  );
  $attrs = array_merge_recursive(  $default_attts, $attrs );

  return apply_filters(
    'igs_cs_pagination',
    wp_sprintf(
      '<div %2$s>%1$s</div>',
      $links,
      igs_cs_html_attributes($attrs)
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
