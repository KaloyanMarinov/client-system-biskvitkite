<?php
  defined( 'ABSPATH' ) || exit;

  $module = IGS_CS()->admin()->subscriptions()->get_export();

  /** @var \IGS_CS_Export $module */

?>

<main class="igs-main pb-50">
  <?php
    /**
     * igs_cs_before_content hook.
     *
     */
    do_action('igs_cs_before_content');

    $module->igs_display_admin_notices();

    $module->get_filter( $export_page );

  ?>
</main>
