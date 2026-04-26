<?php

  defined( 'ABSPATH' ) || exit;

  global $subscription;

  /** @var \IGS_CS_Subscription $subscription */

  // Check if the subscription is a valid IGS Subscription and ensure its visibility before proceeding.
  if ( ! is_a( $subscription, IGS_CS_Subscription::class ) ) {
    return;
  }

  $customer        = $subscription->igs_set_customer();
  $returned_orders = $customer->igs_order_returned_count();

?>

<div class="flc-3">
  <div class="d-f f-c gy-15 p-15 h-100 b-1 bc-2 br-10 ps-r">
    <div class="d-f ai-c gx-10">
      <span class="foc f-a w-50 h-50 fs-20 bg-2 ff-h tc-w fw-bl br-c"><?php echo $subscription->igs_get_abbr(); ?></span>

      <div class="f-1 d-f f-c gy-5">
        <h2 class="fs-16"><?php echo $subscription->get_formatted_billing_full_name(); ?></h2>

        <p class="fw-b fs-14"><?php echo $subscription->igs_get_days_label(); ?></p>
      </div>
    </div>

    <?php echo $subscription->igs_get_birthday_badge(); ?>

    <div class="d-f f-c gy-10">
      <p class="fw-b fs-14"><?php echo $customer->igs_get_billing_email(); ?></p>
      <p class="fw-b fs-14"><?php echo $subscription->igs_get_billing_phone(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('#', 'igs-client-system'); ?></p>
      <p class="f-1 ta-r fw-sb"><?php echo $subscription->get_id(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Status', 'igs-client-system'); ?></p>
      <p class="f-1 ta-r fw-sb"><?php echo $subscription->igs_get_status_name(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Next', 'igs-client-system'); ?>:</p>
      <p class="f-1 ta-r fw-sb"><?php echo $subscription->igs_get_next_date(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Fixed renewal day', 'igs-client-system'); ?>:</p>
      <?php if ( '1' === $subscription->get_meta( '_igs_fixed_renewal_day' ) ) : ?>
        <p class="f-1 ta-r fw-sb"><?php _e('Yes', 'igs-client-system'); ?></p>
      <?php else: ?>
        <p class="f-1 ta-r fw-sb"><?php _e('No', 'igs-client-system'); ?></p>
      <?php endif; ?>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Last', 'igs-client-system'); ?>:</p>
      <p class="f-1 ta-r fw-sb"><?php echo $subscription->igs_get_last_order_date(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <pa class="f-1"><?php _e('Subscriber from', 'igs-client-system'); ?>:</pa
      <p cla ta-rss="f-1 fw-sb"><?php echo $subscription->igs_get_months_subscriber(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Orders', 'igs-client-system'); ?>:</p>
      <p class="f-1 ta-r fw-sb"><?php echo count( $subscription->get_related_orders() ); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14 fw-b tt-u" <?php echo $returned_orders ? 'style="color: red"' : ''; ?>>
      <p class="f-a"><?php _e('Uncollected Orders', 'igs-client-system'); ?>:</p>
      <p class="f-1 ta-r"><?php echo $returned_orders; ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Delivery', 'igs-client-system'); ?>:</p>
      <p class="f-1 ta-r fw-sb"><?php echo $subscription->get_shipping_method(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Payment', 'igs-client-system'); ?>:</p>
      <p class="f-1 ta-r fw-sb"><?php echo $subscription->get_payment_method_title(); ?></p>
    </div>

    <?php
      $is_prepaid    = '1' === $subscription->get_meta( '_igs_prepaid' );
      $prepaid_until = $subscription->get_meta( '_igs_prepaid_until' );
      $until_dt      = $prepaid_until ? DateTime::createFromFormat( 'd.m.Y', $prepaid_until ) : false;
      $prepaid_valid = $until_dt && ( (int) $until_dt->format('Ym') > (int) ( new DateTime() )->format('Ym') );
    ?>
    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Prepaid', 'igs-client-system'); ?>:</p>
      <?php if ( $is_prepaid ) : ?>
        <?php if ( $prepaid_valid ) : ?>
          <p class="f-1 ta-r fw-sb" style="color:green;"><?php echo esc_html( sprintf( __( 'Until %s', 'igs-client-system' ), $prepaid_until ) ); ?></p>
        <?php else : ?>
          <p class="f-1 ta-r fw-sb" style="color:red;"><?php _e( 'Expired', 'igs-client-system' ); ?></p>
        <?php endif; ?>
      <?php else: ?>
        <p class="f-1 ta-r fw-sb"><?php _e('No', 'igs-client-system'); ?></p>
      <?php endif; ?>
    </div>

    <div class="d-f ai-c gx-5 mt-a">
      <?php echo $subscription->igs_get_renew_button(); ?>
      <?php echo $subscription->igs_get_edit_button(); ?>
    </div>
  </div>
</div>


