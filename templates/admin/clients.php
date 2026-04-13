<?php
  defined( 'ABSPATH' ) || exit;

  $module = IGS_CS()->admin()->subscriptions()->get_users();

  /** @var \IGS_CS_Users $module */

?>

<main class="igs-main pb-50">
  <?php
    /**
     * igs_cs_before_content hook.
     *
     */
    do_action('igs_cs_before_content');
  ?>

  <?php $module->get_filter( $clients_page ); ?>

  <?php if ( $users = $module->get_query()->get_results() ) { ?>
    <div class="flr gy-20">
      <?php
        foreach ($users as $user_id ) {
          igs_cs_get_template( 'admin/loop/client' , array('user_id' => $user_id ) );
        }
      ?>
    </div>
  <?php
      echo $module->get_pagination();
    } else {
  ?>
      <p><?php _e('No customers found.', 'igs-client-system'); ?></p>
  <?php } ?>
</main>
