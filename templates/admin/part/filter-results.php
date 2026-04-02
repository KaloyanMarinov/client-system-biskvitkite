<?php

  defined( 'ABSPATH' ) || exit;

?>

<div class="flc-3 d-f f-c gy-5">
  <label class="fw-b f-a"><?php _e('Results per page', 'igs-client-system'); ?>: </label>

  <select name="igs_per_page" class="field">
    <?php foreach ($results_per_page as $per_page) {

      $attrs = array(
        'value' => $per_page
      );

      if ( $per_page == $selected )
        $attrs['selected'] = '';

      echo wp_sprintf( '<option %2$s>%1$s</option>',
        $per_page,
        igs_cs_html_attributes($attrs )
      );
    } ?>
  </select>
</div>
