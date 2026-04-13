<?php

$filename    = 'export-orders-'. $type . '-' . date('d-m-Y') . '.xls';
$data_format = get_option( 'date_format' );

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

echo '<Worksheet ss:Name="Sheet 1">' . "\n";
echo '<Table>' . "\n";

echo '<Column ss:Width="60"/>' . "\n";
if ( $type == 'full' ) {
  echo '<Column ss:Width="200"/>' . "\n";
  echo '<Column ss:Width="200"/>' . "\n";
}
echo '<Column ss:Width="300"/>' . "\n";
if ( $type == 'full' ) {
  echo '<Column ss:Width="100"/>' . "\n";
}
echo '<Column ss:Width="100"/>' . "\n";
if ( $type == 'full' ) {
  echo '<Column ss:Width="120"/>' . "\n";
  echo '<Column ss:Width="300"/>' . "\n";
  echo '<Column ss:Width="120"/>' . "\n";
}
echo '<Column ss:Width="400"/>' . "\n";

echo '<Row ss:StyleID="sHeader">' . "\n";
echo '<Cell><Data ss:Type="String">' . __('#', 'igs-client-system') . '</Data></Cell>' . "\n";
if ( $type == 'full' ) {
  echo '<Cell><Data ss:Type="String">' . __('Customer', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Phone Number', 'igs-client-system') . '</Data></Cell>' . "\n";
}
echo '<Cell><Data ss:Type="String">' . __('Orders', 'igs-client-system') . '</Data></Cell>' . "\n";
if ( $type == 'full' ) {
  echo '<Cell><Data ss:Type="String">' . __('Total', 'igs-client-system') . '</Data></Cell>' . "\n";
}
echo '<Cell><Data ss:Type="String">' . __('Date', 'igs-client-system') . '</Data></Cell>' . "\n";
if ( $type == 'full' ) {
  echo '<Cell><Data ss:Type="String">' . __('Delivery', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Shipping Address', 'igs-client-system') . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __('Payment Method', 'igs-client-system') . '</Data></Cell>' . "\n";
}
echo '<Cell><Data ss:Type="String">' . __('Notes', 'igs-client-system') . '</Data></Cell>' . "\n";
echo '</Row>' . "\n";

foreach ($orders as $order_id) {
  $order            = wc_get_order( $order_id );
  $products         = array();
  $shipping_address = IGS_CS()->admin()->order()->get_shipping_address( $order ) ?: [];;

  foreach ( $order->get_items() as $item_id => $item ) {
    $products[] = $item->get_name() . ' - ' . wp_sprintf( _n( '%d piece' , '%d pieces', $item->get_quantity(), 'igs-client-system') , $item->get_quantity() );
  }

  echo '<Row ss:StyleID="sBody">' . "\n";
  echo '<Cell><Data ss:Type="Number">' . $order->get_id() . '</Data></Cell>' . "\n";
  if ( $type == 'full' ) {
    echo '<Cell><Data ss:Type="String">' . esc_xml($order->get_formatted_billing_full_name()) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . esc_xml($order->get_billing_phone()) . '</Data></Cell>' . "\n";
  }
  echo '<Cell><Data ss:Type="String">' . esc_xml( implode("&#10;", $products) ) . '</Data></Cell>' . "\n";
  if ( $type == 'full' ) {
    echo '<Cell ss:StyleID="sCurrency"><Data ss:Type="Number">' . $order->get_subtotal() . '</Data></Cell>' . "\n";
  }
  echo '<Cell><Data ss:Type="String">' . esc_xml($order->get_date_created()->format($data_format)) . '</Data></Cell>' . "\n";
  if ( $type == 'full' ) {
    echo '<Cell><Data ss:Type="String">' . esc_xml($order->get_shipping_method()) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . esc_xml( implode("&#10;", $shipping_address) ) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . $order->get_payment_method_title() . '</Data></Cell>' . "\n";
  }
  echo '<Cell><Data ss:Type="String">' . esc_xml( $order->get_customer_note() ) . '</Data></Cell>' . "\n";
  echo '</Row>' . "\n";
}

echo '</Table>' . "\n";
echo '</Worksheet>' . "\n";

echo '</Workbook>' . "\n";
