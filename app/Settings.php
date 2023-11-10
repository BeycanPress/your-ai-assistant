<?php

namespace BeycanPress\YAIA;

use \BeycanPress\YAIA\PluginHero\Setting;

class Settings extends Setting
{
    use PluginHero\Helpers;

    public function __construct()
    {
        $parent = $this->pages->ChatGPT->slug;
        parent::__construct(esc_html__('Settings', 'yaia'), $parent);
        
        self::createSection(array(
            'id'     => 'generalOptions', 
            'title'  => esc_html__('General options', 'yaia'),
            'icon'   => 'fa fa-cog',
            'fields' => array(
                array(
                    'id'      => 'dds',
                    'title'   => esc_html__('Data deletion status', 'yaia'),
                    'type'    => 'switcher',
                    'default' => false,
                    'help'    => esc_html__('This setting is passive come by default. You enable this setting. All data created by the plug-in will be deleted while removing the plug-in.', 'yaia')
                ),
                array(
                    'id'      => 'openAiApiKey',
                    'title'   => esc_html__('API Key', 'yaia'),
                    'type'    => 'text',
                    'desc'    => "<a href='https://beta.openai.com/account/api-keys' target='_blank'>".esc_html__('Get API Key', 'yaia')."</a>"
                ),
            )
        ));

        self::createSection(array(
            'id'     => 'contentGenerator', 
            'title'  => esc_html__('Content generator', 'yaia'),
            'icon'   => 'fa fa-edit',
            'fields' => array(
                
                array(
                    'id'      => 'enableYaia',
                    'title'   => esc_html__('Enable OpenAI auto generate content post option', 'yaia'),
                    'type'    => 'switcher',
                    'default' => false
                ),
                array(
                    'id'      => 'realityAndCreativity',
                    'title'   => esc_html__('Reality and creativity', 'yaia'),
                    'type'    => 'number',
                    'default' => 0.7,
                    'desc'    => esc_html__('Higher values produce less accurate but varied and creative output. Lower values will produce more accurate and realistic results.', 'yaia')
                ),
                array(
                    'id'      => 'maximumTokens',
                    'title'   => esc_html__('Maximum tokens', 'yaia'),
                    'type'    => 'number',
                    'default' => 3000,
                    'max'     => 3000,
                    'desc'    => esc_html__('Use with "Reality and creativity" to test the authenticity and randomness of the result.', 'yaia')
                ),
                array(
                    'id'      => 'randomnessOutput',
                    'title'   => esc_html__('Randomness output', 'yaia'),
                    'type'    => 'number',
                    'default' => 1.0,
                    'desc'    => esc_html__('To control randomness of the output.', 'yaia')
                ),
                array(
                    'id'      => 'frequencyPenalty',
                    'title'   => esc_html__('Frequency Penalty', 'yaia'),
                    'type'    => 'number',
                    'default' => 0,
                    'desc'    => esc_html__('For improving the quality and coherence of the generated text.', 'yaia')
                ),
                array(
                    'id'      => 'presencePenalty',
                    'title'   => esc_html__('Presence Penalty', 'yaia'),
                    'type'    => 'number',
                    'default' => 0,
                    'desc'    => esc_html__('To produce more concise text.', 'yaia')
                )
            )
        ));

        self::createSection(array(
            'id'     => 'backup', 
            'title'  => esc_html__('Backup', 'yaia'),
            'icon'   => 'fa fa-shield',
            'fields' => array(
                array(
                    'type'  => 'backup',
                    'title' => esc_html__('Backup', 'yaia')
                ),
            ) 
        ));
    }
    
}