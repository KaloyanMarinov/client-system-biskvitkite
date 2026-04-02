<?php

  defined( 'ABSPATH' ) || exit;

  /** @var \IGS_CS_Subscription $subscription */

  // Check if the subscription is a valid IGS Subscription and ensure its visibility before proceeding.
  if ( ! is_a( $subscription, IGS_CS_Subscription::class ) ) {
    return;
  }

  $customer = $subscription->igs_set_customer();

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

  <div class="flr">
    <div class="flc-8 d-f f-c gy-30">
      <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" class="d-f f-c gy-25">
        <input type="hidden" name="action" value="igs_save_subscription_data">
        <input type="hidden" name="subscription_id" value="<?php echo $subscription->get_id(); ?>">
        <?php
          wp_nonce_field( 'igs_subscription_save_action', 'igs_subscription_save_nonce' );
          wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );
          wp_nonce_field( 'igs_subscription_save_action', 'igs_subscription_save_nonce' );
        ?>

        <div class="d-f ai-c">
          <div class="f-1 d-f ai-c g-10">
            <h3><?php _e('Subscription #', 'igs-client-system'); ?> <?php echo $subscription->get_id(); ?></h3>
            <p class="f-a fw-sb"><?php echo $subscription->igs_get_status_name(); ?></p>
          </div>

          <div class="f-a">
            <?php echo $subscription->igs_get_renew_button(); ?>
          </div>
        </div>

        <div class="d-f fw-b mb-10 g-15">
          <p><?php _e('Last renew', 'igs-client-system'); ?>: <?php echo $subscription->igs_get_last_order_date(); ?></p>

          <p><?php _e('Subscriber from', 'igs-client-system'); ?>: <?php echo $subscription->igs_get_months_subscriber(); ?></p>
        </div>

        <div class="flr gy-25">
          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Client', 'igs-client-system') ?>:</p>
            <?php
							$user_string = '';
							$user_id     = '';

							if ( $subscription->get_user_id() && ( false !== get_userdata( $subscription->get_user_id() ) ) ) {
								$user_id     = absint( $subscription->get_user_id() );
								$user        = get_user_by( 'id', $user_id );
								$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')';
							}

							WCS_Select2::render(
								array(
									'class'       => 'wc-customer-search',
									'name'        => 'customer_user',
									'id'          => 'customer_user',
									'placeholder' => esc_attr__( 'Search for a customer&hellip;', 'woocommerce-subscriptions' ),
									'selected'    => $user_string,
									'value'       => $user_id,
								)
							);
						?>
          </div>

          <div class="flc-u-md-3">
            <p class="fw-b mb-10"><?php _e('Subscription status', 'igs-client-system') ?>:</p>
            <select id="order_status" name="order_status" class="field">
              <?php
              $statuses = wcs_get_subscription_statuses();
              foreach ( $statuses as $status => $status_name ) {
                if ( ! $subscription->can_be_updated_to( $status ) && ! $subscription->has_status( str_replace( 'wc-', '', $status ) ) ) {
                  continue;
                }
                echo '<option value="' . esc_attr( $status ) . '" ' . selected( $status, 'wc-' . $subscription->get_status(), false ) . '>' . esc_html( $status_name ) . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="flc-u-md-3">
            <p class="fw-b mb-10"><?php _e('Start date', 'igs-client-system') ?>:</p>
            <input type="text" name="start_timestamp_utc" class="field js-datepicker" value="<?php echo $subscription->igs_get_start_date( 'd.m.Y' ); ?>">
          </div>

          <div class="flc-u-md-3">
            <p class="fw-b mb-10"><?php _e('Next payment', 'igs-client-system') ?>:</p>
            <input type="text" name="next_payment_timestamp_utc" class="field js-datepicker" value="<?php echo $subscription->igs_get_next_date( 'd.m.Y' ); ?>">
          </div>

          <div class="flc-u-md-3">
            <p class="fw-b mb-10"><?php _e('End date', 'igs-client-system') ?>:</p>

            <input type="text" name="end_timestamp_utc" class="field js-datepicker" value="<?php echo $subscription->igs_get_end_date( 'U' ) ?: ''; ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _ex('Payment', 'edit', 'igs-client-system') ?>:</p>
            <div class="flr">
              <div class="flc-6">
                <select id="_billing_interval" name="_billing_interval" class="field">
                  <?php
                    $options = wcs_get_subscription_period_interval_strings();
                    foreach ( $options as $value => $name ) {
                      echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $subscription->get_billing_interval(), false ) . '>' . esc_html( $name ) . '</option>';
                    }
                  ?>
                </select>
              </div>

              <div class="flc-6">
                <select id="_billing_period" name="_billing_period" class="field">
                  <?php
                    $options = wcs_get_subscription_period_strings();
                    foreach ( $options as $value => $name ) {
                      echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $subscription->get_billing_period(), false ) . '>' . esc_html( $name ) . '</option>';
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <h3><?php _ex('Delivery', 'edit', 'igs-client-system') ?></h3>

        <div class="flr gy-25">
          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('First Name', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_first_name" id="_billing_first_name" value="<?php echo $subscription->get_billing_first_name(); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Last Name', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_last_name" id="_billing_last_name" value="<?php echo $subscription->get_billing_last_name(); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Phone number', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_phone" id="_billing_phone" value="<?php echo $subscription->igs_get_billing_phone(); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Email address', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_email" id="_billing_email" value="<?php echo $subscription->igs_get_billing_email(); ?>">
          </div>

          <div class="flc-12">
            <p class="fw-b mb-15"><?php _e('Delivery Method', 'igs-client-system') ?></p>

            <?php
              $current_id = $subscription->igs_get_shipping_method();
              $available_methods = $subscription->igs_get_shipping_methods()
            ?>

            <ul class="d-f f-w g-20">
              <?php foreach ( $subscription->igs_get_shipping_methods() as $method ) : ?>
                <li>
                  <?php
                    $input_id = 'shipping_method_' . esc_attr( sanitize_title ($method->id ) );
                    $input_attrs = array(
                      'name'  => 'shipping_method',
                      'id'    => $input_id,
                      'value' => esc_attr( $method->id ),
                      'class' => 'field'
                    );

                    if ( 1 < count( $available_methods ) ) {
                      $input_attrs['type'] = 'radio';

                      if ( $method->id == $current_id ) {
                        $input_attrs['checked'] = '';
                      }

                    } else {
                      $input_attrs['type'] = 'hidden';
                    }

                    echo wp_sprintf( '<input %1$s>', igs_cs_html_attributes( $input_attrs ) );
                    echo wp_sprintf( '<label %2$s>%1$s</label>',
                      esc_html( $method->get_title() ),
                      igs_cs_html_attributes(array(
                        'for'   => $input_id,
                        'class' => 'field-radio ps-r'
                      ) )
                    );
                    do_action( 'woocommerce_after_shipping_rate', $method, 0 );
                  ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <h3><?php _ex('Invoice', 'edit', 'igs-client-system') ?></h3>

        <div class="flr gy-25">
          <div class="flc-12">
            <p class="fw-b mb-15"><?php _e('Issuing an invoice to a company.', 'igs-client-system'); ?></p>

            <?php
              $input_attrs = array(
                'type'  => 'radio',
                'name'  => '_billing_is_invoice',
                'class' => 'field',
              );
              $has_invoice = $subscription->get_meta('_billing_is_invoice');
            ?>

            <ul class="d-f f-w g-20">
              <li>
                <?php
                  $input_attrs['id'] = '_billing_is_invoice_yes';
                  echo wp_sprintf( '<input %1$s value="1" %2$s>',
                    igs_cs_html_attributes( $input_attrs ),
                    $has_invoice == 1 ? 'checked' : null,
                  );
                  echo wp_sprintf( '<label %2$s>%1$s</label>',
                    __( 'Yes, issue an invoice', 'igs-client-system' ),
                    igs_cs_html_attributes(array(
                      'for'   => '_billing_is_invoice_yes',
                      'class' => 'field-radio ps-r'
                    ) )
                  );
                ?>
              </li>

              <li>
                <?php
                  $input_attrs['id'] = '_billing_is_invoice_no';
                  echo wp_sprintf( '<input %1$s value="" %2$s>',
                    igs_cs_html_attributes( $input_attrs ),
                    $has_invoice != 1 ? 'checked' : null
                  );
                  echo wp_sprintf( '<label %2$s>%1$s</label>',
                    __( 'No, do not issue an invoice', 'igs-client-system' ),
                    igs_cs_html_attributes(array(
                      'for'   => '_billing_is_invoice_no',
                      'class' => 'field-radio ps-r'
                    ) )
                  );
                ?>
              </li>
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Company', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_invoice_company" id="_billing_invoice_company" value="<?php echo $subscription->get_meta('_billing_invoice_company'); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Materially Responsible Person', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_invoice_mol" id="_billing_invoice_mol" value="<?php echo $subscription->get_meta('_billing_invoice_mol'); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('UIC / Tax ID', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_invoice_eik" id="_billing_invoice_eik" value="<?php echo $subscription->get_meta('_billing_invoice_eik'); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('VAT Number', 'igs-client-system') ?></p>
            <input type="text" class="field" name="_billing_invoice_vatnum" id="_billing_invoice_vatnum" value="<?php echo $subscription->get_meta('_billing_invoice_vatnum'); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Town', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_invoice_town" id="_billing_invoice_town" value="<?php echo $subscription->get_meta('_billing_invoice_town'); ?>">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e('Address', 'igs-client-system') ?> *</p>
            <input type="text" class="field" name="_billing_invoice_address" id="_billing_invoice_address" value="<?php echo $subscription->get_meta('_billing_invoice_address'); ?>">
          </div>
        </div>

        <?php submit_button( __('Save changes', 'igs-client-system'), 'bg-1 bg-h-3 tc-w tc-h-6' ); ?>
      </form>

      <?php do_action('woocommerce_admin_order_data_after_billing_address', $subscription); ?>
    </div>

    <div class="flc-4 d-f f-c gy-30">
      <div id="woocommerce-order-notes" class="d-f f-c gy-30">
        <h3><?php _e('Order Notes', 'igs-client-system'); ?></h3>
        <?php WC_Meta_Box_Order_Notes::output($subscription); ?>
      </div>
    </div>
  </div>

  <?php
    // WCS_Meta_Box_Related_Orders::output($subscription);
  ?>
</main>
<?php //igs_cs_print($subscription->igs_get_notes());
