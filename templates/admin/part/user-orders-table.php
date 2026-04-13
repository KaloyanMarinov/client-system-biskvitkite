<?php

defined( 'ABSPATH' ) || exit;

?>

<table class="table table-order">
  <thead>
    <tr>
      <th><?php _e('Order #', 'igs-client-system') ?></th>
      <th><?php _e('Date', 'igs-client-system') ?></th>
      <th><?php _e('Type order', 'igs-client-system') ?></th>
      <th class="ta-c"><?php _e('Status', 'igs-client-system') ?></th>
      <th class="ta-c"><?php _e('Total', 'igs-client-system') ?></th>
      <th class="ta-c"><?php _e('Actions', 'igs-client-system') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if ( ! empty( $orders ) ) : ?>
      <?php foreach ( $orders as $order ) :
        $order_id      = $order->get_id();
        $status_object = wc_get_order_status_name( $order->get_status() );
        $edit_url      = $order->get_edit_order_url();
        $is_renewal    = wcs_order_contains_renewal( $order );
        $is_parent     = wcs_order_contains_subscription( $order );
      ?>
        <tr>
          <td><strong>#<?php echo $order_id; ?></strong></td>
          <td><?php echo $order->get_date_created()->date( get_option( 'date_format' ) ); ?></td>
          <td>
            <?php
              if ( $is_renewal ) {
                _e('Renewal', 'igs-client-system');
              } elseif ( $is_parent ) {
                _e('New subscription', 'igs-client-system');
              } else {
                _e('One-time purchase', 'igs-client-system');
              } ?>
          </td>
          <td class="ta-c">
            <span class="order-status status-<?php echo esc_attr( $order->get_status() ); ?>"><?php echo esc_html( $status_object ); ?></span>
          </td>
          <td class="ta-c"><strong><?php echo $order->get_formatted_order_total(); ?></strong></td>
          <td class="ta-c"><a href="<?php echo $edit_url; ?>" class="fw-b"><?php _e('Review', 'igs-client-system'); ?></a></td>
        </tr>
      <?php endforeach; ?>
    <?php else : ?>
      <tr>
        <td colspan="5"><?php _e('The customer has no orders placed.', 'igs-client-system'); ?></td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
