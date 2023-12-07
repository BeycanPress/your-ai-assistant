<?php

namespace BeycanPress\YAIA\PluginHero;

class Updater
{
    use Helpers;
    
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $updaterApi = 'https://updater.beycanpress.net';

	/**
     * @param array $params
     */
	public function __construct(array $data = []) 
    {
		global $pagenow;

        if ($pagenow == 'update-core.php' || $pagenow == 'plugins.php' || $pagenow == 'update.php') {
			try {
				$ch = curl_init($this->updaterApi);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$response = curl_exec($ch);

				curl_close($ch);
				
				$exists = ($response !== false);
			} catch (\Throwable $th) {
				$exists = false;
			}	
        } else {
            $exists = true;
        }

        if ($this->setting('license') && $exists) {
            $this->data = $data;

            /* Updater Config */
            $this->config = array(
                'server'  => $this->updaterApi,
                'id'      => $this->data['plugin_file'],
                'api'     => $this->data['plugin_version'],
            );

            add_action('admin_init', array($this, 'adminInit'));
            add_filter('upgrader_post_install', array($this, 'installFolder'), 11, 3);
        }
	}

	/**
	 * Admin Init.
	 * Some functions only available in admin.
	 */
	public function adminInit()
    {
		add_filter('plugins_api_result', array($this, 'pluginInfo'), 10, 3);
		add_filter('pre_set_site_transient_update_plugins', array($this, 'addPluginUpdateData'), 10, 2);
	}

	/**
     * @param object $value
     * @return object
     */
	public function addPluginUpdateData(object $value) : object 
    {
		if(isset($value->response)){
			$updateData = $this->getData('query_plugins');
			foreach ($updateData as $plugin => $data) {
				if (isset($data['new_version'], $data['slug'], $data['plugin'])) {
					$value->response[$plugin] = (object) array_merge($data, $this->data);
				} else {
					unset($value->response[$plugin]);
				}
			}
		}

		return $value;
	}

    /**
     * @param object $res
     * @param string $action
     * @param object $args
     * @return object
     */
	public function pluginInfo(object $res, string $action, object $args) : object
    {
        $slug = dirname($this->config['id']);
        $listPlugins = array($slug => $this->config['id']);

		/* If in our list, add our data. */
		if ('plugin_information' == $action && isset($args->slug) && array_key_exists($args->slug, $listPlugins)) {

			$info = $this->getData('plugin_information', $listPlugins[$args->slug]);

			if (isset($info['name'], $info['slug'], $info['external'], $info['sections'])) {
				$res = (object) $info;
			}
		}

		return $res;
	}

	/**
     * @param string $action
     * @param string $plugin
     * @return array
     */
	public function getData(string $action, string $plugin = '') : array 
    {
		/* Get WP Version */
		global $wp_version;

		/* Remote Options */
		$body = [];
		if ('query_plugins' == $action) {
			$body['plugins'] = get_plugins();
		} elseif ('plugin_information' == $action){
			$body['plugin'] =  $plugin;
		}

		$options = array(
			'timeout'    => 20,
			'body'       => $body,
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url'),
		); 

		/* Remote URL */
		$urlArgs = array(
			'bp_updater' => $action,
			'plugin'     => $this->config['id'],
		);
		$server = set_url_scheme($this->config['server'], 'http');
		$url = $httpUrl = add_query_arg($urlArgs, $server);
		if (wp_http_supports(array('ssl'))) {
			$url = set_url_scheme($url, 'https');
		}

		/* Try HTTPS */
		$rawResponse = wp_remote_post(esc_url_raw($url), $options);

		/* Fail, try HTTP */
		if (is_wp_error($rawResponse)) {
			$rawResponse = wp_remote_post(esc_url_raw($httpUrl), $options);
		}

		/* Still fail, bail. */
		if (is_wp_error($rawResponse) || 200 != wp_remote_retrieve_response_code($rawResponse)) {
			return array();
		}

		/* return array */
		$data = json_decode(trim(wp_remote_retrieve_body($rawResponse)), true);
		return is_array($data) ? $data : array();
	}

	/**
     * @param boolean $true
     * @param array $hookExtra
     * @param array $result
     * @return boolean
     */
	public function installFolder(bool $true, array $hookExtra, array $result) : bool
    {
		if (isset($hookExtra['plugin'])) {
			global $wp_filesystem, $hook_suffix;
			$properDestination = trailingslashit($result['local_destination']) . dirname($hookExtra['plugin']);
			$wp_filesystem->move($result['destination'], $properDestination);
			$result['destination'] = $properDestination;
			$result['destination_name'] = dirname($hookExtra['plugin']);
			if (
                'update.php' == $hook_suffix && 
                isset($_GET['action'], $_GET['plugin']) && 
                'upgrade-plugin' == $_GET['action'] && 
                $hookExtra['plugin'] == $_GET['plugin']
            ) {
				activate_plugin($hookExtra['plugin']);
			}
		}

		return $true;
	}

}
