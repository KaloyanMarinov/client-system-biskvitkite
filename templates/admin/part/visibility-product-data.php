<div id="igs_visibility_product_data" class="panel woocommerce_options_panel hidden">
  <div class="options_group">
    <?php
      $options = IGS_CS_Admin_Product_Data::igs_get_visibility_options();

      if ($options) {
        $selected_values = $product->get_meta('_visibility_roles');

        if ( ! is_array($selected_values) ) {
          $selected_values = array();
        }

        $tooltip = __('Select who can see this product in the frontend.', 'igs-client-system');
    ?>

      <p class="form-field">
        <label for="_visibility_roles"><?php _e('Visibility Level', 'igs-client-system'); ?></label>

			  <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php echo $tooltip; ?>" data-tip="<?php echo $tooltip; ?>"></span>

        <select id="_visibility_roles" name="_visibility_roles[]" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e('Select...', 'igs-client-system'); ?>">
          <?php
            foreach ($options as $key => $value) {
              $is_selected = in_array($key, $selected_values);
              echo '<option value="' . esc_attr($key) . '" ' . selected($is_selected, true, false) . '>' . esc_html($value) . '</option>';
            }
          ?>
        </select>
      </p>
    <?php } ?>
  </div>
</div>
