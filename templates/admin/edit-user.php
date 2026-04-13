<?php

  defined( 'ABSPATH' ) || exit;

  $module = IGS_CS()->admin()->subscriptions()->get_users();

  /** @var \IGS_CS_User $user */

  // Check if the user is a valid IGS user and ensure its visibility before proceeding.
  if ( ! is_a( $user, IGS_CS_User::class ) ) {
    return;
  }

  $first_name = $temp_data ? $temp_data['first_name'] : $user->igs_get_first_name();
  $last_name  = $temp_data ? $temp_data['last_name'] : $user->igs_get_last_name();
  $phone      = $temp_data ? $temp_data['billing_phone'] : $user->igs_get_billing_phone();
  $email      = $temp_data ? $temp_data['email'] : $user->igs_get_email();
  $price_list = $temp_data ? $temp_data['price_list'] : $user->igs_get_price_list();

?>

<main class="igs-main pb-50">
  <?php
    /**
     * igs_cs_before_content hook.
     *
     * @hooked igs_cs_header_template - 10
     */
    do_action('igs_cs_before_content');
  ?>

  <div class="flr jc-c">
    <div class="flc-8 d-f f-c gy-30">
      <?php $module->igs_display_admin_notices(); ?>

      <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" class="d-f f-c gy-25">
        <input type="hidden" name="action" value="igs_save_user_data">
        <input type="hidden" name="user_id" value="<?php echo $user->igs_get_id(); ?>">
        <?php
          wp_nonce_field( 'igs_user_save_action', 'igs_user_save_nonce' );
          wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );
        ?>

        <h3><?php _e('Client #', 'igs-client-system'); ?><?php echo $user->igs_get_id(); ?></h3>

        <div class="d-f f-c fw-b mb-10 g-15">
          <p><?php _e('Date registered', 'igs-client-system'); ?>: <?php echo $user->date_registered(); ?></p>

          <p><?php _e('Price list', 'igs-client-system'); ?>: <?php echo $user->igs_get_price_list_label(); ?></p>

          <?php if ( $user->igs_has_subscription() ) { ?>
            <p><?php _e('Active subscriber', 'igs-client-system'); ?>: <?php echo $user->igs_has_active_subscription(); ?></p>
          <?php } else { ?>
            <p><?php _e('No subscription', 'igs-client-system'); ?></p>
          <?php } ?>

          <p><?php _e('Orders', 'igs-client-system'); ?>: <?php echo $user->igs_order_count(); ?></p>
        </div>

        <h3><?php _ex('Personal data', 'edit', 'igs-client-system') ?></h3>

        <div class="flr gy-25">
          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('First Name', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="first_name" id="first_name" value="<?php echo $first_name; ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Last Name', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="last_name" id="last_name" value="<?php echo $last_name; ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Phone number', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="billing_phone" id="billing_phone" value="<?php echo $phone; ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Email address', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="email" id="email" value="<?php echo $email; ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Price List', 'igs-client-system') ?></p>
            <?php
              $prices = get_terms(array(
                'taxonomy'   => 'product_prices_list',
                'hide_empty' => false,
                'fields'     => 'id=>name'
              ));

              $options = array('0' => __('Standard', 'igs-client-system' )) + $prices;
              woocommerce_wp_select(array(
                'id'      => 'price_list',
                'class'   => 'field',
                'label'   => '',
                'value'   => $price_list,
                'options' => $options,
              ), );
            ?>
          </div>
        </div>

        <?php submit_button( __('Save changes', 'igs-client-system'), 'bg-1 bg-h-3 tc-w tc-h-6' ); ?>
      </form>

      <h3><?php _ex('Orders', 'edit', 'igs-client-system') ?></h3>

      <?php $user->igs_get_orders_table(); ?>

      <h3><?php _ex('Subscriptions', 'edit', 'igs-client-system') ?></h3>

      <?php $user->igs_get_subscription_table(); ?>

    </div>
  </div>

</main>
