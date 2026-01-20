<?php


/**
 * Fetches an array containing all of the configurable path constants to be used in tokenization.
 *
 * @return array The key is the define and the path is the constant.
 */
function igs_cs_get_path_define_tokens() {
  $defines = array(
    'ABSPATH',
    'WP_CONTENT_DIR',
    'WP_PLUGIN_DIR',
    'WPMU_PLUGIN_DIR',
    'PLUGINDIR',
    'WP_THEME_DIR',
  );

  $path_tokens = array();
  foreach ( $defines as $define ) {
    if ( defined( $define ) ) {
      $path_tokens[ $define ] = constant( $define );
    }
  }

  return apply_filters( 'igs_cs_get_path_define_tokens', $path_tokens );
}

/**
 * Given a path, this will convert any of the subpaths into their corresponding tokens.
 *
 * @param string $path The absolute path to tokenize.
 * @param array  $path_tokens An array keyed with the token, containing paths that should be replaced.
 * @return string The tokenized path.
 */
function igs_cs_tokenize_path( $path, $path_tokens ) {
  uasort(
    $path_tokens,
    function ( $a, $b ) {
      $a = strlen( $a );
      $b = strlen( $b );

      if ( $a > $b ) {
          return -1;
      }

      if ( $b > $a ) {
          return 1;
      }

      return 0;
    }
  );

  foreach ( $path_tokens as $token => $token_path ) {
    if ( 0 !== strpos( $path, $token_path ) ) {
      continue;
    }

    $path = str_replace( $token_path, '{{' . $token . '}}', $path );
    break;
  }

  return $path;
}

/**
 * Given a tokenized path, this will expand the tokens to their full path.
 *
 * @param string $path The absolute path to expand.
 * @param array  $path_tokens An array keyed with the token, containing paths that should be expanded.
 * @return string The absolute path.
 */
function igs_cs_untokenize_path( $path, $path_tokens ) {
  foreach ( $path_tokens as $token => $token_path ) {
    $path = str_replace( '{{' . $token . '}}', $token_path, $path );
  }

  return $path;
}

/**
 * Set a template to the template cache with tokens.
 *
 * @param string $cache_key Object cache key.
 * @param string $template Located template.
 */
function igs_cs_set_template_cache( $cache_key, $template ) {
  $cache_path = igs_cs_tokenize_path( $template, igs_cs_get_path_define_tokens() );
  wp_cache_set( $cache_key, $cache_path, 'igs_cs' );

  $cached_templates = wp_cache_get( 'cached_templates', 'igs_cs' );
  if ( is_array( $cached_templates ) ) {
    $cached_templates[] = $cache_key;
  } else {
    $cached_templates = array( $cache_key );
  }

  wp_cache_set( 'cached_templates', $cached_templates, 'igs_cs' );
}

/**
 * Get template part (for templates like the shop-loop).
 *
 * @param mixed  $slug Template slug.
 * @param string $name Template name (default: '').
 */
function igs_cs_get_template_part( $slug, $name = '', $args = array() ) {
	$cache_key     = sanitize_key( implode( '-', array( 'template-part', $slug, $name, IGS_CS_Constants::get_constant( 'IGS_CS_VERSION' ) ) ) );
	$template      = (string) wp_cache_get( $cache_key, 'igs_cs' );
	$template_name = $name ? "{$slug}-{$name}" : $slug;

	if ( ! $template ) {
		if ( $name ) {
			$template = locate_template(
				array(
					"{$slug}-{$name}.php",
					IGS_CS()->template_path() . "/{$slug}-{$name}.php",
				)
			);

			if ( ! $template ) {
				$fallback = IGS_CS()->plugin_path() . "/templates/{$slug}-{$name}.php";
        $template = file_exists( $fallback ) ? $fallback : '';
			}
		}

		if ( ! $template ) {
			$template = locate_template(
				array(
					"{$slug}.php",
					IGS_CS()->template_path() . "{$slug}.php",
				)
			);
		}

    $cache_path = igs_cs_tokenize_path( $template, igs_cs_get_path_define_tokens() );
		igs_cs_set_template_cache( $cache_key, $cache_path );
  } else {
    $template = igs_cs_untokenize_path( $template, igs_cs_get_path_define_tokens() );
  }

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'igs_cs_get_template_part', $template, $slug, $name );

	if ( $template ) {
		igs_cs_get_template( $template_name, $args );
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
	$cache_key = sanitize_key( implode( '-', array( 'template', $template_name, $template_path, $default_path, IGS_CS_Constants::get_constant( 'IGS_CS_VERSION' ) ) ) );
	$template  = (string) wp_cache_get( $cache_key, 'igs_cs' );

  $template_name .= '.php';

	if ( ! $template ) {
		$template = igs_cs_locate_template( $template_name, $template_path, $default_path );

    $cache_path = igs_cs_tokenize_path( $template, igs_cs_get_path_define_tokens() );
		igs_cs_set_template_cache( $cache_key, $cache_path );
  } else {
    $template = igs_cs_untokenize_path( $template, igs_cs_get_path_define_tokens() );
  }

	$filter_template = apply_filters( 'igs_cs_get_template', $template, $template_name, $args, $template_path, $default_path );

  if ( $filter_template !== $template ) {
    if ( ! file_exists( $filter_template ) ) {
      _doing_it_wrong(__FUNCTION__, wp_sprintf(__( '%s does not exist.' ), '<code>' . $filter_template . '</code>' ), '1.0');
      return;
    }
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
				__( 'action_args should not be overwritten when calling igs_cs_get_template.' ),
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
 * Like igs_cs_get_template, but returns the HTML instead of outputting.
 *
 * @see igs_cs_get_template
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
	igs_cs_get_template( $template_name, $args, $template_path, $default_path );
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
		$template_path = IGS_CS()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = IGS_CS()->plugin_path() . '/templates/';
	}

  $template = locate_template([
    trailingslashit( $template_path ) . $template_name,
    $template_name
  ]);


	if ( empty( $template ) ) {
	  $template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);
	}

	/**
	 * Filter to customize the path of a given IGS_Client_System template.
	 *
	 * @param string $template Full file path of the template.
	 * @param string $template_name Template name.
	 * @param string $template_path Template path.
	 * @param string $template_path Default IGS_Client_System templates path.
   * 
	 */
	return apply_filters( 'igs_cs_locate_template', $template, $template_name, $template_path, $default_path );
}