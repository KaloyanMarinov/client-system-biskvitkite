<?php
/**
 * Constants Manager Class.
 *
 * This class acts as a wrapper for plugin constants, allowing for dynamic 
 * definition, retrieval, and filtering of constant values.
 *
 * @since      1.0.0
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/includes
 */
class IGS_CS_Constants {

  /**
   * Checks if a constant has been set and has a truthy value.
   *
   * @since  1.0.0
   * @access public
   * @static
   *
   * @param  string $name The name of the constant.
   * @return bool True if the constant is defined and truthy.
   */
  public static function is_true( $name ) {
    return self::is_defined( $name ) && self::get_constant( $name );
  }

  /**
   * Checks if a constant has been set in the Manager or defined via define().
   *
   * @since  1.0.0
   * @access public
   * @static
   *
   * @param  string $name The name of the constant.
   * @return bool True if defined in the manager or globally.
   */
  public static function is_defined( $name ) {
    return defined( $name );
  }

  /**
   * Retrieves a constant value from the Manager, global constants, or filters.
   *
   * @since  1.0.0
   * @access public
   * @static
   *
   * @param  string $name The name of the constant.
   * @return mixed  The value of the constant or null if not found.
   */
  public static function get_constant( $name ) {

    if ( defined( $name ) ) {
      return constant( $name );
    }

    /**
     * Filters the default value of a constant if it is not defined.
     *
     * @since 1.0.0
     *
     * @param mixed  $value The default value (null).
     * @param string $name  The constant name being requested.
     */
    return apply_filters( 'igs_cs_constant_default_value', null, $name );
  }
  
}