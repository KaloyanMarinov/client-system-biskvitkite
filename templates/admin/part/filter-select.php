<?php

  defined( 'ABSPATH' ) || exit;

  $hide = isset( $hide ) ? $hide : false;

?>

<div class="flc-3 d-f f-c gy-5 <?php echo $class; ?>"  <?php echo $hide ? 'style="display:none"' : null; ?>>
  <label class="fw-b f-a"><?php echo $label; ?>: </label>

  <select name="<?php echo $name; ?>" class="field">
    <?php foreach ($options as $value => $option) {
      $attrs = array(
        'value' => $value
      );

      if ( ! is_null($selected) && $value == $selected )
        $attrs['selected'] = '';

      echo wp_sprintf( '<option %2$s>%1$s</option>',
        $option,
        igs_cs_html_attributes($attrs)
      );
    } ?>
  </select>
</div>
