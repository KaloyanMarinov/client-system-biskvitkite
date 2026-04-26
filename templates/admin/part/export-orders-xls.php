<?php

// $grouped_orders : array of 'Y-m-d' => WC_Order[]  (sorted ascending)
// $type           : 'full' | 'short'
// $status         : WC status string, e.g. 'wc-cooking'

$status_slug = $status ? str_replace( 'wc-', '', $status ) : 'all';
$filename    = 'export-orders-' . $type . '-' . $status_slug . '-' . date( 'd-m-Y_H-i-s' ) . '.xls';
$data_format = get_option( 'date_format' );
$all_statuses = wc_get_order_statuses(); // 'wc-xxx' => 'Label'

header( "Content-Type: application/vnd.ms-excel; charset=utf-8" );
header( "Content-Disposition: attachment; filename=$filename" );
header( "Pragma: no-cache" );
header( "Expires: 0" );

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

echo '<Styles>
  <Style ss:ID="sHeader">
    <Font ss:Bold="1" ss:Size="14" ss:FontName="Arial"/>
    <Interior ss:Color="#EEEEEE" ss:Pattern="Solid"/>
    <Alignment ss:Vertical="Center" ss:Horizontal="Center"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    </Borders>
  </Style>
  <Style ss:ID="sSummaryHeader">
    <Font ss:Bold="1" ss:Size="14" ss:FontName="Arial"/>
    <Alignment ss:Vertical="Center"/>
    <Borders>
      <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    </Borders>
  </Style>
  <Style ss:ID="sBody">
    <Font ss:Size="14" ss:FontName="Arial"/>
    <Alignment ss:Vertical="Top" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="sCurrency">
    <Font ss:Size="14" ss:FontName="Arial"/>
    <Alignment ss:Vertical="Top" ss:Horizontal="Right"/>
    <NumberFormat ss:Format="#,##0.00\ &quot;€&quot;"/>
  </Style>
</Styles>' . "\n";

// ── One Worksheet per date ─────────────────────────────────────────────────

foreach ( $grouped_orders as $date_key => $orders ) {

  // Sheet name: d.m.Y or fallback.
  if ( '0000-00-00' === $date_key ) {
    $sheet_name = __( 'No date', 'igs-client-system' );
  } else {
    $dt         = DateTime::createFromFormat( 'Y-m-d', $date_key );
    $sheet_name = $dt ? $dt->format( 'd.m.Y' ) : $date_key;
  }

  echo '<Worksheet ss:Name="' . esc_attr( $sheet_name ) . '">' . "\n";
  echo '<Table>' . "\n";

  // ── Column widths ────────────────────────────────────────────────────────
  echo '<Column ss:Width="60"/>'  . "\n";   // #
  echo '<Column ss:Width="120"/>' . "\n";   // Status
  if ( 'full' === $type ) {
    echo '<Column ss:Width="200"/>' . "\n"; // Customer
    echo '<Column ss:Width="150"/>' . "\n"; // Phone
  }
  echo '<Column ss:Width="300"/>' . "\n";   // Orders
  echo '<Column ss:Width="180"/>' . "\n";   // Products total
  echo '<Column ss:Width="250"/>' . "\n";   // Fees
  echo '<Column ss:Width="110"/>' . "\n";   // Total
  echo '<Column ss:Width="110"/>' . "\n";   // Date
  echo '<Column ss:Width="150"/>' . "\n";   // Preparation date
  if ( 'full' === $type ) {
    echo '<Column ss:Width="120"/>' . "\n"; // Delivery
    echo '<Column ss:Width="300"/>' . "\n"; // Shipping address
    echo '<Column ss:Width="140"/>' . "\n"; // Payment method
  }
  echo '<Column ss:Width="400"/>' . "\n";   // Notes
  echo '<Column ss:Width="350"/>' . "\n";   // Invoice

  // ── Header row ───────────────────────────────────────────────────────────
  echo '<Row ss:StyleID="sHeader">' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( '#', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( 'Status', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  if ( 'full' === $type ) {
    echo '<Cell><Data ss:Type="String">' . __( 'Customer', 'igs-client-system' ) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . __( 'Phone Number', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  }
  echo '<Cell><Data ss:Type="String">' . __( 'Orders', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( 'Products Total', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( 'Fees', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( 'Total', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( 'Date', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( 'Preparation Date', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  if ( 'full' === $type ) {
    echo '<Cell><Data ss:Type="String">' . __( 'Delivery', 'igs-client-system' ) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . __( 'Shipping Address', 'igs-client-system' ) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . __( 'Payment Method', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  }
  echo '<Cell><Data ss:Type="String">' . __( 'Notes', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '<Cell><Data ss:Type="String">' . __( 'Invoice', 'igs-client-system' ) . '</Data></Cell>' . "\n";
  echo '</Row>' . "\n";

  // ── Data rows + collect product totals ───────────────────────────────────
  $product_totals = array();

  foreach ( $orders as $order ) {
    $products         = array();
    $shipping_address = IGS_CS()->admin()->order()->get_shipping_address( $order ) ?: array();

    foreach ( $order->get_items() as $item_id => $item ) {
      $name       = $item->get_name();
      $qty        = $item->get_quantity();
      $products[] = $name . ' - ' . wp_sprintf( _n( '%d piece', '%d pieces', $qty, 'igs-client-system' ), $qty );

      if ( ! isset( $product_totals[ $name ] ) ) {
        $product_totals[ $name ] = 0;
      }
      $product_totals[ $name ] += $qty;
    }

    // Preparation date: stored as Y-m-d, display as d.m.Y.
    $prep_stored  = $order->get_meta( '_igs_preparation_date' );
    $prep_display = '';
    if ( $prep_stored ) {
      $prep_dt = DateTime::createFromFormat( 'Y-m-d', $prep_stored );
      if ( $prep_dt ) {
        $prep_display = $prep_dt->format( 'd.m.Y' );
      }
    }

    // Fees (excluding shipping).
    $fee_lines  = array();
    $fees_total = 0.0;
    foreach ( $order->get_items( 'fee' ) as $fee_item ) {
      $fee_amount  = (float) $fee_item->get_total();
      $fee_lines[] = $fee_item->get_name() . ': ' . number_format( $fee_amount, 2 ) . ' €';
      $fees_total += $fee_amount;
    }
    $grand_total = (float) $order->get_subtotal() + $fees_total;

    // Order status label.
    $order_status_key   = 'wc-' . $order->get_status();
    $order_status_label = $all_statuses[ $order_status_key ] ?? $order->get_status();

    // Invoice data.
    $invoice_data = '';
    if ( $order->get_meta( '_billing_is_invoice' ) == '1' ) {
      $invoice_parts = array_filter( array(
        $order->get_meta( '_billing_invoice_company' ),
        $order->get_meta( '_billing_invoice_mol' )    ? __( 'Materially Responsible Person', 'igs-client-system' ) . ': ' . $order->get_meta( '_billing_invoice_mol' )     : '',
        $order->get_meta( '_billing_invoice_eik' )    ? __( 'UIC / Tax ID', 'igs-client-system' )                  . ': ' . $order->get_meta( '_billing_invoice_eik' )     : '',
        $order->get_meta( '_billing_invoice_vatnum' ) ? __( 'VAT Number', 'igs-client-system' )                    . ': ' . $order->get_meta( '_billing_invoice_vatnum' )  : '',
        $order->get_meta( '_billing_invoice_town' )   ? __( 'City', 'igs-client-system' )                          . ': ' . $order->get_meta( '_billing_invoice_town' )    : '',
        $order->get_meta( '_billing_invoice_address' ) ? __( 'Address', 'igs-client-system' )                      . ': ' . $order->get_meta( '_billing_invoice_address' ) : '',
      ) );
      $invoice_data = implode( "&#10;", $invoice_parts );
    }

    echo '<Row ss:StyleID="sBody">' . "\n";
    echo '<Cell><Data ss:Type="Number">' . $order->get_id() . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . esc_xml( $order_status_label ) . '</Data></Cell>' . "\n";
    if ( 'full' === $type ) {
      echo '<Cell><Data ss:Type="String">' . esc_xml( $order->get_formatted_billing_full_name() ) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml( $order->get_billing_phone() ) . '</Data></Cell>' . "\n";
    }
    echo '<Cell><Data ss:Type="String">' . esc_xml( implode( "&#10;", $products ) ) . '</Data></Cell>' . "\n";
    echo '<Cell ss:StyleID="sCurrency"><Data ss:Type="Number">' . $order->get_subtotal() . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . esc_xml( implode( "\n", $fee_lines ) ) . '</Data></Cell>' . "\n";
    echo '<Cell ss:StyleID="sCurrency"><Data ss:Type="Number">' . $grand_total . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . esc_xml( $order->get_date_created()->format( $data_format ) ) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . esc_xml( $prep_display ) . '</Data></Cell>' . "\n";
    if ( 'full' === $type ) {
      echo '<Cell><Data ss:Type="String">' . esc_xml( $order->get_shipping_method() ) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml( implode( "&#10;", $shipping_address ) ) . '</Data></Cell>' . "\n";
      echo '<Cell><Data ss:Type="String">' . esc_xml( $order->get_payment_method_title() ) . '</Data></Cell>' . "\n";
    }
    echo '<Cell><Data ss:Type="String">' . esc_xml( $order->get_customer_note() ) . '</Data></Cell>' . "\n";
    echo '<Cell><Data ss:Type="String">' . esc_xml( $invoice_data ) . '</Data></Cell>' . "\n";
    echo '</Row>' . "\n";
  }

  // ── Product summary ──────────────────────────────────────────────────────
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
