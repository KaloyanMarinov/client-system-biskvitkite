<?php

  defined( 'ABSPATH' ) || exit;

?>

<div class="flc-3 d-f f-c gy-5">
  <label class="fw-b f-a"><?php _ex('Next Date', 'filter', 'igs-client-system'); ?>: </label>

  <select name="igs_next_date" class="field">
    <?php foreach ($next_days as $value => $order) {
      $attrs = array(
        'value' => $value,
      );
      if ( $value == $selected )
        $attrs['selected'] = '';

      echo wp_sprintf( '<option %2$s>%1$s</option>',
        $order,
        igs_cs_html_attributes($attrs)
      );
    } ?>
  </select>
</div>
