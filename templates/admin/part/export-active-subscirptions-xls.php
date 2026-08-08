<?php

$filename = 'subscriptions-export-' . date('d-m-Y-H-i-s') . '.xls';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

echo '  <Styles>
  <Style ss:ID="sHeader">
    <Font ss:Bold="1" ss:Size="12" ss:FontName="Arial"/>
    <Interior ss:Color="#EEEEEE" ss:Pattern="Solid"/>
    <Alignment ss:Vertical="Center" ss:Horizontal="Center"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    </Borders>
  </Style>
  <Style ss:ID="sSummaryHeader">
    <Font ss:Bold="1" ss:Size="12" ss:FontName="Arial"/>
    <Alignment ss:Vertical="Center"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    </Borders>
  </Style>
  <Style ss:ID="sBody">
    <Font ss:Size="12" ss:FontName="Arial"/>
    <Alignment ss:Vertical="Top" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="sCurrency">
    <Font ss:Size="12" ss:FontName="Arial"/>
    <Alignment ss:Vertical="Top" ss:Horizontal="Right"/>
    <NumberFormat ss:Format="#,##0.00\ &quot;€&quot;"/>
  </Style>
</Styles>' . "\n";

foreach ($grouped_data as $date => $subscriptions) {
  $sheet_name = esc_attr($date);

  echo '<Worksheet ss:Name="' . $sheet_name . '">' . "\n";
  echo '<Table>' . "\n";

  echo '<Column ss:Width="60"/>' . "\n";
  echo '<Column ss:Width="200"/>' . "\n";
  echo '<Column ss:Width="200"/>' . "\n";
  echo '<Column ss:Width="300"/>' . "\n";
  echo '<Column ss:Width="100"/>' . "\n";
  echo '<Column ss:Width="100"/>' . "\n";
  echo '<Column ss:Width="120"/>' . "\n";
  echo '<Column ss:Width="100"/>' . "\n";
  echo '<Column ss:Width="300"/>' . "\n";
  echo '<Column ss:Width="120"/>' . "\n";
  echo '<Column ss:Width="120"/>' . "\n";
  echo '<Column ss:Width="400"/>' . "\n";

  echo '<Row ss:StyleID="sHeader">' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('#', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Customer', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Phone Number', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Orders', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Total', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Status', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Next Date', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Delivery', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Shipping Address', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Payment Method', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Subscriber from', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Notes', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '</Row>' . "\n";

  $product_totals = array();

  if (empty($subscriptions)) {
    echo '<Row ss:StyleID="sBody">' . "\n";
    echo '<Cell ss:MergeAcross="3"><Data ss:Type="String">' . __('No deliveries scheduled for this day.', 'igs-client-system') . '</Data></Cell>' . "\n";
    echo '</Row>' . "\n";
  } else {
    foreach ($subscriptions as $sub) {
      $status           = wp_strip_all_tags( $sub->igs_get_status_name() );
      $products         = array();
      $shipping_address = IGS_CS()->admin()->order()->get_shipping_address( $sub ) ?: [];

      foreach ( $sub->get_items() as $item_id => $item ) {
        $product     = $item->get_product();
        $export_name = '';
        if ( $product ) {
          $export_name = get_post_meta( $product->get_id(), '_igs_export_name', true );
          if ( ! $export_name && $product->get_parent_id() ) {
            $parent_export_name = get_post_meta( $product->get_parent_id(), '_igs_export_name', true );
            if ( $parent_export_name ) {
              $attrs       = wc_get_formatted_variation( $product, true, false );
              $export_name = $parent_export_name . ( $attrs ? ' - ' . $attrs : '' );
            }
          }
        }
        $name        = $export_name ?: $item->get_name();
        $qty         = $item->get_quantity();
        $products[]  = $name . ' - ' . wp_sprintf( _n( '%d piece', '%d pieces', $qty, 'igs-client-system' ), $qty );

        if ( ! isset( $product_totals[ $name ] ) ) {
          $product_totals[ $name ] = 0;
        }
        $product_totals[ $name ] += $qty;
      }

      echo '<Row ss:StyleID="sBody">' . "\n";
      echo '<Cell><Data ss:Type="Number">' . $sub->get_id() . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml($sub->get_formatted_billing_full_name()) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml($sub->get_billing_phone()) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml( implode("&#10;", $products) ) . '</Data></Cell>' . "\n";
      echo '<Cell ss:StyleID="sCurrency"><Data ss:Type="Number">' . $sub->get_subtotal() . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . $status . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml($sub->igs_get_next_date()) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml($sub->get_shipping_method()) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml( implode("&#10;", $shipping_address) ) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . $sub->get_payment_method_title() . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml( $sub->igs_get_months_subscriber() ) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml( $sub->get_customer_note() ) . '</Data></Cell>' . "\n";
      echo '</Row>' . "\n";
    }
  }

  // ── Product summary ────────────────────────────────────────────────────────
  if ( ! empty( $product_totals ) ) {

    echo '<Row><Cell><Data ss:Type="String"></Data></Cell></Row>' . "\n";

    echo '<Row ss:StyleID="sSummaryHeader">' . "\n";
    echo '<Cell ss:MergeAcross="2"><Data ss:Type="String">' . __( 'Products', 'igs-client-system' ) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . __( 'Total Quantity', 'igs-client-system' ) . '</Data></Cell>' . "\n";
    echo '</Row>' . "\n";

    arsort( $product_totals );

    foreach ( $product_totals as $name => $qty ) {
      echo '<Row ss:StyleID="sBody">' . "\n";
      echo '<Cell ss:MergeAcross="2"><Data ss:Type="String">' . esc_xml( $name ) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="Number">' . (int) $qty . '</Data></Cell>' . "\n";
      echo '</Row>' . "\n";
    }
  }

  echo '</Table>' . "\n";
  echo '</Worksheet>' . "\n";
}

echo '</Workbook>' . "\n";
