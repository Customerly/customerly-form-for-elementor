<?php

class Elementor_Customerly_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {

    public function get_name() {
        return "customerly";
    }

    public function get_label() {
        return esc_html__( 'Customerly', 'customerly' );
    }

    public function run( $record, $ajax_handler ) {

        $settings = $record->get( 'form_settings' );

        //  Make sure that there is a Customerly API Key
        if ( empty( $settings['customerly_api_key'] ) ) {
            return;
        }

        $customerly_api_key = $settings['customerly_api_key'];
        $customerly_track_UTMs = $settings['customerly_track_UTMs'];
        $customerly_track_everything = $settings['customerly_track_everything'];


        // Get submitted form data.
        $raw_fields = $record->get( 'fields' );

        $email = "";
        $name = "";

        // Normalize form data.
        $fields = [];
        foreach ( $raw_fields as $id => $field ) {

            if ( $id  === "email"){
                $email = $field['value'];
            } else if ( $id  === "name"){
                $name = $field['value'];
            } else {
                $fields[ $id ] = $field['value'];
            }
        }

      $fields["ipaddress"] = \ElementorPro\Core\Utils::get_client_ip();
      $fields["referrer"] = sanitize_url( $_POST['referrer']) ?? '';
      $fields["form_url"] = sanitize_url($_POST['referrer']) ?? '';
      $fields["form_name"] = $settings['form_name'] ;

      $url_components = parse_url($fields["referrer"]);
      parse_str($url_components['query'], $URL_params);

      $params = [];

      if ($customerly_track_everything){
          $params = $URL_params;
      }else if ($customerly_track_UTMs){
          foreach ( $URL_params as $id => $field ) {
              if ( $id  === "utm_campaign"
                  || $id  === "utm_source"
                  || $id  === "utm_medium"
                  || $id  === "utm_term"
                  || $id  === "utm_content"){
                  $params[$id] = $field;
              }
          }
      }

      wp_remote_post( CUSTOMERLY_API_BASE_URL."leads", [
            "method" => "POST",
            "headers" => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authentication' => 'AccessToken: ' . $customerly_api_key
            ],
            "body" => json_encode(array(
                "leads" => array(
                    array(
                    "email" => $email,
                    "name" => $name,
                    "attributes" => array_merge($params, $fields)
                ))
            )),
            'httpversion' => '1.0',
            'timeout' => 60,
        ]);

    }

    public function on_export( $element ) {
        return $element;
    }

    public function register_settings_section( $widget ) {

        $widget->start_controls_section(
            'customerly_action',
            [
                'label' => esc_html__( 'Customerly', 'customerly' ),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'customerly_api_key',
            [
                'label' => esc_html__( 'API Key', 'customerly' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__( 'Use this field to set a custom API Key for the current form. More details at go.customerly.io/apikey', 'elementor-pro' ),
            ]
        );

        $widget->add_control(
            'customerly_track_UTMs',
            [
                'label' => esc_html__( 'Track UTMs', 'customerly' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__( 'When enabled all the UTMs will be tracked as Contact Properties', 'elementor-pro' ),
            ]
        );

        $widget->add_control(
            'customerly_track_everything',
            [
                'label' => esc_html__( 'Track all URL Params', 'customerly' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__( 'When enabled all other URL params will be tracked as Contact Properties', 'elementor-pro' ),
            ]
        );

        $widget->end_controls_section();
    }


}