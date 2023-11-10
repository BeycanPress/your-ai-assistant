<?php 

namespace BeycanPress\YAIA\Pages;

use BeycanPress\YAIA\Lang;
use BeycanPress\YAIA\PluginHero\Page;

class ChatGPT extends Page
{   
    /**
     * Class construct
     * @return void
     */
    public function __construct()
    {
        parent::__construct([
            'pageName' => esc_html__('Your AI Assistant', 'yaia'),
            'subMenuPageName' => esc_html__('Chat GPT', 'yaia'),
            'subMenu' => true,
            'icon' => $this->getImageUrl('logo.png'),
        ]);

        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    /**
     * Enqueue scripts
     * @return void
     */
    public function enqueueScripts()
    {
        $this->addStyle('css/chat-gpt.css');
        $this->addScript('js/chat-gpt.js');
    }

    /**
     * @return void
     */
    public function page()
    {
        $this->viewEcho('chat-gpt-be');
    }
}