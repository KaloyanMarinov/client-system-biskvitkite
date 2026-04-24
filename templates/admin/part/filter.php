<?php

  defined( 'ABSPATH' ) || exit;

  /** @var \IGS_CS_List_Subscription $module */

?>

<form action="<?php echo admin_url( 'admin.php' ); ?>" method="get" class="mb-20">
  <input type="hidden" name="page" value="<?php echo esc_attr( $subscription_page->get_subscriptions_slug() ); ?>" >

  <div class="flr gy-15 mb-40">
    <?php
      echo $module->get_filter_status();
      echo $module->get_filter_next_date();
      echo $module->get_filter_number();
      echo $module->get_filter_client();
      echo $module->get_filter_sort();
      echo $module->get_filter_order();
      echo $module->get_filter_per_page();
    ?>

    <div class="flc-3 as-fe d-f gx-5">
      <button type="submit" class="button bg-3 bg-h-1 tc-6 tc-h-w w-100"><?php _e('Filter', 'igs-client-system'); ?></button>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $subscription_page->get_subscriptions_slug() ) ); ?>" class="button bg-h-3 tc-6 tc-h-w f-a ta-c"><?php _e('Reset', 'igs-client-system'); ?></a>
    </div>
  </div>

  <div class="d-f gy-15">
    <div class="d-f f-auto ai-c g-10">
      <?php echo $module->no_found_rows(); ?>
    </div>
  </div>
</form>
