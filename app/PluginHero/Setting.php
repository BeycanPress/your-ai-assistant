<?php

namespace BeycanPress\YAIA\PluginHero;

use \CSF;

abstract class Setting
{
    use Helpers;

    /**
     * @var string
     */
    private static $prefix;

    /**
     * @param string $title
     * @param string|null $parent
     * @return void
     */
    public function __construct(string $title, string $parent = null)
    {
        self::$prefix = $this->settingKey;

        $params = array(

            'framework_title'         => $title . ' <small>By BeycanPress</small>',

            // menu settings
            'menu_title'              => $title,
            'menu_slug'               => self::$prefix,
            'menu_capability'         => 'manage_options',
            'menu_position'           => 999,
            'menu_hidden'             => false,

            // menu extras
            'show_bar_menu'           => false,
            'show_sub_menu'           => false,
            'show_network_menu'       => true,
            'show_in_customizer'      => false,

            'show_search'             => true,
            'show_reset_all'          => true,
            'show_reset_section'      => true,
            'show_footer'             => true,
            'show_all_options'        => true,
            'sticky_header'           => true,
            'save_defaults'           => true,
            'ajax_save'               => true,
            
            // database model
            'transient_time'          => 0,

            // contextual help
            'contextual_help'         => array(),

            // typography options
            'enqueue_webfont'         => false,
            'async_webfont'           => false,

            // others
            'output_css'              => false,

            // theme
            'theme'                   => 'dark',

            // external default values
            'defaults'                => array(),

        );

        if (!is_null($parent)) {
            $params['menu_type'] = 'submenu';
            $params['menu_parent'] = $parent;
        }

        CSF::createOptions(self::$prefix, $params);

        $this->licenseControls();
    }

    /**
     * @param array $params
     * @return void
     */
    public static function createSection(array $params)
    {
        CSF::createSection(self::$prefix, $params);
    }

    /**
     * @param null|string $key
     * @return mixed
     */
    public static function get(?string $key = null)
    {
        return Plugin::$instance->setting($key);
    }

    /**
     * @return void
     */
    private function licenseControls() : void
    {
        if ($licenseCode = self::get('license')) {
            if ($expireTime = get_option($this->pluginKey . '_licenseExpireTime')) {
                if (time() > strtotime($expireTime)) {
                    self::deleteLicense();
                }
            }

            if (date('Y-m-d') != get_option($this->pluginKey . '_dailyLicenseCheck')) {
                if (!$this->checkLicense($licenseCode)->success) {
                    self::deleteLicense();
                }
            }
        }
    }

    /**
     * @param string $licenseCode
     * @return object
     */
    private function checkLicense(string $licenseCode) : object
    {
        $data = $this->verifyLicense($licenseCode, $this->pluginKey);
        update_option($this->pluginKey . '_licenseData', $data->data);
        update_option($this->pluginKey . '_dailyLicenseCheck', date('Y-m-d'));

        if (isset($data->data->expireTime) && $data->data->expireTime) {
            update_option($this->pluginKey . '_licenseExpireTime', $data->data->expireTime);
        }

        return $data;
    }

    /**
     * @return void
     */
    private function deleteLicense(): void
    {
        $settings = self::get();
        if (isset($settings['license'])) {
            unset($settings['license']);
            update_option($this->settingKey, $settings);
            delete_option($this->pluginKey . '_licenseExpireTime');
            delete_option($this->pluginKey . '_dailyLicenseCheck');
        }
    }

    /**
     * It checks the validity of the purchase licenseCode you entered and returns true false.
     * 
     * @param string $licenseCode
     * @param string|null $productcode
     * @return object
     */
    public function verifyLicense(string $licenseCode, ?string $productcode = null) : ?object
    {
        $headers = ["Content-Type: application/json"];

        $curl = curl_init('https://beycanpress.com/?rest_route=/licensor-api/verify');
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_REFERER => $_SERVER["SERVER_NAME"],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode([
                "licenseCode" => trim($licenseCode),
                "productCode" => trim($productcode)
            ]),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $resp = json_decode(curl_exec($curl));

        curl_close($curl);
        
        return $resp ? $resp : null;
    }
    
    /**
     * @return void
     */
    protected function licensed() : void
    {
        add_action("csf_".self::$prefix."_save_after", function($data, $opt) {
            if (isset($opt->errors['license'])) self::deleteLicense();
        }, 10, 2);

        self::createSection(array(
            'id'     => 'license', 
            'title'  => esc_html__('License'),
            'icon'   => 'fa fa-key',
            'priority' => 99999,
            'fields' => array(
                array(
                    'id'    => 'license',
                    'type'  => 'text',
                    'title' => esc_html__('License (Purchase code)'),
                    'sanitize' => function($val) {
                        return sanitize_text_field($val);
                    },
                    'validate' => function($val) {
                        $val = sanitize_text_field($val);
                        if (empty($val)) {
                            return esc_html__('License cannot be empty.');
                        } elseif (strlen($val) < 36 || strlen($val) > 36) {
                            return esc_html__('License must consist of 36 characters.');
                        }

                        $data = $this->checkLicense($val);
                        
                        if (!$data->success) {
                            return esc_html__($data->message . " - Error code: " . $data->errorCode);
                        }
                    }
                ),
            ) 
        ));
    }
}