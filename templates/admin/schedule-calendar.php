<?php
  defined( 'ABSPATH' ) || exit;

  $module = IGS_CS()->admin()->subscriptions()->get_schedule();

  /** @var \IGS_CS_List_Subscription $module */

  $selected_period = isset( $_GET['igs_next_date'] ) ? sanitize_text_field( $_GET['igs_next_date'] ) : 'this_month';

  if ( 'next_month' === $selected_period ) {
    $schedule      = $module->get_days_subscription();
    $days_in_month = date( 't', strtotime( 'first day of next month' ) );
    $month_ts      = strtotime( 'first day of next month' );
  } else {
    $schedule      = $module->get_days_subscription();
    $days_in_month = date( 't' );
    $month_ts      = time();
  }
?>

<main class="igs-main pb-50">
  <?php
    /**
     * igs_cs_before_content hook.
     *
     */
    do_action('igs_cs_before_content');
  ?>

  <div class="d-f ai-c jc-sb mb-30">
    <h2 class="h3">
      <?php _e( 'Renewal schedule', 'igs-client-system' ); ?>:
      <?php echo date_i18n( 'F Y', $month_ts ); ?>
    </h2>

    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="d-f ai-c gx-10">
      <input type="hidden" name="page" value="<?php echo esc_attr( IGS_CS()->admin()->menus()->get_schedule_slug() ); ?>">
      <select name="igs_next_date" class="field" onchange="this.form.submit()">
        <option value="this_month" <?php selected( $selected_period, 'this_month' ); ?>><?php _e( 'This month', 'igs-client-system' ); ?></option>
        <option value="next_month" <?php selected( $selected_period, 'next_month' ); ?>><?php _e( 'Next Month', 'igs-client-system' ); ?></option>
      </select>
    </form>
  </div>

  <table class="w-100 va-b">
    <thead>
      <th class="px-10 py-15 bg-2 tc-w tt-u b-1 bc-2 w-200"><?php _e('Date', 'igs-client-system'); ?></th>
      <th class="px-10 py-15 bg-2 tc-w tt-u b-1 bc-2"><?php _e('Subscriptions for renewal', 'igs-client-system'); ?></th>
    </thead>
    <tbody>
      <?php
        for ( $day = 1; $day <= $days_in_month; $day++ ) {
          $day_ts           = mktime( 0, 0, 0, date( 'n', $month_ts ), $day, date( 'Y', $month_ts ) );
          $current_date_str = date( 'd.m.Y', $day_ts );
          $has_subs         = isset( $schedule[ $day ] );
      ?>
        <tr class="<?php echo $day % 2 == 0 ? 'bg-5' : null ?>">
          <td class="px-10 py-15 b-1 bc-b fw-b w-200 ws-nw va-t">
            <?php echo $current_date_str; ?> - <?php echo date_i18n( 'l', $day_ts ); ?>
          </td>

          <td class="px-10 py-15 b-1 bc-b fw-b">
            <?php if ( $has_subs ) { ?>
              <ul class="d-f f-c gy-10">
                <?php foreach ( $schedule[$day] as $sub ) { ?>
                  <li>
                    <?php
                      echo wp_sprintf( '<a %2$s>%1$s</a>',
                        wp_sprintf( '#%d %s', $sub->get_id(), $sub->get_formatted_billing_full_name() ),
                        igs_cs_html_attributes(array(
                          'href' => admin_url( 'admin.php?page=igs-subscriptions&action=edit&id=' . $sub->get_id() ),
                          'class' => 'tc-h-1 td-h-u'
                        ))
                      );
                    ?>
                  </li>
                <?php } ?>
              </ul>
            <?php } else { ?>
              <span><?php _e('No scheduled renewals', 'igs-client-system'); ?></span>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</main>
