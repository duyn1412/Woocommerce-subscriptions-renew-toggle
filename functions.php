add_filter('woocommerce_subscriptions_frontend_view_subscription_script_parameters','d_script_params', 10 ,1);

function d_script_params($script_params){
  global $wp;
  $subscription   = wcs_get_subscription( $wp->query_vars['view-subscription'] );
  //var_dump($subscription);
  $script_params['auto_renew_nonce'] =  check_renew_toggle( $subscription ) ? wp_create_nonce( "toggle-auto-renew-{$subscription->get_id()}" ) : false;
   return $script_params;
}



function send_ajax_response_cs( $subscription ) {
    wp_send_json( array(
      'payment_method' => esc_attr( $subscription->get_payment_method_to_display( 'customer' ) ),
      'is_manual'      => wc_bool_to_string( $subscription->is_manual()),
    ) );
  }




add_action( 'wp_ajax_wcs_enable_auto_renew','d_enable_renew_cs', 1);

function d_enable_renew_cs(){
 // print_r($_REQUEST);
  //       exit;
    $subscription_id = absint( $_POST['subscription_id'] );
    check_ajax_referer( "toggle-auto-renew-{$subscription_id}", 'security' );

    $subscription = wcs_get_subscription( $subscription_id );

    if ( wc_get_payment_gateway_by_order( $subscription )  ) {
      $subscription->set_requires_manual_renewal( false );
      $subscription->save();

      send_ajax_response_cs( $subscription );
    }
}




add_action( 'wp_ajax_wcs_disable_auto_renew','disable_auto_renew_cs' , 1);

function disable_auto_renew_cs(){
    //print_r($_REQUEST);
     //    exit;

    $subscription_id = absint( $_POST['subscription_id'] );
    check_ajax_referer( "toggle-auto-renew-{$subscription_id}", 'security' );

    $subscription = wcs_get_subscription( $subscription_id );

    if ( wc_get_payment_gateway_by_order( $subscription )  ) {
      $subscription->set_requires_manual_renewal( true );
      $subscription->save();


      send_ajax_response_cs( $subscription );
    }
}
