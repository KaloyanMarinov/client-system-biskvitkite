<?php

  defined( 'ABSPATH' ) || exit;

?>

<div class="flc-3 d-f f-c gy-5">
  <label class="fw-b f-a"><?php echo $label; ?>:</label>

  <?php
    WCS_Select2::render(
      array(
        'class'       => 'wc-customer-search',
        'name'        => $name,
        'id'          => $name,
        'placeholder' => $placeholder,
        'selected'    => $selected,
        'value'       => $value,
      )
    );
  ?>
</div>
