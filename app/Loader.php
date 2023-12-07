<?php

namespace BeycanPress\YAIA;

use BeycanPress\YAIA\PluginHero\Plugin;

class Loader extends Plugin
{
    /**
     * @var array
     */
    private $loadScriptThisPages = [
        'post.php',
        'post-new.php'
    ];

    public function __construct($pluginFile)
    {
        parent::__construct([
            'pluginFile' => $pluginFile,
            'pluginKey' => 'yaia',
            'textDomain' => 'yaia',
            'settingKey' => 'yaiaSettings',
            'pluginVersion' => '1.3.1'
        ]);

        if (!$this->setting('openAiApiKey')) {
            $this->adminNotice(esc_html__('Your AI Assitant: The plugin does not work because you did not enter the OpenAI API code.', 'yaia'), 'error');
        }

        if (is_admin()) {
            new MetaBox();
        }

        new AiService();

        $this->integrateElementor();
    }

    public function frontendProcess()
    {
        new ChatGPT();
    }

    public function adminProcess()
    {
        new Pages\ChatGPT();
        new Pages\TextEditor();
        new Pages\ImageGenerator();
        new Pages\ImageGenerator2();
        new Pages\OtherPlugins($this->pluginFile);

        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);

        add_action('init', function(){
            new Settings();
        }, 9);
    }

    /**
     * @return void
     */
    public function enqueueScripts() : void
    {
        global $pagenow;

        if (in_array($pagenow, $this->loadScriptThisPages) || $this->isYaiaPages()) {
            $this->addStyle('admin.css');
            $this->addScript('sweetalert2.js');
            $jsKey = $this->addScript('main.js', ['jquery']);
            $this->loadJsVariable($jsKey);
        }
    }

    /**
     * @param string $key
     * @return void
     */
    private function loadJsVariable(string $key) : void
    {
        wp_localize_script($key, 'YAIA', [
            'bot' => $this->getImageUrl('bot.svg'),
            'user' => $this->getImageUrl('user.svg'),
            'lang' => Lang::get(),
            'apiUrl' => admin_url('admin-ajax.php'),
            'nonce'  => $this->createNewNonce(),
        ]);
    }

    /**
     * @return boolean
     */
    private function isYaiaPages() : bool
    {
        return isset($_GET['page']) && strpos($_GET['page'], 'yaia') !== false;
    }

    public static function uninstall()
    {
        $settings = get_option(self::$instance->settingKey);
        if (isset($settings['dds']) && $settings['dds']) {
            delete_option(self::$instance->settingKey);
        }
    }

    private function integrateElementor()
    {
        add_action('wp_enqueue_scripts', function() {
            if (isset($_GET['action']) && $_GET['action'] == 'elementor') {
                $this->addStyle('admin.css');
            }
            
            $this->registerScript('js/sweetalert2.js', 'yaiaSweetAlert2');
            $this->registerScript('js/elementor.js', 'yaiaElementor', ['jquery', 'yaiaSweetAlert2']);
            $this->loadJsVariable('yaiaElementor');
        });
    
        add_action('plugins_loaded', function(){
            if (defined('ELEMENTOR_VERSION')) {
                add_action('elementor/widgets/register', function($widgetsManager) {
                    $widgetsManager->register(new Integrations\Elementor());
                });
            }
        });
    }
}
