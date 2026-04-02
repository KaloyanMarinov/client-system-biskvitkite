<?php

  defined( 'ABSPATH' ) || exit;

?>

<p class="fw-b f-a">
  <?php
    echo _e('Found:', 'igs-client-system');
    echo ' ';
    echo wp_sprintf( _n(  '%d Result', '%d Results', $results, 'igs-client-system' ), $results );
  ?>
</p>
