<?php

defined( 'ABSPATH' ) || exit;

// $subscription_page : IGS_CS_Admin_Menus
// $notice            : string  (empty | 'existing_subscription')
// $sub_warning_id    : int     (subscription ID when notice === 'existing_subscription')

?>
<main class="igs-main pb-50">
  <?php
    /**
     * igs_cs_before_content hook.
     *
     * @hooked igs_cs_header_template - 10
     */
    do_action( 'igs_cs_before_content' );
  ?>

  <div class="flr">
    <div class="flc-8 d-f f-c gy-30">

      <?php if ( 'existing_subscription' === $notice && $sub_warning_id ) : ?>
        <div class="notice notice-error">
          <p>
            <?php
              printf(
                /* translators: %s: subscription ID link */
                esc_html__( 'This email address already has an active subscription. Subscription #%s.', 'igs-client-system' ),
                '<a href="' . esc_url( admin_url( 'admin.php?page=' . $subscription_page->get_subscriptions_slug() . '&action=edit&id=' . $sub_warning_id ) ) . '">' . esc_html( $sub_warning_id ) . '</a>'
              );
            ?>
          </p>
        </div>
      <?php endif; ?>

      <?php
        $errors = array();
        if ( isset( $_GET['errors'] ) ) {
          $errors = explode( ',', sanitize_text_field( $_GET['errors'] ) );
        }
      ?>

      <?php if ( ! empty( $errors ) ) : ?>
        <div class="notice notice-error">
          <ul>
            <?php if ( in_array( 'first_name',    $errors ) ) : ?><li><?php _e( 'First name is required.',     'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'last_name',     $errors ) ) : ?><li><?php _e( 'Last name is required.',      'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'phone_number',  $errors ) ) : ?><li><?php _e( 'Phone number is required.',   'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'email_required',$errors ) ) : ?><li><?php _e( 'Email address is required.',  'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'email_invalid', $errors ) ) : ?><li><?php _e( 'Email address is not valid.', 'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'start_date',    $errors ) ) : ?><li><?php _e( 'Start date is required.',     'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'next_date',     $errors ) ) : ?><li><?php _e( 'Next payment date is required.', 'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'no_products',   $errors ) ) : ?><li><?php _e( 'At least one product is required.', 'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'invoice_company', $errors ) ) : ?><li><?php _e( 'Company name is required.',  'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'invoice_mol',   $errors ) ) : ?><li><?php _e( 'Materially responsible person is required.', 'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'invoice_eik',   $errors ) ) : ?><li><?php _e( 'UIC / Tax ID is required.',   'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'invoice_town',  $errors ) ) : ?><li><?php _e( 'City is required.',           'igs-client-system' ); ?></li><?php endif; ?>
            <?php if ( in_array( 'invoice_address', $errors ) ) : ?><li><?php _e( 'Address is required.',      'igs-client-system' ); ?></li><?php endif; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" class="d-f f-c gy-25">
        <input type="hidden" name="action" value="igs_create_subscription">
        <?php wp_nonce_field( 'igs_create_subscription_action', 'igs_create_subscription_nonce' ); ?>

        <h3><?php _e( 'New Subscription', 'igs-client-system' ); ?></h3>

        <div class="flr gy-25">

          <div class="flc-u-md-3">
            <p class="fw-b mb-10"><?php _e( 'Start date', 'igs-client-system' ); ?> *</p>
            <input type="text" name="start_timestamp_utc" class="field js-datepicker" value="" placeholder="DD.MM.YYYY">
          </div>

          <div class="flc-u-md-3">
            <p class="fw-b mb-10"><?php _e( 'Next payment', 'igs-client-system' ); ?> *</p>
            <input type="text" name="next_payment_timestamp_utc" class="field js-datepicker" value="" placeholder="DD.MM.YYYY">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _ex( 'Payment', 'edit', 'igs-client-system' ); ?></p>
            <div class="flr">
              <div class="flc-6">
                <select id="_billing_interval" name="_billing_interval" class="field">
                  <?php
                    $options = wcs_get_subscription_period_interval_strings();
                    foreach ( $options as $value => $name ) {
                      echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, 1, false ) . '>' . esc_html( $name ) . '</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="flc-6">
                <select id="_billing_period" name="_billing_period" class="field">
                  <?php
                    $options = wcs_get_subscription_period_strings();
                    foreach ( $options as $value => $name ) {
                      echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, 'month', false ) . '>' . esc_html( $name ) . '</option>';
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>

        </div>

        <h3><?php _e( 'Products', 'igs-client-system' ); ?></h3>

        <table id="igs-subscription-items" class="w-100">
          <thead>
            <tr>
              <th class="py-10 px-5"><?php _e( 'Product', 'igs-client-system' ); ?></th>
              <th class="py-10 px-5 w-80"><?php _e( 'Quantity', 'igs-client-system' ); ?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr class="igs-item-row">
              <td class="p-5">
                <input type="hidden" name="igs_line_product_id[]" value="0" class="igs-pid">
                <select class="field igs-product-search w-100"
                        data-placeholder="<?php esc_attr_e( 'Search product…', 'igs-client-system' ); ?>">
                </select>
              </td>
              <td class="p-5 w-80">
                <input type="number" name="igs_line_qty[]" value="1" min="1" class="field ta-c">
              </td>
              <td class="p-5 w-80">
                <button type="button" class="button tc-1 bg-h-1 tc-h-w igs-remove-item"><?php _e( 'Delete', 'igs-client-system' ); ?></button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="px-5">
          <button type="button" id="igs-add-product-btn" class="button button-primary"><?php _e( '+ Add product', 'igs-client-system' ); ?></button>
        </div>

        <h3><?php _ex( 'Delivery', 'edit', 'igs-client-system' ); ?></h3>

        <div class="flr gy-25">

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'First Name', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_first_name" id="_billing_first_name" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'Last Name', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_last_name" id="_billing_last_name" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'Phone number', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_phone" id="_billing_phone" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'Email address', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_email" id="_billing_email" value="">
          </div>

          <div class="flc-u-">
            <p class="fw-b mb-10"><?php _e( 'Customer note', 'igs-client-system' ); ?></p>
            <textarea class="field" name="customer_note" placeholder="<?php esc_attr_e( 'Additional information for the order, such as allergens, special customer requirements, and others.', 'igs-client-system' ); ?>"></textarea>
          </div>

          <div class="flc-12">
            <p class="fw-b mb-15"><?php _e( 'Payment Method', 'igs-client-system' ); ?></p>

            <?php
              $gateways = array_filter(
                WC()->payment_gateways()->payment_gateways(),
                fn( $g ) => 'yes' === $g->enabled
              );
              $first_gateway = array_key_first( $gateways );
            ?>

            <ul class="d-f f-w g-20">
              <?php foreach ( $gateways as $gateway_id => $gateway ) : ?>
                <li>
                  <?php
                    $input_id = 'payment_method_' . esc_attr( $gateway_id );
                    $input_attrs = array(
                      'type'  => 'radio',
                      'name'  => 'payment_method',
                      'id'    => $input_id,
                      'value' => esc_attr( $gateway_id ),
                      'class' => 'field',
                    );
                    if ( $gateway_id === $first_gateway ) {
                      $input_attrs['checked'] = 'checked';
                    }
                    echo wp_sprintf( '<input %1$s>', igs_cs_html_attributes( $input_attrs ) );
                    echo wp_sprintf( '<label %2$s>%1$s</label>',
                      esc_html( $gateway->get_title() ),
                      igs_cs_html_attributes( array(
                        'for'   => $input_id,
                        'class' => 'field-radio ps-r',
                      ) )
                    );
                  ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="flc-12">
            <p class="fw-b mb-15"><?php _e( 'Delivery Method', 'igs-client-system' ); ?></p>

            <?php
              $temp_sub          = new IGS_CS_Subscription();
              $available_methods = $temp_sub->igs_get_shipping_methods() ?? array();
              $first_method      = ! empty( $available_methods ) ? reset( $available_methods ) : null;
            ?>

            <ul class="d-f f-w g-20">
              <?php foreach ( $available_methods as $method ) : ?>
                <li>
                  <?php
                    $input_id    = 'shipping_method_' . esc_attr( sanitize_title( $method->id ) );
                    $input_attrs = array(
                      'name'  => 'shipping_method',
                      'id'    => $input_id,
                      'value' => esc_attr( $method->id ),
                      'class' => 'field',
                    );

                    if ( 1 < count( $available_methods ) ) {
                      $input_attrs['type'] = 'radio';
                      if ( $first_method && $method->id === $first_method->id ) {
                        $input_attrs['checked'] = 'checked';
                      }
                    } else {
                      $input_attrs['type'] = 'hidden';
                    }

                    echo wp_sprintf( '<input %1$s>', igs_cs_html_attributes( $input_attrs ) );
                    echo wp_sprintf( '<label %2$s>%1$s</label>',
                      esc_html( $method->get_title() ),
                      igs_cs_html_attributes( array(
                        'for'   => $input_id,
                        'class' => 'field-radio ps-r',
                      ) )
                    );
                    do_action( 'woocommerce_after_shipping_rate', $method, 0 );
                  ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

        </div>

        <h3><?php _ex( 'Invoice', 'edit', 'igs-client-system' ); ?></h3>

        <div class="flr gy-25">

          <div class="flc-12">
            <p class="fw-b mb-15"><?php _e( 'Issuing an invoice to a company.', 'igs-client-system' ); ?></p>

            <?php
              $input_attrs = array(
                'type'  => 'radio',
                'name'  => '_billing_is_invoice',
                'class' => 'field',
              );
            ?>

            <ul class="d-f f-w g-20">
              <li>
                <?php
                  $input_attrs['id'] = '_billing_is_invoice_yes';
                  echo wp_sprintf( '<input %1$s value="1">', igs_cs_html_attributes( $input_attrs ) );
                  echo wp_sprintf( '<label %2$s>%1$s</label>',
                    __( 'Yes, issue an invoice', 'igs-client-system' ),
                    igs_cs_html_attributes( array( 'for' => '_billing_is_invoice_yes', 'class' => 'field-radio ps-r' ) )
                  );
                ?>
              </li>
              <li>
                <?php
                  $input_attrs['id'] = '_billing_is_invoice_no';
                  echo wp_sprintf( '<input %1$s value="" checked>', igs_cs_html_attributes( $input_attrs ) );
                  echo wp_sprintf( '<label %2$s>%1$s</label>',
                    __( 'No, do not issue an invoice', 'igs-client-system' ),
                    igs_cs_html_attributes( array( 'for' => '_billing_is_invoice_no', 'class' => 'field-radio ps-r' ) )
                  );
                ?>
              </li>
            </ul>
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'Company', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_invoice_company" id="_billing_invoice_company" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'Materially Responsible Person', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_invoice_mol" id="_billing_invoice_mol" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'UIC / Tax ID', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_invoice_eik" id="_billing_invoice_eik" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'VAT Number', 'igs-client-system' ); ?></p>
            <input type="text" class="field" name="_billing_invoice_vatnum" id="_billing_invoice_vatnum" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'Town', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_invoice_town" id="_billing_invoice_town" value="">
          </div>

          <div class="flc-u-sm-6">
            <p class="fw-b mb-10"><?php _e( 'Address', 'igs-client-system' ); ?> *</p>
            <input type="text" class="field" name="_billing_invoice_address" id="_billing_invoice_address" value="">
          </div>

        </div>

        <?php submit_button( __( 'Create Subscription', 'igs-client-system' ), 'bg-1 bg-h-3 tc-w tc-h-6' ); ?>

      </form>

    </div>
  </div>
</main>
