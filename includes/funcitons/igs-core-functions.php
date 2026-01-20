<?php

/*
 * IGS IS ADMIN
 */
function igs_cs_is_user_admin() {

  if ( ! is_user_logged_in() )
    return;

  if ( ! $user = wp_get_current_user() )
    return;

  return $user->user_email === get_option('admin_email');

}

/*
* Print R
*/
function igs_cs_print($array) {
  
  if ( ! igs_is_user_admin() ) 
    return;

  if ( ! IGS_CS()->is_request('admin') ) {
    echo '<pre class="ta-l">';
    print_r($array);
    echo '</pre>';
  }

}

/*
* Var Dump
*/
function igs_cs_var_dump($vars) {
  
  if ( ! igs_is_user_admin() ) 
    return;

  if ( ! IGS_CS()->is_request('admin') ) {
    var_dump($vars);
  }

}

/**
 * Get other templates passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function igs_cs_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	$cache_key = sanitize_key( implode( '-', array( 'template', $template_name, $template_path, $default_path, IGS_CS()->version ) ) );
	$template  = (string) wp_cache_get( $cache_key, 'igs' );

  $template_name .= '.php';

	if ( ! $template ) {
		$template = igs_locate_template( $template_name, $template_path, $default_path );
	}

	$filter_template = apply_filters( 'igs_cs_get_template', $template, $template_name, $args, $template_path, $default_path );

  if (!file_exists($filter_template)) {
    _doing_it_wrong(__FUNCTION__, wp_sprintf(__( '%s does not exist.' ), '<code>' . $filter_template . '</code>' ), '1.0');
    return;
  }

  $template = $filter_template;

	$action_args = array(
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	);

	if ( ! empty( $args ) && is_array( $args ) ) {
		if ( isset( $args['action_args'] ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				__( 'action_args should not be overwritten when calling igs_get_template.' ),
				'1.0.0'
			);
			unset( $args['action_args'] );
		}
		extract( $args );
	}

	do_action( 'igs_cs_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

	include $action_args['located'];

	do_action( 'igs_cs_after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}

/**
 * Get template part (for templates like the shop-loop).
 *
 * @param mixed  $slug Template slug.
 * @param string $name Template name (default: '').
 */
function igs_cs_get_template_part( $slug, $name = '', $args = array() ) {
	$cache_key = sanitize_key( implode( '-', array( 'template-part', $slug, $name, IGS_CS()->version ) ) );
	$template  = (string) wp_cache_get( $cache_key, 'igs' );

	if ( ! $template ) {
		if ( $name ) {
      $template_name = "{$slug}-{$name}";
			$template = locate_template(
				array(
					"{$slug}-{$name}.php",
					IGS_CS()->template_path() . "public/{$slug}-{$name}.php",
				)
			);

			if ( ! $template ) {
				$fallback = IGS_CS()->theme_path() . "public/{$slug}-{$name}.php";
        $template = file_exists( $fallback ) ? $fallback : '';
			}
		}

		if ( ! $template ) {
			$template = locate_template(
				array(
					"{$slug}.php",
					IGS_CS()->template_path() . "public/{$slug}.php",
				)
			);
      $template_name = $slug;
		}
  }

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'igs_cs_get_template_part', $template, $slug, $name );

	if ( $template ) {
		igs_get_template( $template_name, $args );
	}
}

/**
 * Get template part (for templates like the shop-loop).
 *
 * @param mixed  $slug Template slug.
 * @param string $name Template name (default: '').
 */
function igs_cs_get_template_part_html( $slug, $name = '', $args = array() ) {
	ob_start();
	igs_get_template_part( $slug, $name, $args );
	return ob_get_clean();
}

/**
 * Like igs_get_template, but returns the HTML instead of outputting.
 *
 * @see igs_get_template
 * @since 1.0.0
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function igs_cs_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	igs_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}
/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function igs_cs_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = IGS_CS()->template_path() . 'public/';
	}

	if ( ! $default_path ) {
		$default_path = IGS_CS()->theme_path() . '/';
	}

  $template = locate_template([
    trailingslashit( $template_path ) . $template_name,
    $template_name
  ]);


	// Get default template/.
	if ( ! $template ) {
	  $template = $default_path . $template_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'igs_cs_locate_template', $template, $template_name, $template_path );
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

  echo igs_get_pagination( $igs_query );

}
