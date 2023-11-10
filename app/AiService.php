<?php

namespace BeycanPress\YAIA;

use \Beycan\Response;

class AiService
{
    use PluginHero\Helpers;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var object
     */
    private $params;

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var array
     */
    private $requestBody = [];

    /**
     * @var array
     */
    private $openAiModels = [
        'text'  => 'text-davinci-003',
        'image' => 'image-alpha-001',
        'text-edit'  => 'text-davinci-edit-001',
        'code-edit'  => 'code-davinci-edit-001'
    ];

    /**
     * @var string
     */
    private $requestApi = '';

    /**
     * @var string
     */
    private $apiKey = '';
    
    /**
     * @var array
     */
    private $openAiApis = [
        'text'  => 'https://api.openai.com/v1/completions',
        'image' => 'https://api.openai.com/v1/images/generations',
        'edit'  => 'https://api.openai.com/v1/edits'
    ];
    


    public function __construct()
    {
		$this->apiKey = $this->setting('openAiApiKey');
        $this->requestApi = $this->openAiApis['text'];
        $this->ajaxAction('yaiaCreateContent');
        $this->ajaxAction('yaiaImageGenerator');
        $this->ajaxAction('yaiaSaveImage');
        $this->ajaxAction('yaiaSaveImages');
        $this->ajaxAction('yaiaTextEditor');
        $this->ajaxAction('yaiaChatGPT');
    }

    public function yaiaChatGPT()
    {
        if (!$this->setting('openAiApiKey')) {
            Response::error(esc_html__('Please enter your OpenAI API key before!'));
        }

        if (!$prompt = $this->sanitizeTextField('prompt')) {
            Response::error(esc_html__('Please enter your prompt before!'));
        }
        
        $re = '/^\/imagine\s+((-[a-z]+:[0-9]+\s+)+)?(.+)$/';
        preg_match_all($re, $prompt, $matches, PREG_SET_ORDER, 0);

        if (empty($matches)) {
            $this->requestBody = [
                'model'             => $this->openAiModels['text'],
                'prompt'            => $prompt,
                'temperature'       => (float) $this->setting('realityAndCreativity'),
                'max_tokens'        => (int) $this->setting('maximumTokens'),
                'top_p'             => (float) $this->setting('randomnessOutput'),
                'frequency_penalty' => (float) $this->setting('frequencyPenalty'),
                'presence_penalty'  => (float) $this->setting('presencePenalty'),
            ];
            
            $this->parseResponse($this->sendRequest());
        } else {
            $prompt = trim($matches[0][3]);
            
            $this->requestApi = $this->openAiApis['image'];
            $this->requestBody = [
                'model'             => $this->openAiModels['image'],
                'prompt'            => $prompt,
            ];

            if (!empty($matches[0][1])) {
                $args = explode(' ', trim($matches[0][1]));
                foreach ($args as $arg) {
                    $arg = explode(':', $arg);
                    if ($arg[0] == '-count') {
                        $this->requestBody['n'] = intval($arg[1]);
                    } else {
                        $this->requestBody['size'] = $arg[1] . 'x' . $arg[1];
                    }
                }
            }
            $response = $this->sendRequest();

            if (isset($response->body->data)) {
                $imageCreated = true;
                foreach ($response->body->data as $image) {
                    $this->content .= "<img src='" . $image->url . "' alt='" . $prompt . "' />";
                }
            }
        }

        if (!empty($this->errors)) {
            Response::error(esc_html__('Errors'), $this->errors);
        }
        
        Response::success($this->content, [
            'imageCreated' => isset($imageCreated)
        ]);
    }
    
    /**
     * @return void
     */
    public function yaiaTextEditor()
    {
        if (!$this->setting('openAiApiKey')) {
            Response::error(esc_html__('Please enter your OpenAI API key before!'));
        }
        
        $input = $this->sanitizeTextField('input');
        $instruction = $this->sanitizeTextField('instruction');

        $this->requestApi = $this->openAiApis['text'];
        $this->requestBody = [
            'model'  => $this->openAiModels['text'],
            "prompt" => $input . " " . $instruction,
        ];

		$this->parseResponse($this->sendRequest());

        if (!empty($this->errors)) {
            Response::error(esc_html__('Errors'), $this->errors);
        }
        
        Response::success(esc_html__('Edit completed', 'yaia'), $this->content);
    }

    /**
     * @return void
     */
    public function yaiaSaveImage()
    {
        $image = $this->sanitizeUrlField('image') ?? '';

        try {
            $this->uploadImage($image);
            Response::success("Image saved successfully");
        } catch (\Throwable $th) {
            Response::error($th->getMessage());
        }
    }

    /**
     * @return void
     */
    public function yaiaSaveImages()
    {
        $images = $this->sanitizeUrlFieldInArray('images') ?? [];
        try {
            foreach ($images as $image) {
                $this->uploadImage($image);
            }
            Response::success("Images saved successfully");
        } catch (\Throwable $th) {
            Response::error($th->getMessage());
        }
    }

    /**
     * @return void
     */
    public function uploadImage(string $imageUrl)
    {
        $uploadDir = wp_upload_dir();

        $imageData = file_get_contents($imageUrl);

        $filename = "Image generator " . time() . rand(1, 100) . ".png";

        if (wp_mkdir_p($uploadDir['path'])) {
            $file = $uploadDir['path'] . '/' . $filename;
        } else {
            $file = $uploadDir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $imageData);

        $wpFileType = wp_check_filetype($filename, null);

        $attachment = array(
            'post_mime_type' => $wpFileType['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachId = wp_insert_attachment($attachment, $file);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachData = wp_generate_attachment_metadata($attachId, $file);
        wp_update_attachment_metadata($attachId, $attachData);

    }

    /**
     * @return void
     */
    public function yaiaImageGenerator()
    {
        if (!$this->setting('openAiApiKey')) {
            Response::error(esc_html__('Please enter your OpenAI API key before!'));
        }

        $prompt = $this->sanitizeTextField('prompt');
        $imageCount = $this->sanitizeNumberField('imageCount');
        $imageSizes = $this->sanitizeTextField('imageSizes');

        if (!$prompt) {
            Response::error(esc_html__('Please enter a prompt', 'yaia'));
        }

        $this->requestApi = $this->openAiApis['image'];
        $this->requestBody = [
            'model'  => $this->openAiModels['image'],
            "n"      => $imageCount,
            'prompt' => $prompt,
            'size'   => $imageSizes
        ];

        $response = $this->sendRequest();
        
        if (!empty($this->errors)) {
            Response::error(esc_html__('Errors'), $this->errors);
        }

        if (isset($response->status) && 200 === $response->status) {
            Response::success(esc_html__('Image generation completed', 'yaia'), $response->body->data);
        } else {
            Response::error(esc_html__('Image generation failed', 'yaia'), $response);
        }
    }

    /**
     * @return void
     */
    public function yaiaCreateContent()
    {
        $this->checkNonce();
        $this->prepareParams();

        if (!$this->setting('openAiApiKey')) {
            Response::error(esc_html__('Please enter your OpenAI API key before!'));
        }

		$this->requestBody = [
			'model'             => $this->openAiModels['text'],
			'prompt'            => $this->createPrompt(),
			'temperature'       => (float) $this->setting('realityAndCreativity'),
			'max_tokens'        => (int) $this->setting('maximumTokens'),
			'top_p'             => (float) $this->setting('randomnessOutput'),
			'frequency_penalty' => (float) $this->setting('frequencyPenalty'),
			'presence_penalty'  => (float) $this->setting('presencePenalty'),
        ];

		$this->parseResponse($this->sendRequest());

		if (!isset($this->params->customPrompt)) {
            if ($this->params->addIntroduction) {
                $this->requestBody['prompt'] = $this->createPrompt('introduction');
                $this->parseResponse($this->sendRequest(), true);
            }
    
            if ($this->params->addConclusion) {
                $this->requestBody['prompt'] = $this->createPrompt('conclusion');
                $this->parseResponse($this->sendRequest(), true);
            }
        }

        $this->generateImageForContent();

        if (!empty($this->errors)) {
            Response::error(esc_html__('Errors'), $this->errors);
        }

        Response::success(esc_html__('Content creation completed', 'yaia'), $this->content);
    }

    /**
     * @param string $writingStyle
     * @return array|null
     */
    private function getWritingStyle(string $writingStyle) : ?array
    {
        return [
            'introduction' => $writingStyle . ' introduction about of',
            'article'      => $writingStyle . ' article about of',
            'conclusion'   => $writingStyle . ' conclusion about of',
        ];
    }

    /**
     * @return void
     */
    private function generateImageForContent() : void
    {
        if ($this->params->generateImage) {
            $imgSubject = $this->params->customPrompt ?? $this->params->title;
            $this->requestApi = $this->openAiApis['image'];
            $this->requestBody = [
                'model'  => $this->openAiModels['image'],
                "n"      => $this->params->imageCount,
                'prompt' => $this->params->imagePrompt,
                'size'   => $this->params->imageSizes
            ];

            $response = $this->sendRequest();

            if (isset($response->status) && 200 === $response->status) {
                if ($images = $response->body->data ?? '') {
                    foreach ($images as $image) {
                        $imgresult = "<img src='" . $image->url . "' alt='" . $imgSubject . "' />";
                        $this->content .= "<br>" . $imgresult;
                    }
                }
            }
        }
    }

    /**
     * @return object|null
     */
    private function sendRequest() : ?object
    {
		$args = [
            'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->apiKey,
            ],
			'method'  => 'POST',
			'timeout' => 45,
            'body'    => wp_json_encode($this->requestBody)
        ];

		$response = wp_remote_post($this->requestApi, $args);
		if (!is_wp_error($response) && isset($response['response'])) {
			$response = (object) [
				'status'  => $response['response']['code'],
				'message' => $response['response']['message'],
				'code'    => $response['response']['code'],
				'body'    => json_decode($response['body']),
            ];
		}

        if (isset($response->body->error)) {
            // if (
            //     $response->body->error->type === 'insufficient_quota' || 
            //     isset($response->body->error->code) && $response->body->error->code === 'billing_hard_limit_reached'
            // ) {
            //     Response::error(esc_html__('The API has been closed due to too much spending. You can look at examples created by other users.'));
            // }
        }
        
        if (isset($response->status) && in_array($response->status, [400, 404]) || isset($response->body->error)) {
            $this->addError(isset($response->body->error->message) ? $response->body->error->message : esc_html__('Something went wrong!', 'yaia'));
		}

		return $response;
    }

    /**
     * @param object $response
     * @param bool $content
     * @return void
     */
    private function parseResponse(object $response, bool $content = false) : void
    {
        if (isset($response->status) && 200 === $response->status) {
			if (!empty($choices = $response->body->choices ?? [])) {
                if (!$content) {
                    foreach ($choices as $choice) {
                        $this->content .= $choice->text;
                    }
                } else {
                    $content = '';
                    foreach ($choices as $choice) {
                        $content .= $choice->text;
                    }
                    $this->content = $content . $this->content;
                }
			} else {
				$this->addError(esc_html__('Not enough choices generated', 'yaia'));
			}
		}
    }

    /**
     * @param string $message
     * @return void
     */
    private function addError(string $message) : void
    {
        $this->errors[] = $message;
    }

    /**
     * @param string|null $style
     * @return string
     */
    private function createPrompt(?string $style = null) : string
    {
        if (isset($this->params->customPrompt)) {
            return $this->params->customPrompt;
        }

        $prompt = '';

        if (gettype($style) == 'string' && $this->params->writingStyle !== 'standart') {
			$prompt .= $this->params->allWritingStyles[$style] . ' ' . $this->params->title . ' in ' . $this->params->language;
        } else {
            if ($this->params->paragraphsCount != 'unlimited') {
                $prompt .= strval($this->params->paragraphsCount) . ' paragraph on ';
            }
    
            if ($this->params->writingStyle != 'standart') {
                $promptTextArticle = $this->params->allWritingStyles['article'];
    
                $prompt .= $promptTextArticle . ' ' . $this->params->title . ' without introduction and conclusion ';
    
            } else {
                $prompt .= $this->params->title . ' ';
            }
    
            $prompt .= 'in ' . $this->params->language . ' language';
        }
    
        if ('no' != $this->params->addHeadings) {
            $prompt .= ' with headings wrapped in <' . $this->params->addHeadings . '>';
        }

        return $prompt;
    }

    /**
     * @return object
     */
    private function prepareParams() : object
    {
        $this->params = (object) [
            'title'           => $this->sanitizeTextField('title'),
            'language'        => $this->sanitizeTextField('language'),
            'paragraphsCount' => $this->sanitizeNumberField('paragraphsCount'),
            'addHeadings'     => $this->sanitizeTextField('addHeadings'),
            'writingStyle'    => $this->sanitizeTextField('writingStyle'),
            'addIntroduction' => $this->getBooleanValue('addIntroduction'),
            'addConclusion'   => $this->getBooleanValue('addConclusion'),
            'generateImage'   => $this->getBooleanValue('generateImage'),
            'customPromptS'   => $this->getBooleanValue('customPromptS')
        ];

        
        if ($this->params->generateImage) {
            $this->params->imagePrompt = $this->sanitizeTextField('imagePrompt');
            $this->params->imageCount = $this->sanitizeNumberField('imageCount');
            $this->params->imageSizes = $this->sanitizeTextField('imageSizes');
            if (!$this->params->imagePrompt) {
                Response::error(esc_html__('Please enter image prompt before!'));
            }
        }
        
        if ($this->params->customPromptS) {
            $this->params->customPrompt = $this->sanitizeTextField('customPrompt');
            if (!$this->params->customPrompt) {
                Response::error(esc_html__('Please enter prompt before!'));
            }
        } else {
            if (!$this->params->title) {
                Response::error(esc_html__('Please enter title before!'));
            }

            $this->params->allWritingStyles = $this->getWritingStyle($this->params->writingStyle);
        }

        return $this->params;
    }

    /**
     * @param string $key
     * @return boolean
     */
    private function getBooleanValue(string $key) : bool
    {
        return isset($_POST[$key]) && $_POST[$key] ? true : false;
    }

    /**
     * @param string $key
     * @return string|null
     */
    private function sanitizeTextField(string $key) : ?string
    {
        return isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : null;
    }

    /**
     * @param string $key
     * @return string|null
     */
    private function sanitizeUrlField(string $key) : ?string
    {
        return isset($_POST[$key]) ? esc_url_raw($_POST[$key]) : null;
    }

    /**
     * @param string $key
     * @return array|null
     */
    private function sanitizeUrlFieldInArray(string $key) : ?array
    {
        $newArray = [];
        if (isset($_POST[$key]) && is_array($_POST[$key])) {
            foreach ($_POST[$key] as $value) {
                $newArray[] = esc_url_raw($value);
            }
            return $newArray;
        } else {
            return null;
        }
    }

    /**
     * @param string $key
     * @return int|null
     */
    private function sanitizeNumberField(string $key) : ?int
    {
        return isset($_POST[$key]) ? absint($_POST[$key]) : null;
    }
}