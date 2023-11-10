<?php defined('ABSPATH') || exit;

/**
 * Plugin Name:  Your AI Assistant
 * Version:      1.3.1
 * Plugin URI:   https://yaia.beycanpress.com
 * Description:  It assists you to take advantage of the capabilities of artificial intelligence such as GPT-3 for WordPress and DALL-E 2, making it easier for you to use these artificial intelligences. For example, creating ready-made articles and images.
 * Author URI:   https://beycanpress.com
 * Author:       BeycanPress LLC
 * Tags:         WordPress auto article generator, WordPress auto image generator, WordPress ai article generator, WordPress dall-e image generator, WordPress OpenAI GPT-3 article generator
 * Text Domain:  yaia
 * License:      GPLv3
 * License URI:  https://www.gnu.org/licenses/gpl-3.0.tr.html
 * Domain Path:  /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
*/

require __DIR__ . '/vendor/autoload.php';
new \BeycanPress\YAIA\Loader(__FILE__);