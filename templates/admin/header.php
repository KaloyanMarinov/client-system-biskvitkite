<?php

  defined( 'ABSPATH' ) || exit;

?>

<header class="header mt-10 mb-30 tc-b">
  <ul class="header__menu h-u-md-80 h-d-sm-60 px-u-sm-20 px-o-xs-10 d-f ai-c jc-c gx-u-lg-15 tc-w lh-1-5 bg-6 tc-w br-p">
    <li><a href="<?php echo admin_url( '/' ) ?>"><?php _e('Dashborad', 'igs-client-system'); ?></a></li>
    <li>
      <a href="<?php echo admin_url( 'admin.php?page=igs-subscriptions' ) ?>"><?php _e('Subscriptions', 'igs-client-system'); ?></a>
    </li>
    <li>
      <a href="<?php echo admin_url( 'admin.php?page=igs-schedule' ) ?>"><?php _e('Schedule', 'igs-client-system'); ?></a>
    </li>
    <li><a href="<?php echo admin_url( 'admin.php?page=igs-clients' ) ?>"><?php _e('Clients', 'igs-client-system'); ?></a></li>
    <li><a href="<?php echo admin_url( 'admin.php?page=igs-subscriptions' ) ?>"><?php _e('Export', 'igs-client-system'); ?></a></li>
  </ul>
</header>
