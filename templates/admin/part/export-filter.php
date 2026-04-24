<?php

  defined( 'ABSPATH' ) || exit;

  /** @var \IGS_CS_Export $module */

?>

<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" class="mb-50">
  <input type="hidden" name="action" value="igs_export_subscriptions">
  <?php wp_nonce_field( 'igs_export_subscriptions_action', 'igs_export_subscriptions_nonce' ); ?>

  <h3 class="mb-20"><?php _e('Subscriptions', 'igs-client-system'); ?></h3>

  <div class="flr gy-15">
    <?php
      $module->get_filter_sub_status();
      $module->get_filter_next_date();
    ?>

    <div class="flc-3 as-fe">
      <button type="submit" class="button bg-1 bg-h-3 tc-w tc-h-w w-100"><?php _e('Download XLS', 'igs-client-system'); ?></button>
    </div>
  </div>
</form>

<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" class="mb-20">
  <input type="hidden" name="action" value="igs_export_orders">
  <?php wp_nonce_field( 'igs_export_orders_action', 'igs_export_orders_nonce' ); ?>

  <h3 class="mb-20"><?php _e('Orders', 'igs-client-system'); ?></h3>

  <div class="flr gy-15">
    <?php
      $module->get_filter_orders_status();
      $module->get_filter_order_period();
      $module->get_filter_order_date();
      $module->get_filter_order_type();
    ?>

    <div class="flc-3 as-fe">
      <button type="submit" class="button bg-1 bg-h-3 tc-w tc-h-w w-100"><?php _e('Download XLS', 'igs-client-system'); ?></button>
    </div>
  </div>
</form>
