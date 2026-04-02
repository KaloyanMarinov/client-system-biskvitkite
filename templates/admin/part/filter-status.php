<?php

  defined( 'ABSPATH' ) || exit;

?>

<div class="flc-3 d-f f-c gy-5">
  <label class="fw-b f-a"><?php _e('Status', 'igs-client-system'); ?>: </label>

  <select name="igs_status" class="field">
    <?php foreach ($statuses as $value => $status ) {

      $attrs = array(
        'value' => $value,
      );
      if ( $value == $selected )
        $attrs['selected'] = '';

      echo wp_sprintf( '<option %2$s>%1$s</option>',
        $status,
        igs_cs_html_attributes($attrs)
      );
    } ?>
  </select>
</div>
