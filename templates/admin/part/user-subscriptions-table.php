<?php

defined( 'ABSPATH' ) || exit;

?>

<table class="table table-order">
  <thead>
    <tr>
      <th><?php _e('Subscription #', 'igs-client-system') ?></th>
      <th><?php _e('Date', 'igs-client-system') ?></th>
      <th class="ta-c"><?php _e('Status', 'igs-client-system') ?></th>
      <th class="ta-c"><?php _e('Total', 'igs-client-system') ?></th>
      <th class="ta-c"><?php _e('Actions', 'igs-client-system') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if ( $subscriptions->have_posts() ) : ?>
      <?php
        while( $subscriptions->have_posts() ) : $subscriptions->the_post();
          global $subscription;

          /** @var \IGS_CS_Subscription $subscription */

          // Check if the subscription is a valid IGS Subscription and ensure its visibility before proceeding.
          if ( ! is_a( $subscription, IGS_CS_Subscription::class ) ) {
            return;
          }

          $edit_url = $subscription->get_edit_order_url();
      ?>
        <tr>
          <td><strong>#<?php echo $subscription->get_id(); ?></strong></td>
          <td><?php echo $subscription->igs_get_start_date(); ?></td>
          <td class="ta-c fs-21"><?php echo $subscription->igs_get_status_name(); ?></td>
          <td class="ta-c"><strong><?php echo $subscription->get_formatted_order_total(); ?></strong></td>
          <td class="ta-c"><a href="<?php echo $edit_url; ?>" class="fw-b"><?php _e('Review', 'igs-client-system'); ?></a></td>
        </tr>
      <?php endwhile; ?>
    <?php else : ?>
      <tr>
        <td colspan="5"><?php _e('The customer has no subscriptions placed.', 'igs-client-system'); ?></td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
