<?php

  defined( 'ABSPATH' ) || exit;

?>

<div class="flc-3 d-f f-c gy-5">
  <label class="fw-b f-a"><?php _e('Active subscriber', 'igs-client-system'); ?>: </label>

  <select name="igs_active_subscriber" class="field">
    <?php foreach ($options as $value => $status ) {

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
