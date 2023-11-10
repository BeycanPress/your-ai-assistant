<?php

namespace BeycanPress\YAIA;

use CSF;

class MetaBox {

    use PluginHero\Helpers;

    /**
     * @var array
     */
    private $allowedPostTypes = [
        'post', 
        'page', 
        'product'
    ];

    /**
     * Class construct
     * @return void
     */
	public function __construct() 
    {
        if ($this->setting('enableYaia')) {

            $prefix = 'yaiaMetabox';

            CSF::createMetabox($prefix, array(
                'title'     => esc_html__('Your AI Assistant - Content generator', 'yaia'),
                'post_type' => $this->allowedPostTypes,
                'context'   => 'normal',
                'priority'  => 'high',
            ) );
    
            CSF::createSection($prefix, array(
                'fields' => array(
                    array(
                        'id'      => 'customPromptS',
                        'title'   => esc_html__('I want to create a prompt myself', 'yaia'),
                        'type'    => 'switcher',
                        'default' => false,
                    ),
                    array(
                        'id'      => 'customPrompt',
                        'type'    => 'text',
                        'title'   => esc_html__('Custom prompt', 'yaia'),
                        'desc'    => esc_html__('GPT-3 will be sent directly to the prompt you enter here.', 'yaia'),
                        'dependency' => array('customPromptS', '==', 'true'),
                    ),
                    array(
                        'id'     => 'options',
                        'type'   => 'fieldset',
                        'dependency' => array('customPromptS', '==', 'false'),
                        'fields' => array(
                            array(
                                'id'      => 'title',
                                'type'    => 'text',
                                'title'   => esc_html__('Title', 'yaia'),
                                'desc'    => esc_html__('If you leave this blank, the WordPress title you entered will be selected. If you want to enter a special title, you can enter it here.', 'yaia')
                            ),
                            array(
                                'id'      => 'language',
                                'type'    => 'select',
                                'title'   => esc_html__('Language', 'yaia'),
                                'options' => [
                                    'english'    => esc_html__('English', 'yaia'),
                                    'dutch'      => esc_html__('Dutch', 'yaia'),
                                    'french'     => esc_html__('French', 'yaia'),
                                    'german'     => esc_html__('German', 'yaia'),
                                    'hindi'      => esc_html__('Hindi', 'yaia'),
                                    'indonesian' => esc_html__('Indonesian', 'yaia'),
                                    'italian'    => esc_html__('Italian', 'yaia'),
                                    'japanese'   => esc_html__('Japanese', 'yaia'),
                                    'arabic'     => esc_html__('Arabic', 'yaia'),
                                    'chinese'    => esc_html__('Chinese', 'yaia'),
                                    'korean'     => esc_html__('Korean', 'yaia'),
                                    'polish'     => esc_html__('Polish', 'yaia'),
                                    'portuguese' => esc_html__('Portuguese', 'yaia'),
                                    'russian'    => esc_html__('Russian', 'yaia'),
                                    'spanish'    => esc_html__('Spanish', 'yaia'),
                                    'turkish'    => esc_html__('Turkish', 'yaia'),
                                    'ukranian'   => esc_html__('Ukranian', 'yaia'),
                                ]
                            ),
                            array(
                                'id'      => 'paragraphsCount',
                                'type'    => 'select',
                                'title'   => esc_html__('Paragraphs count', 'yaia'),
                                'options' => [
                                    'unlimited'  => esc_html__('Unlimited', 'yaia'),
                                    '1'  => esc_html__('1', 'yaia'),
                                    '2'  => esc_html__('2', 'yaia'),
                                    '3'  => esc_html__('3', 'yaia'),
                                    '4'  => esc_html__('4', 'yaia'),
                                    '5'  => esc_html__('5', 'yaia'),
                                    '6'  => esc_html__('6', 'yaia'),
                                    '7'  => esc_html__('7', 'yaia'),
                                    '8'  => esc_html__('8', 'yaia'),
                                    '9'  => esc_html__('9', 'yaia'),
                                    '10' => esc_html__('10', 'yaia'),
                                ]
                            ),
                            array(
                                'id'      => 'addHeadings',
                                'type'    => 'select',
                                'title'   => esc_html__('Add headings to paragraph', 'yaia'),
                                'options' => [
                                    'no'  => esc_html__('No', 'yaia'),
                                    'h1'  => esc_html__('h1', 'yaia'),
                                    'h2'  => esc_html__('h2', 'yaia'),
                                    'h3'  => esc_html__('h3', 'yaia'),
                                    'h4'  => esc_html__('h4', 'yaia'),
                                    'h5'  => esc_html__('h5', 'yaia'),
                                    'h6'  => esc_html__('h6', 'yaia'),
                                ]
                            ),
                            array(
                                'id'      => 'writingStyle',
                                'type'    => 'select',
                                'title'   => esc_html__('Writing Style', 'yaia'),
                                'options' => [
                                    'standart'    => esc_html__('Standart', 'yaia'),
                                    'informative' => esc_html__('Informative', 'yaia'),
                                    'descriptive' => esc_html__('Descriptive', 'yaia'),
                                ]
                            ),
                            array(
                                'id'      => 'addIntroduction',
                                'title'   => esc_html__('Add Introduction', 'yaia'),
                                'type'    => 'switcher',
                                'default' => false
                            ),
                            array(
                                'id'      => 'addConclusion',
                                'title'   => esc_html__('Add Conclusion', 'yaia'),
                                'type'    => 'switcher',
                                'default' => false
                            ),
                        ),
                    ),
                    array(
                        'id'      => 'generateImage',
                        'title'   => esc_html__('Generate Image', 'yaia'),
                        'type'    => 'switcher',
                        'default' => false,
                    ),
                    array(
                        'id'      => 'imagePrompt',
                        'title'   => esc_html__('Image prompt', 'yaia'),
                        'type'    => 'text',
                        'default' => false,
                        'dependency' => array('generateImage', '==', 'true'),
                    ),
                    array(
                        'id'      => 'imageCount',
                        'type'    => 'number',
                        'default' => 1,
                        'title'   => esc_html__('Image count', 'yaia'),
                        'dependency' => array('generateImage', '==', 'true'),
                    ),
                    array(
                        'id'      => 'imageSizes',
                        'type'    => 'select',
                        'title'   => esc_html__('Image sizes', 'yaia'),
                        'default' => '512x512',
                        'options' => [
                            '256x256'   => '256x256',
                            '512x512'   => '512x512',
                            '1024x1024' => '1024x1024',
                        ],
                        'dependency' => array('generateImage', '==', 'true'),
                    ),
                    array(
                        'id'         => 'createArticle',
                        'type'       => 'content',
                        'title'      => esc_html__('Create content', 'yaia'),
                        'content'    => '<a href="#" class="button button-primary" id="yaiaCreateArticle">'.esc_html__('Create content', 'yaia').'</a>',
                    ),
                )
            ));
        }
	}

}
