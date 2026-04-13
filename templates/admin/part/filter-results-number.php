<?php

  defined( 'ABSPATH' ) || exit;

?>

<div class="flc-3 d-f f-c gy-5">
  <label class="fw-b f-a"><?php _e('Results per page', 'igs-client-system'); ?>: </label>

  <select name="igs_number" class="field">
    <?php foreach ($options as $option) {

      $attrs = array(
        'value' => $option
      );

      if ( $option == $selected )
        $attrs['selected'] = '';

      echo wp_sprintf( '<option %2$s>%1$s</option>',
        $option,
        igs_cs_html_attributes($attrs )
      );
    } ?>
  </select>
</div>
