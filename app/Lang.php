<?php

namespace BeycanPress\YAIA;

class Lang
{
    public static function get()
    {
        return [
            "contentCreationPleaseWait" => esc_html__('Content creation please wait...', 'yaia'),
            "imagesCreationPleaseWait" => esc_html__('Images creation please wait...', 'yaia'),
            "somethingWentWrong" => esc_html__('Something went wrong!', 'yaia'),
            'saveAll' => esc_html__('Save all', 'yaia'),
            'saveImage' => esc_html__('Save image', 'yaia'),
            'imageSaveProcess' => esc_html__('Image is being saved please wait...', 'yaia'),
            'imagesSaveProcess' => esc_html__('Images is being saved please wait...', 'yaia'),
            'commandRunning' => esc_html__('Command running please wait...', 'yaia'),
            'errors' => esc_html__('Errors', 'yaia'),
        ];
    }

}