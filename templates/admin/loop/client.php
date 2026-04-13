<?php

  defined( 'ABSPATH' ) || exit;

  $user = new IGS_CS_User( $user_id );

  /** @var \IGS_CS_User $user */

  // Check if the user is a valid IGS user and ensure its visibility before proceeding.
  if ( ! is_a( $user, IGS_CS_User::class ) ) {
    return;
  }

?>

<div class="flc-3">
  <div class="d-f f-c gy-15 p-15 h-100 b-1 bc-2 br-10 ps-r">
    <div class="d-f ai-c gx-10">
      <span class="foc f-a w-50 h-50 fs-20 bg-2 ff-h tc-w fw-bl br-c"><?php echo $user->igs_get_abbr(); ?></span>

      <div class="f-1 d-f f-c gy-5">
        <h2 class="fs-16"><?php echo $user->igs_get_name(); ?></h2>
      </div>
    </div>

    <div class="d-f f-c gy-10">
      <p class="fw-b fs-14"><?php echo $user->igs_get_email(); ?></p>
      <p class="fw-b fs-14"><?php echo $user->igs_get_billing_phone(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('ID', 'igs-client-system'); ?></p>
      <p class="f-1 ta-r fw-sb"><?php echo $user->igs_get_id(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Price list', 'igs-client-system'); ?></p>
      <p class="f-1 ta-r fw-sb"><?php echo $user->igs_get_price_list_label(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Active subscriber', 'igs-client-system'); ?></p>
      <p class="f-1 ta-r fw-sb"><?php echo $user->igs_has_active_subscription(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Orders', 'igs-client-system'); ?></p>
      <p class="f-1 ta-r fw-sb"><?php echo $user->igs_order_count(); ?></p>
    </div>

    <div class="d-f ai-c g-5 fs-14">
      <p class="f-a"><?php _e('Uncollected Orders', 'igs-client-system'); ?></p>
      <p class="f-1 ta-r fw-sb"><?php echo $user->igs_order_returned_count(); ?></p>
    </div>

    <?php echo $user->igs_get_edit_button(); ?>

  </div>
</div>


