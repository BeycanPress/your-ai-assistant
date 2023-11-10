<?php 

namespace BeycanPress\YAIA\Pages;

use BeycanPress\YAIA\PluginHero\Page;

class ImageGenerator2 extends Page
{   
    /**
     * Class construct
     * @return void
     */
    public function __construct()
    {
        parent::__construct([
            'pageName' => esc_html__('Image generator', 'yaia'),
            'parent' => $this->pages->ChatGPT->slug
        ]);
    }

    /**
     * @return void
     */
    public function page()
    {
        $this->viewEcho('pages/image-generator');
    }
}