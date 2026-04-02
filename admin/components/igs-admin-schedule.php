<?php
/**
 * The Admin Schedule Component.
 *
 * @package    IGS
 * @subpackage IGS/Admin/Components
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

class IGS_CS_Schedule {

  /**
   * The query handler instance.
   *
   * @since 1.0.0
   * @var IGS_CS_Query|null
   */
  protected $igs_cs_query = null;

  /**
   * Singleton instance.
   */
  private static $_instance = null;

  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() {

    if ( is_null( $this->igs_cs_query ) ) {

      $this->igs_cs_query = new IGS_CS_Subscriptions_Query( $this->igs_get_param() );

    }

    return $this->igs_cs_query;

  }

  protected function igs_get_param() {

    $defaults = array(
      'igs_type'      => '3',
      'igs_results'   => '',
      'igs_per_page'  => '',
      'igs_next_date' => 'this_month',
      'igs_orderby'   => 'ASC'
    );

    return wp_parse_args( $_GET, $defaults );

  }

  /**
   * Get the query handler and execute the search.
   *
   * @since  1.0.0
   * @return IGS_CS_Subscriptions_Query
   */
  public function get_query() {

    return $this->igs_cs_query->igs_get_query();

  }

  public function get_days_subscription() {
    $schedule = array();

    if ( $this->get_query()->have_posts() ) {
      while( $this->get_query()->have_posts() ) : $this->get_query()->the_post();
        global $subscription;
        /** @var \IGS_CS_Subscription $subscription */

        if ( ! is_a( $subscription, IGS_CS_Subscription::class ) ) {
          continue;
        }

        $day = $subscription->igs_get_next_date('j');

        $schedule[$day][] = $subscription;
      endwhile;

      wp_reset_query();
    }

    return $schedule;
  }
}
