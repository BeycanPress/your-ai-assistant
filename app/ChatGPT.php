<?php

namespace BeycanPress\YAIA;

class ChatGPT 
{
    use PluginHero\Helpers;

    /**
     * @return void
     */
    public function __construct()
    {
        add_action('init', function() {
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
            add_shortcode('yaia-chat-gpt', [$this, 'chatGPT']);
        });
    }

    /**
     * Enqueue scripts
     * @return void
     */
    public function enqueueScripts()
    {
        $this->addStyle('chat-gpt.css');
        $key = $this->addScript('chat-gpt.js', ['jquery']);
        wp_localize_script($key, 'YAIA', [
            'bot' => $this->getImageUrl('bot.svg'),
            'user' => $this->getImageUrl('user.svg'),
            'lang' => Lang::get(),
            'apiUrl' => admin_url('admin-ajax.php'),
            'nonce'  => $this->createNewNonce(),
        ]);
    }

    /**
     * @return void
     */
    public function chatGPT()
    {
        return $this->view('chat-gpt-fe', [], ['form' => true]);
    }
}