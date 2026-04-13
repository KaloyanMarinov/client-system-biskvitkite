<?php
$order_id      = $order->get_order_number();
$status_object = wc_get_order_status_name( $order->get_status() );
$edit_url      = $order->get_edit_order_url();
$is_renewal    = wcs_order_contains_renewal( $order );
$is_parent     = wcs_order_contains_subscription( $order );
?>
<tr>
  <td><strong>#<?php echo $order->get_order_number(); ?></strong></td>
  <td><?php echo $order->get_date_created()->date_i18n( get_option( 'date_format' ) ); ?></td>
  <td><?php echo $order->get_meta( '_relationship' ); ?></td>
  <td class="ta-c">
    <span class="order-status status-<?php echo esc_attr( $order->get_status() ); ?>"><?php echo esc_html( $status_object ); ?></span>
  </td>
  <td class="ta-c"><strong><?php echo $order->get_formatted_order_total(); ?></strong></td>
  <td class="ta-c"><a href="<?php echo $edit_url; ?>" class="fw-b"><?php _e('Review', 'igs-client-system'); ?></a></td>
</tr>
