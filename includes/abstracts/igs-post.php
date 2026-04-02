<?php

/**
 *
 * @link       https://igamingsolutions.com
 * @since      1.0.0
 *
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/Abstracts
 *
 */

defined( 'ABSPATH' ) || exit;

abstract class IGS_CS_Post {

  /**
   * ID for this object.
   *
   * @since 1.0.0
   * @var int
   *
   */
  protected $id;

  /**
   * Post Data for this object.
   *
   * @since 1.0.0
   * @var object
   *
   */
  protected $data;

  /**
   * Post Default Meta Data for this object.
   *
   * @since 1.0.0
   * @var array
   *
   */
  protected $default_meta_data = array();

  /**
   * Post Meta Data for this object.
   *
   * @since 1.0.0
   * @var array
   *
   */
  protected $meta_data = array();

  /**
   * Set Meta data.
   *
   * @since 1.0.0
   *
   */
  protected function igs_set_default_meta_data() {

    if ( $this->default_meta_data ) {

      foreach ($this->default_meta_data as $prop => $method) {
        if ( method_exists($this, $method ) ) {
          $this->igs_set_prop( $prop, $this->$method() );
        }
      }

    }

    if ( $this->meta_data ) {

      foreach ($this->meta_data as $prop => $method) {
        if ( method_exists($this, $method ) ) {
          $this->igs_set_prop( $prop, $this->$method() );
        }
      }

    }

  }

  /**
   * Set Meta data.
   *
   * @since 1.0.0
   *
   */
  protected function igs_set_meta_data() { }

  /**
   * Get Block Fields.
   *
   * @since 1.0.0
   * @return array
   *
   */
  protected function igs_get_block() { }

  /**
   * Delete Post Meta Fields.
   *
   * @since 1.0.0
   * @return array
   *
   */
  protected function igs_delete_post_meta() {
    $post_meta = get_post_meta( $this->igs_get_id() );

    if ( ! $post_meta )
      return;

    foreach ($post_meta as $meta_key => $meta_value) {
      if ( strpos( $meta_key, 'igs_' ) === 0 ) {
        delete_post_meta( $this->igs_get_id(), $meta_key );
      }
    }
  }

  /**
   * Update Post Meta Fields.
   *
   * @since 1.0.0
   * @return array
   *
   */
  protected function igs_update_post_meta() { }

  /**
   * Update Term.
   *
   * @since 1.0.0
   * @return array
   *
   */
  protected function igs_update_term() { }


  /**
   * Get the post if ID is passed, otherwise the product is new and empty.
   *
   * @param int|IGS_Post|object $post Product to init.
   *
   */
  public function __construct( $post = 0 ) {

    if ( ! $post )
      return;

    $cache_key     = 'igs_post_' . ( is_object( $post ) ? $post->ID : $post ) . '_';
    $cached_object = wp_cache_get( $cache_key, 'igs_post' );

    if ( false !== $cached_object && $cached_object instanceof self ) {
      $this->igs_set_id( $cached_object->id );
      $this->igs_set_data( $cached_object->data );
      $_post = get_post($cached_object->id);
      $this->igs_set_prop('post_content', $_post->post_content);
    } else {
      if ( is_numeric( $post ) && $post > 0 ) {
        $this->igs_set_id( $post );
        $post = get_post( $post );
        $this->igs_set_data( $post );
      } elseif ( $post instanceof self ) {
        $this->igs_set_id( $post->id );
        $this->igs_set_data( $post->data );
      } elseif ( ! empty( $post ) ) {
        $this->igs_set_id( $post->ID );
        $this->igs_set_data( $post );
      }

      $this->default_meta_data = apply_filters( 'igs_post_default_meta_data', $this->default_meta_data );
      $this->meta_data         = apply_filters( 'igs_post_meta_data', $this->meta_data );

      $this->igs_set_default_meta_data();

      if ( $blocks = $this->igs_get_block() ) {
        foreach ( $blocks as $key => $value ) {
          $this->igs_set_prop( $key , $value );
        }
      }

      $this->igs_set_meta_data();
      $this->igs_update_post_meta();
      $this->igs_update_term();
      $base_expiration = MONTH_IN_SECONDS;
      $random_offset   = mt_rand( 0, DAY_IN_SECONDS );
      $expiration_time = $base_expiration + $random_offset;

      $content = $this->igs_get_content();
      $this->delete_prop('post_content');
      wp_cache_set( $cache_key, $this, 'igs_post', $expiration_time );
      $this->igs_set_prop('post_content', $content);
    }
  }

  /*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Functions for getting review data.
  */

  /**
   * Prefix for action and filter hooks on data.
   *
   * @since  1.0.0
   * @return string
   *
   */
  protected function igs_get_hook_prefix( $key ) {

    return 'igs_get_post_' . $key;

  }

  /**
   * Set data.
   *
   * @since 1.0.0
   * @param string $prop.
   * @return array|mixed;
   *
   */
  public function igs_get_data() {

    return $this->data;

  }

  /**
   * Set data.
   *
   * @since 1.0.0
   * @param string $prop.
   * @return string|array|mixed;
   *
   */
  public function igs_get_prop( $prop ) {

    $value = null;

    if ( is_object( $this->data ) && property_exists( $this->data, $prop ) ) {
      $value = $this->data->$prop;

      $value = apply_filters( $this->igs_get_hook_prefix( $prop ) , $value, $this );
    }

    return $value;

  }

  /**
   * Unsets a property from the object's data.
   *
   * @since 1.0.0
   * @param string $prop Name of prop to unset.
   *
   */
  public function delete_prop( $prop ) {
    if ( property_exists( $this->data, $prop ) ) {
      unset( $this->data->$prop );
    }
  }

  /**
   * Returns the unique ID for this object.
   *
   * @since  1.0.0
   * @return int
   *
   */
  public function igs_get_id() {

    return $this->igs_get_prop('ID');

  }

  /**
   * Returns the Parent ID for this object.
   *
   * @since  1.0.0
   * @return int
   *
   */
  public function igs_get_parent_id() {

    return $this->igs_get_prop('post_parent');

  }

  /**
   * Returns get post title for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_post_title() {

    return $this->igs_get_prop( 'post_title' );

  }

  /**
   * Returns get post name for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_post_name() {

    return $this->igs_get_prop( 'post_name' );

  }

  /**
   * Returns the post permalink url for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_permalink_url() {

    if ( is_null( $permalink_url = $this->igs_get_prop( 'permalink_url' ) ) ) {
      $permalink_url = get_the_permalink( $this->igs_get_id() );
    }

    return apply_filters( 'igs_post_permalink',  $permalink_url );

  }

  /**
   * Returns has post featured image for this object.
   *
   * @since  1.0.0
   * @return bool Whether the post has an image attached.
   *
   */
  public function igs_has_thumbnail( ) {

    if ( is_null( $igs_has_thumbnail = $this->igs_get_prop( 'igs_has_thumbnail' ) ) ) {
      $igs_has_thumbnail = has_post_thumbnail( $this->igs_get_id() );
    }

    return apply_filters( 'igs_post_igs_has_thumbnail', $igs_has_thumbnail );

  }

  /**
   * Returns post featured image id for this object.
   *
   * @since  1.0.0
   * @return int|false Post thumbnail ID (which can be 0 if the thumbnail is not set), or false if the post does not exist.
   *
   */
  public function igs_get_thumbnail_id() {

    if ( ! $this->igs_has_thumbnail() ) {
      $thumbnail_id = null;
    } elseif ( ! $thumbnail_id = $this->igs_get_prop( 'thumbnail_id' ) ) {
      $thumbnail_id = get_post_thumbnail_id( $this->igs_get_id() );
    }

    return apply_filters( 'igs_post_thumbnail_id', $thumbnail_id );

  }

  /**
   * Returns post featured image url for this object.
   *
   * @since  1.0.0
   * @return string|null Post thumbnail URL or false if the post does not exist.
   *
   */
  public function igs_get_thumbnail_url() {

    if ( ! $this->igs_has_thumbnail() )
      $thumbnail_url = null;
    else
      $thumbnail_url = get_the_post_thumbnail_url( $this->igs_get_id() );

    return apply_filters( 'igs_post_thumbnail_url', $thumbnail_url );

  }

  /**
   * Returns the post content for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_content() {

    return $this->igs_get_prop( 'post_content' );

  }

  /**
   * Returns the author for this object.
   *
   * @since  1.0.0
   * @return int
   *
   */
  public function igs_get_post_author() {

    return $this->igs_get_prop('post_author');

  }

  /**
   * Returns the status for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_status() {

    return $this->igs_get_prop('post_status');

  }

  /**
   * Returns the status for this object.
   *
   * @since  1.0.0
   * @return boolean
   *
   */
  public function igs_get_post_type() {

    return $this->igs_get_prop('post_type');

  }

  /**
   * Returns the post publish date for this object.
   *
   * @since  1.0.0
   * @return string publish date.
   *
   */
  public function igs_get_publish_date( $format = null ) {

    if ( ! $format ) {
      $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    }

    $timestamp = strtotime( $this->igs_get_prop( 'post_date' ) );
    return date_i18n( $format, $timestamp );

  }

  /**
   * Returns the post modified date for this object.
   *
   * @since  1.0.0
   * @return string modified date.
   *
   */
  public function igs_get_modified_date( $format = null ) {

    if ( ! $format ) {
      $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    }

    $timestamp = strtotime( $this->igs_get_prop( 'post_modified' ) );
    return date_i18n( $format, $timestamp );

  }

  /**
	 * Get and store terms from a taxonomy.
	 *
	 * @since  1.0.0
	 * @param  IGS_POST|integer $object IGS_POST object or object ID.
	 * @param  string          $taxonomy Taxonomy name e.g. category.
	 * @return array of terms
   *
	 */
	protected function igs_get_term_ids( $object, $taxonomy = 'category' ) {
		$object_id = is_numeric( $object ) ? $object : $object->igs_get_id();

    $term_ids = wp_get_object_terms( $object_id, $taxonomy, array(
      'fields'                 => 'ids',
      'update_term_meta_cache' => false
    ) );

    if ( is_wp_error( $term_ids ) ) {
      return array();
    }

    return $term_ids;
	}
  /*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting review data.
  */

  /**
   * Set ID.
   *
   * @since 1.0.0
   * @param int $id ID.
   *
   */
  public function igs_set_id( $id ) {

    $this->id = absint( $id );

  }

  /**
   * Set data.
   *
   * @since 1.0.0
   * @param object|WP_POST $post.
   *
   */
  public function igs_set_data( $post ) {

    $this->data = $post;

  }

  /**
   * Sets a prop for a setter method.
   *
   * This stores changes in a special array so we can track what needs saving
   * the the DB later.
   *
   * @since 3.0.0
   * @param string $prop Name of prop to set.
   * @param mixed  $value Value of the prop.
   *
   */
  protected function igs_set_prop( $prop, $value ) {

    $this->data->$prop = $value;

  }

  /**
	 * Set a collection of props in one go, collect any errors, and return the result.
	 * Only sets using public methods.
	 *
	 * @since  1.0.0
	 *
	 * @param array  $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 *
	 */
	public function igs_set_props( $props ) {
		foreach ( $props as $prop => $value ) {

      $setter = "set_$prop";

      if ( is_callable( array( $this, $setter ) ) ) {
        $this->{$setter}( $value );
      }
		}
	}

  /*
	|--------------------------------------------------------------------------
	| Outputs
	|--------------------------------------------------------------------------
  */

  /**
   * Determines whether the object has a public status.
   *
   * @since  1.0.0
   * @return boolean
   *
   */
  public function igs_is_published() {

    return $this->igs_get_status() === 'publish';

  }

  /**
   * Determines whether the object has a draft status.
   *
   * @since  1.0.0
   * @return boolean
   *
   */
  public function igs_is_draft() {

    return $this->igs_get_status() === 'draft';

  }

  /**
   * Determines whether the object has a draft status.
   *
   * @since  1.0.0
   * @return boolean
   *
   */
  public function igs_is_archived() {

    return $this->igs_get_status() === 'archived';

  }

  /**
   * Returns the post permalink for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_permalink( $link_text, $attrs = array() ) {

    $attrs = wp_parse_args($attrs, array(
      'aria-label' => wp_sprintf( esc_attr_x( 'Go to %1$s', 'aria-label', 'igaming' ), $this->igs_get_prop( 'post_title' ) )
    ));

    return apply_filters( 'igs_get_post_permalink',
      wp_sprintf( '<a href="%1$s" %3$s>%2$s</a>',
        $this->igs_get_permalink_url(),
        $link_text,
        igs_html_attributes($attrs)
      ),
      $this->igs_get_permalink_url(),
      $link_text,
      $attrs
    );
  }

  /**
   * Returns get post title for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_title( $tag = 'h3', $attr = array() ) {

    return apply_filters( 'igs_get_post_title',
      wp_sprintf( '<%1$s %3$s>%2$s</%1$s>',
        $tag,
        $this->igs_get_post_title(),
        igs_html_attributes($attr),
      ),
      $tag,
      $this->igs_get_post_title(),
      $attr
    );

  }

  /**
   * Returns get post title for this object.
   *
   * @since  1.0.0
   * @return string
   *
   */
  public function igs_get_title_link( $tag = 'h3', $attr = array(), $link_attr = array() ) {

    return apply_filters( 'igs_get_post_title',
      sprintf( '<%1$s %3$s>%2$s</%1$s>',
        $tag,
        $this->igs_get_permalink( $this->igs_get_post_title(), $link_attr ),
        igs_html_attributes($attr),
      ),
      $tag,
      $this->igs_get_post_title(),
      $attr
    );

  }

  /**
   * Returns the post featured image for this object.
   *
   * @since  1.0.0
   * @param $size string|int[] Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order).
   * @param $attr Query string or array of attributes.
   * @return string The post thumbnail image tag.
   *
   */
  public function igs_get_thumbnail( $size = 'full', $attrs = array(), $placeholder = true ) {

    $image = '';

    if ( $thumbnail_id = $this->igs_get_thumbnail_id() ) {
      $image = wp_get_attachment_image( $thumbnail_id, $size, false, $attrs );
    } elseif ( $this->igs_get_parent_id() ) {
      if ( $parent = new IGS_Post( $this->igs_get_parent_id() ) ) {
        $image = $parent->igs_get_thumbnail( $size, $attrs, $placeholder );
      }
    }

    if ( ! $image && $placeholder ) {
      $image = igs_placeholder_img( $size, $attrs );
    }

    return apply_filters( 'igs_post_get_thumbnail', $image, $this, $size, $attrs, $placeholder, $image );

  }

  /**
   * Returns the post featured image for this object.
   *
   * @since  1.0.0
   * @param $size string|int[] Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order).
   * @param $attr Query string or array of attributes.
   * @return string The post thumbnail image tag.
   *
   */
  public function igs_get_thumbnail_link( $size = 'full', $link_attr = array(), $attr = array(), $placeholder = true ) {

    if ( ! $image = $this->igs_get_thumbnail( $size, $attr, $placeholder ) )
      return;

    return $this->igs_get_permalink( $image, $link_attr );

  }


  /*
	|--------------------------------------------------------------------------
	| Static Functions
	|--------------------------------------------------------------------------
  */
}
