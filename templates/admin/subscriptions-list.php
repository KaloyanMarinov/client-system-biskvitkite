<?php

  $module = IGS_CS()->admin()->subscriptions()->get_list();

  /** @var \IGS_CS_List_Subscription $module */

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

  <div class="d-f ai-c jc-sb mb-20">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . IGS_CS()->admin()->menus()->get_new_subscription_slug() ) ); ?>" class="button button-primary"><?php _e( '+ New Subscription', 'igs-client-system' ); ?></a>
  </div>

  <?php $module->get_filter( $subscription_page ); ?>

  <?php if ( $module->get_query()->have_posts() ) { ?>
    <div class="flr gy-20">
      <?php
        while ( $module->get_query()->have_posts() ) : $module->get_query()->the_post();
          igs_cs_get_template( 'admin/loop/list-subscription' );
        endwhile;
      ?>
    </div>
  <?php
    echo $module->get_pagination();
      wp_reset_query();
    } else { ?>
    <p><?php _e('No Subscriptions Found', 'igs-client-system'); ?></p>
  <?php } ?>

</main>
