<?php

  defined( 'ABSPATH' ) || exit;

  $hide = isset( $hide ) ? $hide : false;

?>

<div class="flc-3 d-f f-c gy-5 <?php echo $class; ?>"  <?php echo $hide ? 'style="display:none"' : null; ?>>
  <label class="fw-b f-a"><?php echo $label; ?>: </label>
  <input type="text" name="<?php echo $name; ?>" class="field js-datepicker" value="<?php echo date('d.m.Y'); ?>">
</div>
