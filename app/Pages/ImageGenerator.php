<?php 

namespace BeycanPress\YAIA\Pages;

use BeycanPress\YAIA\PluginHero\Page;

class ImageGenerator extends Page
{   
    /**
     * Class construct
     * @return void
     */
    public function __construct()
    {
        parent::__construct([
            'pageName' => esc_html__('YAIA Image generator', 'yaia'),
            'parent' => 'upload.php'
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