<?php
/*
 * ThemeEngine v1.0.0 - PHP theming engine for ArcaneCMS
 *
 * Derived from Enrober by Peter Johnson.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @package arc-themeengine
 * @author Peter Johnson, Max Fierke
 * @copyright Copyright (c) 2011, Peter Johnson, Max Fierke
 * @version 1.0.0
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link http://www.uselesscode.org/php/themeengine/
 */

/**
 */


require_once('./includes/class.config.php');


/**
 * @ignore
 * Filter the body and template after they have been combined
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_ALL', 0);
/**
 * @ignore
 * Filter the header data after tags have been injected
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_HEAD', 1);
/**
 * @ignore
 * Filter just the body of the calling page
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_BODY', 2);
/**
 * @ignore
 * Filter the footer data after tags have been injected
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_FOOT', 3);
/**
 * @ignore
 * Filter the whole page after it has been combined
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_EVERYTHING ', 4);

/**
 * @ignore
 * Filters to be applied to XHR requests before they are returned.
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_XHR', 5);
/**
 * @ignore
 * Filters header, allways applied at request time, even on cached items.
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_ALWAYS_HEAD', 6);
/**
 * @ignore
 * Filters content, allways applied at request time, even on cached items.
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_ALWAYS_BODY', 7);
/**
 * @ignore
 * Filters footer, allways applied at request time, even on cached items.
 * @set add_filter()
 */
define('THEMEENGINE_FILTER_ALWAYS_FOOT', 8);

/**
 * @package themeengine
 * @subpackage classes
 */
class ThemeEngine {
	/**
	 * Once an instance of the ThemeEngine class is created using the singleton() method,
	 * it is stored in $instance.
	 * @private
	 */
	private static $instance;

	/**
	 * @var string The currently selected theme
	 */
	private $theme_name = null;

	/**
	 * @var string The title of the page
	 */
	private $page_title = null;

	/**
	 * Array of CSS files to link
	 */
	private $css_stack = array();

	/**
	 * Array of JavaScript files to link
	 */
	private $js_stack = array();

	/**
	 * Array of filters to apply to the body of the page.
	 */
	private $filter_body_stack = array();

	/**
	 * Array of filters to apply to the header.
	 */
	private $filter_head_stack = array();

	/**
	 * Array of filters to apply to the footer.
	 */
	private $filter_foot_stack = array();

	/**
	 * Array of filters to apply to the entire page after it's been buffered.
	 */
	private $filter_everything_stack = array();

	/**
	 * Array of filters to apply to XHR requests.
	 */
	private $filter_xhr_stack = array();

	/**
	 * Array of filters to apply just before the page is served, even on cached files.
	 */
	private $filter_always_head_stack = array();

	/**
	 * Array of filters to apply just before the page is served, even on cached files.
	 */
	private $filter_always_body_stack = array();

	/**
	 * Array of filters to apply just before the page is served, even on cached files.
	 */
	private $filter_always_foot_stack = array();

	/**
	 * Array of tags that will be processed in the template header.
	 */
	private $tag_stack_head = array();

	/**
	 * Array of tags that will be processed in the template footer.
	 */
	private $tag_stack_foot = array();

	/**
	 * Array of modifiers that will be used in generating the cache file filename.
	 */
	private $modifiers = array();

	/**
	 * String of keywords for a <meta> tag
	 */
	private $keywords = '';

	/**
	 * String of the description for a <meta> tag
	 */
	private $desc = '';

	 /**
	 * The canonical URL for this page
	 */
	private $url = null;

	/**
	 * Creates the $themeengine singleton object.
	 * Automatically called when themeengine.php is included.
	 * The created object is assigned to the $themeengine variable.
	 * Because ThemeEngine is a singleton, further calls to singleton()
	 * will result in a reference to the original object instead
	 * of a new instance.
	 * @returns object
	 */
	public static function singleton() {
		if(!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	/**
	 * @ignore
	 */
	public function __clone() {
		trigger_error('ThemeEngine can not be cloned', E_USER_ERROR);
	}

	/**
	 * Override the default theme.
	 *
	 * Can only be called once, subsequent calls will be ignored.  If no $theme is provided, the default
	 * theme is explicitly set as the theme.
	 *
	 * Certain methods (for example {@link is_cached} and {@link get_cache_file_path}) require that the theme be 
	 * set before they are called or they may raise warnings or errors, in these 
	 * cases call {@link set_theme}() (with no parameters if you want to use the default 
	 * theme) to avoid errors.
	 *
	 * If you are using set_theme(), it must be called before go(). go() uses the default theme if one has not been
	 * set before go() is called.
	 *
	 * <code>
	 * $themeengine->set_theme('pressy'); // Change the theme from the default to the 'pressy' theme
	 * $themeengine->go();
	 *
	 * // or
	 * $themeengine->set_theme(); // Explicitly use the default theme.
	 * $themeengine->go();
	 * </code>
	 *
	 * @param string $theme A string containing the name of the theme to wrap the page in.  If not provided the value of the ARCANE_DEFAULT_THEME constant from the config file is used.
	 * @since 1.1.0 Previously the theme could be set as a second parameter to {@link go()}, but it is now required that you use set_theme().
	 */
	public function set_theme($theme = null) {
		if ($this->theme_name === null) {
			if ($theme === null) {
				$theme = ARCANE_DEFAULT_THEME;
			}
			// Theme names can only contain a-z, A-Z, 0-9, underscore and dash to protect
			// against dir traversal in $theme in case users set the theme with
			// a var that can be manipulated by an attacker
			$theme = preg_replace('/[^a-z-_A-Z0-9]/', '', $theme);
		 	if (in_array($theme, $this->get_themes())) {
				$this->theme_name = $theme;
			} else {
				trigger_error('ThemeEngine was unable to find the theme "' . $theme . '"', E_USER_ERROR);
			}
		}
	}

	/**
	 * Returns a list of themes currently available.
	 * <code>
	 * <?php
	 * echo '<ul>';
	 * // list currently installed themes
	 * foreach($themeengine->get_themes() as $t) {
	 * 	echo "<li>$t</li>";
	 * }
	 * echo '</ul>';
	 * ?>
	 * </code>
	 * @returns array An array containing the names of all of the themes installed in this copy of ThemeEngine
	 * @since 1.1.0
	 */
	public function get_themes() {
		static $themes = null;
		$out = array();

		if ($themes === null) {
			$d = dir(DOC_ROOT . DIRECTORY_SEPARATOR .'themes' . DIRECTORY_SEPARATOR);
			while (($item = $d->read()) !== false) {
				if ($item !== '.' && $item !== '..' && is_dir($d->path . $item)) {
					$out[] = $item;
				}
			}
			$d->close();
		}
		return $out;
	}

	/**
	 * Returns the title of the page as it was set with go()
	 * Useful inside of XHR filters if transforming data into another format (JSON, XML, etc).
	 * @returns string
	 * @since 1.2.0
	 */
	public function get_title() {
		return $this->page_title;
	}

	/**
	 * Determines if the current request is being served as a XHR request.
	 *
	 * @since 1.2.0
	 * @returns boolean
	 */
	function is_xhr () {
			if (THEMEENGINE_X_REQUESTED_WITH === true && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
				return true;
			} else {
				return false;
			}
	}

 	/**
	 * Figures out the file system path to the current script
	 * @private
	 * @returns string
	 */
	private function get_script_path() {
		$path = realpath($_SERVER['SCRIPT_FILENAME']);
		// realpath can return a funky mixture of / and \ paths
		// make sure the entire path usues the correct DIRECTORY_SEPARATOR
		return preg_replace('/\/|\\\/', DIRECTORY_SEPARATOR, $path); // replaces / or \ with DIRECTORY_SEPARATOR
	}

	/**
	 * Adds <meta> keywords to the page.
	 * Multiple calls append to the list.
	 * @param mixed $keywords What to put in the <meta> keywords tag.  Either a string of comma delimited keywords or an array of keywords
	 *
	 * <code>
	 * $themeengine->add_keywords('keyword1,keyword2');
	 * $themeengine->add_keywords(array('keyword3', 'keyword4'));
	 * // the %keywords% template tag will now output
	 * // <meta name="keywords" content="keyword1,keyword2,keyword3,keyword4">
	 * </code>
	 */
	public function add_keywords($keywords) {
		if ($this->keywords !== '' && $keywords !== '') {
			$this->keywords .= ',';
		}

		if (is_array($keywords)) {
			$this->keywords .= implode(',', $keywords);
		} else {
			// assume it's a string
			$this->keywords .= $keywords;
		}
	}

	/**
	 * Adds a <meta> description to the page.
	 * Calling add_desc() a second time will replace any previous values.
	 *
	 * @param string $desc What to put in the <meta> description tag
	 * <code>
	 * $themeengine->add_desc('This is a page description.');
	 * $themeengine->add_desc('This description just replaced the old one.');
	 * </code>
	 */
	public function add_desc($desc) {
		$this->desc = $desc;
	}

	/**
	 * Invokes ThemeEngine's page wrapping; invoke after calling any of the other ThemeEngine methods you want to use and before page content starts.
	 *
	 * If you have not already added a {@link http://us.php.net/manual/en/function.ob-gzhandler.php ob_gzhandler} output buffer when go() is invoked, ThemeEngine will automatically call ob_start('ob_gzhandler').
	 *
	 * <code>
	 * <?php
	 * $themeengine->go(); // Invoke ThemeEngine without a page title
	 * $themeengine->go('Example'); // Invoke ThemeEngine with the page title 'Example'
	 * ?>
	 * </code>
	 *
	 * @param string $title If specified this string will be inserted into a <title> tag
	 */
	public function go($title = '') {

		$this->page_title = $title;

		if ($this->theme_name === null) {
			$this->set_theme(ARCANE_DEFAULT_THEME);
		}

		$theme = $this->theme_name;

		if (defined('ARCANE_SITE_NAME') && ARCANE_SITE_NAME !== null) {
			$this->add_tag('site_name', ARCANE_SITE_NAME, 1);
		}

		if (defined('ARCANE_TAGLINE') && ARCANE_TAGLINE !== null) {
			$this->add_tag('tagline', ARCANE_TAGLINE, 1);
		}

		$desc = $this->desc;
		$keywords = $this->keywords;

		// normal
		$js = $this->get_js();
		$css = $this->get_css();

		$url = $this->url;

		$theme_fs_path = DOC_ROOT . DIRECTORY_SEPARATOR . "themes" . DIRECTORY_SEPARATOR . "$theme" . DIRECTORY_SEPARATOR;
		$theme_uri_path = WEB_ROOT . DIRECTORY_SEPARATOR . "themes" . DIRECTORY_SEPARATOR . "$theme";

		if ($title !== '') {
			$title = '<title>' . (htmlentities($title)) . '</title>';
			$this->add_tag('title', $title, 1, false);
		}

		if ($desc !== '') {
			$this->add_tag('desc', '<meta name="description" content="' . htmlentities($desc) . '">', 1, false);
		}

		if ($keywords !== '') {
			$this->add_tag('keywords', '<meta name="keywords" content="' . htmlentities($keywords) . '">', 1, false);
		}

		$generated = 'Page generated ' . date(DATE_RFC822);

		if ($js !== '') {
			$this->add_tag('js', $js, 0, false);
		}

		if ($css !== '') {
			$this->add_tag('css', $css, 1, false);
		}

		if ($url !== null) {
			$this->add_tag('canonical', '<link rel="canonical" href="' . $url . '">', 1, false);
		}

		$this->add_tag('themepath', $theme_uri_path, 0, false);
		$this->add_tag('generated', $generated , 0);

		$tag_head_stack = $this->tag_stack_head;
		$tag_foot_stack = $this->tag_stack_foot;

		$filter_head_stack = &$this->filter_head_stack;
		$filter_foot_stack = &$this->filter_foot_stack;
		$filter_body_stack = &$this->filter_body_stack;

		$filter_everything_stack = &$this->filter_everything_stack;

		$filter_xhr_stack = &$this->filter_xhr_stack;

		$filter_always_head_stack = &$this->filter_always_head_stack;
		$filter_always_foot_stack = &$this->filter_always_foot_stack;
		$filter_always_body_stack = &$this->filter_always_body_stack;

		$theme_config = file_exists($theme_fs_path . 'config.json') ? json_decode(file_get_contents($theme_fs_path . 'config.json')) : array();

		// function that takes some text $data and applies each of the $filters
		$apply_filters = function(&$data, $filters) {
			// Apply each filter in the filters stack
			for($i = 0, $len = count($filters); $i < $len; $i++) {
				$new_data = call_user_func($filters[$i], $data);
				if ($new_data !== false) {
					$data = $new_data;
				}
			}
			return $data;
		};

		$get_header = function() use ($theme_config, $theme_fs_path, $tag_head_stack, &$filter_head_stack, $apply_filters) {
			static $header = null;

			if ($header === null) {
				// If head is defined in the config file use that or default
				$head_file = isset($theme_config->head) ? $theme_config->head : 'header.php';
				// Load header
				$header = file_get_contents($theme_fs_path . $head_file);

				// Process all of the %tags% for the head
				if(count($tag_head_stack) > 0) {
					foreach($tag_head_stack as $tag) {
						$header = str_replace('%' . $tag['name'] . '%', $tag['value'], $header);
					}
				}
				// Remove misc add_tag tags that were not processed
				$header = preg_replace('/%[^%\s]*%/', '', $header);

				$header = $apply_filters($header, $filter_head_stack);
			}

			return $header;
		};

		// The callback must be an anonymous function so we can use closure to pass 
		// variables into the callback.
		//
		// Reminder: In PHP anonymous functions are early binding, pass variables
		// by reference if they are prone to change after the function's definition
		$enrobe_callback = function ($body) use ($get_header, $theme_fs_path,
			$tag_head_stack, $tag_foot_stack,
			&$filter_head_stack, &$filter_body_stack, &$filter_foot_stack, &$filter_xhr_stack,
			&$filter_everything_stack,
			&$filter_always_head_stack, &$filter_always_body_stack, &$filter_always_foot_stack,
			$theme_config,
			$apply_filters
		) {

			$body = $apply_filters($body, $filter_body_stack);

			// If X-requested-with support is turned on and this is an XHR request
			if (THEMEENGINE_X_REQUESTED_WITH === true && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
				// Set empty footer
				$header = ''; // Needs to be here since outside the callback $header is not set if it is XHR.
				$footer = '';
				$filter_always_foot_stack = array(); // ignore any foot filters since we don't have a foot
				$body = $apply_filters($body, $filter_xhr_stack);
			} else {
				$header = $get_header();
				// If is defined in the config file use that or default
				$foot_file = isset($theme_config->foot) ? $theme_config->foot : 'footer.php';
				// Non-XHR, load footer a usual
				$footer = file_get_contents($theme_fs_path . $foot_file);

				// Process all of the %tags% for the foot
				if(count($tag_foot_stack) > 0) {
					foreach($tag_foot_stack as $tag) {
						$footer = str_replace('%' . $tag['name'] . '%', $tag['value'], $footer);
					}
				}

				// Remove misc add_tag tags that were not processed
				$footer = preg_replace('/%[^%\s]*%/', '', $footer);

				$footer = $apply_filters($footer, $filter_foot_stack);
			}

			// Cache page
			if (count($filter_everything_stack) <= 0) {
				$body = $apply_filters($body, $filter_always_body_stack);
				$footer = $apply_filters($footer, $filter_always_foot_stack);

				// Head has already been printed, output the body/foot*/
				return $body . $footer;
			} else {
				// Return it with chunkers in it so that always filters can be run after the everything filters are applied
				return $header . '<!--themeengine>chunk-->' . $body . '<!--themeengine>chunk-->' . $footer;
			}
		}; // Anon function, the ; is needed  here!

			// Maintenance mode is off or this page is allowed during maintenance, 
			// proceed as usual.

			// If it's not an XHR request
			if (!(THEMEENGINE_X_REQUESTED_WITH === true && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {

				// If there are any filter everything filters defined start an output buffer to process them.
				// This affects the page content, the header and footer all at once.  Since it has to
				// buffer the whole page it prevents early head flushing!!  Use with caution.
				if (count($this->filter_everything_stack) > 0) {
					$filter_everything_stack = &$this->filter_everything_stack;
					$filter_evertying = function($data) use ($apply_filters, &$filter_everything_stack, &$filter_always_head_stack, &$filter_always_body_stack, &$filter_always_foot_stack)
					{
						$data = $apply_filters($data, $filter_everything_stack);
						$chunks = explode('<!--themeengine>chunk-->', $data);

						$header = $apply_filters($chunks[0], $filter_always_head_stack);
						$body = $apply_filters($chunks[1], $filter_always_body_stack);
						$footer = $apply_filters($chunks[2], $filter_always_foot_stack);

						return $header . $body . $footer;
					}; // Anon function, the ; is needed
					ob_start($filter_evertying);
				} else {
					$header = $get_header();

					$header = $apply_filters($header, $filter_always_head_stack);

					// Flush the head early
					echo $header;
					ob_flush();
					flush();
				}
			}
			ob_start($enrobe_callback); 
		}
		// Function ends, normal page logic takes over; ob_ callbacks end when page ends
	

	/**
	 * Adds a CSS file to be inserted at the %css% tag.
	 * If a stylesheet is added a second time it is ignored.
	 *
	 * <code>
	 * <?php
	 * // Linked as a regular stylesheet
	 * $themeengine->add_css('extra.css');
	 *
	 * // Linked as a print-only stylesheet
	 * $themeengine->add_css('print.css', array('media' => 'print');
	 *
	 * // Linked as an alternative stylesheet
	 * $themeengine->add_css('alternate.css', array('rel' => 'alt', 'title' => 'Alternate layout');
	 *
	 * // Not added because it is a duplicate
	 * $themeengine->add_css('extra.css');
	 * ?>
	 * </code>
	 *
	 * @param string $filepath The url to the css file to be included.  If the css file is in the same directory as the calling script only the filename needs to be specified.
	 * @param array $config An array that allows you to add additional optional attributes to the link tag.
	 * These attributes are:
	 * <ul>
	 * <li>'media': Which media types the stylesheet should be applied to, 'all' by default.</li>
	 * <li>'rel': The rel attribute of the <link> tag, 'stylesheet' by default can also be set to 'alternate stylesheet' to specify an alternate stylesheet.  If set to 'alt' it will automatically be expanded to 'alternate stylesheet'.</li>
	 * <li>'title': The title attribute for the link, most browsers that support alternative stylesheets will use this as a label in the stylesheet switching interface.</li>
	 * </ul>
	 *
	 */
	public function add_css($filepath, $config = null) {

		static $files = array();

		if (!is_array($config)) {
			$config = array();
		}

		if (array_key_exists('rel', $config)) {
			// Allow $rel to be set to 'alt' as a shortcut for 'alternate stylesheet'
			if ($config['rel'] === 'alt') {
				$config['rel'] = 'alternate stylesheet';
			}
		} else {
			$config['rel'] = 'stylesheet';
		}

		if (!array_key_exists('media', $config)) {
			$config['media'] = 'all';
		}

		if (!array_key_exists('title', $config)) {
			$config['title'] = 'Stylesheet #' . count($this->css_stack) + 1;
		}

		$new_item = array('file' => $filepath, 'media' => $config['media'], 'rel' => $config['rel'], 'title' => $config['title']);

		/* Don't add if it's already in the stack */
		if (!in_array($filepath, $files)) {
			$this->css_stack[] = $new_item;
			$files[] = $filepath;
		}
	}

	/**
	 * Adds a JavaScript file to be linked via the %js% tag.
	 * If a script is added a second time it is ignored.
	 * 
	 * @param string $filepath The url of the JavaScript file to add absolute or relative to the calling script.
	 *
	 * <code>
	 * <?php
	 * $themeengine->add_js('http://cdn.example.com/1.5/jquery.min.js'); // Import jQuery from a CDN
	 * $themeengine->add_js('blink.js'); // Add a JavaScript file that is in the same
	 *                               // directory as the page using it.
	 * ?>
	 * </code>
	 */
	public function add_js($filepath) {
		/* Don't add if it's already in the stack */
		if (!in_array($filepath, $this->js_stack)) {
			$this->js_stack[] = $filepath;
		}
	}

	/**
	 * Adds a content filter to be applied with ob_start()
	 *
	 * @param string $filter A string with the name of the filter function to use
	 * @param bool $filter_mode What part of the page should be filtered?
	 * <ul>
	 * <li>THEMEENGINE_FILTER_BODY - Filter the body of the page.  If no filter mode is specified, this is used as the default.</li>
	 * <li>THEMEENGINE_FILTER_HEAD - Filter the header of the template.</li>
	 * <li>THEMEENGINE_FILTER_FOOT - Filter the footer of the template.</li>
	 * <li>THEMEENGINE_FILTER_ALL - Adds a head, body and foot filter with one command.</li>
	 * <li>THEMEENGINE_FILTER_EVERYTHING - Buffers the entire page and then applies your filter to the whole thing at once.</li>
	 * <li>THEMEENGINE_FILTER_ALWAYS_HEAD - Filter the  head.  Happens at request time, even on cached pages.</li>
	 * <li>THEMEENGINE_FILTER_ALWAYS_BODY - Filter the  body.  Happens at request time, even on cached pages.</li>
	 * <li>THEMEENGINE_FILTER_ALWAYS_FOOT - Filter the  foot.  Happens at request time, even on cached pages.</li>
	 * <li>THEMEENGINE_FILTER_ALWAYS_ALL - Adds a head, body and foot filter with one command.  Happens at request time, even on cached pages.</li>
	 * <li>THEMEENGINE_FILTER_XHR - Before an XHR request is returned it is run through any XHR filters.  Ideal for returning XHR requests in different formats (JSON, XML, etc.)</li>
	 * <li>false - Synonym for THEMEENGINE_FILTER_BODY (deprecated)</li>
	 * <li>true - Synonym for THEMEENGINE_FILTER_ALL (deprecated)</li>
	 * </ul>
	 *
	 * Note: If a request is an XHR request, it is filtered with any body filters, all filters, XHR filters and always filters.  Evertything filters are not run on XHR requests.  Also "always" filters are always run last so care must be taken that you don't corrupt any data transformations done by a XHR filter, for instance insert non-escaped characters into JSON, or search and replace tag names or property names in XML, etc.
	 *
	 * @since 1.2.0 More granularity in where filters are applied. True/false have been deprecated.
	 *
	 * <code>
	 * <?php
	 * // Apply the HTML Tidy callback to the body of the
	 * // page to fix any broken markup (Requires the Tidy
	 * // extension for PHP).  The entire document is processed as one piece.
	 * $themeengine->add_filter('ob_tidyhandler', THEMEENGINE_FILTER_EVERYTHING);
	 *
	 * // This filter function collapses any whitespace
	 * // between HTML tags down to a single space
	 * function collapse_whitespace($in) {
	 * 	return preg_replace('/>\s+</','> <', $in); 
	 * }
	 *
	 * // add our custom filter, filtering everything
	 * // including the header and footer.  Each section is filtered by itself.
	 * $themeengine->add_filter('collapse_whitespace', THEMEENGINE_FILTER_ALL);
	 *
	 * // Censor the words foo, bar and baz.
	 * function censor($in) {
	 * 	$out = str_replace('foo', '***', $in);
	 * 	$out = str_replace('bar', '***', $out);
	 * 	$out = str_replace('baz', '***', $out);
	 * 	return $out;
	 * }
	 *
	 * $themeengine->add_filter('censor'); // Implicitly only filtering the body of the page.
	 * ?>
	 * </code>
	 */
	public function add_filter($filter, $filter_mode = THEMEENGINE_FILTER_BODY) {

		// Don't allow gzipping since we already do that automatically
		if ($filter === 'ob_gzhandler') {
			trigger_error("Can not add ob_gzhandler as a filter, ThemeEngine automatically wraps your page with it already.", E_USER_WARNING);
			return;
		}	

		if ($filter_mode === THEMEENGINE_FILTER_ALL) {
			$this->filter_head_stack[] = $filter;
			$this->filter_body_stack[] = $filter;
			$this->filter_foot_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_BODY) {
			$this->filter_body_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_HEAD) {
			$this->filter_head_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_FOOT) {
			$this->filter_foot_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_XHR) {
			$this->filter_xhr_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_ALWAYS_HEAD) {
			$this->filter_always_head_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_ALWAYS_BODY) {
			$this->filter_always_body_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_ALWAYS_FOOT) {
			$this->filter_always_foot_stack[] = $filter;
		} elseif ($filter_mode === THEMEENGINE_FILTER_ALWAYS_ALL) {
			$this->filter_always_head_stack[] = $filter;
			$this->filter_always_body_stack[] = $filter;
			$this->filter_always_foot_stack[] = $filter;
		} elseif ($filter_mode === false) {
			$this->filter_body_stack[] = $filter;
		} else if ($filter_mode === THEMEENGINE_FILTER_EVERYTHING || $filter_mode === true) {
			$this->filter_everything_stack[] = $filter;
		} else {
		}
	}

	/**
	 * Adds a new theme tag.
	 *
	 *
	 * Theme tags allow you to specify where things ThemeEngine generates are injected into the theme. There are a number of built in tags:
	 * <ul>
	 * <li>%title% - The page's <title> tag</li>
	 * <li>%desc% - The page's description <meta> tag</li>
	 * <li>%keywords% - The page's keywords <meta> tag</li>
	 * <li>%themepath% - The URI for the directory that the current theme is stored in, useful for including the theme's CSS file.</li>
	 * <li>%css% - Any <link> tags for CSS files added via $themeengine->add_css()</li>
	 * <li>%js% - Any <script> tags for JavaScript files added via $themeengine->add_js()</li>
	 * <li>%generated% - Inserts a "Generated on" or "Cached on" message depending on whether or not caching is enabled on the current page.</li>
	 * <li>%canonical% - Where in the head to insert the Canonical URL if one was set with $themeengine->url()</li>
	 * <li>%site_name% - The value for ARCANE_SITE_NAME specified in themeengine-config.php</li>
	 * <li>%tagline% - The value for THEMEENGINE_TAGLINE specified in themeengine-config.php</li>
	 * </ul>
	 * 
	 * If you add your own tags to your templates you can then set their value for any particular page with add_tag().
	 * 
	 *
	 * <code>
	 * <?php
	 * $themeengine->add_tag('foo', 'bar'); // Adds the text "bar" anywhere in the 
	 *                                  // header or footer that "%foo%" appears
	 * $themeengine->add_tag('foo', 'bar', 0); // Same as previous
	 * $themeengine->add_tag('foo', 'bar', 1); // Adds the text "bar" only in the header
	 * $themeengine->add_tag('foo', 'bar', 2); // Adds the text "bar" only in the footer
	 *
	 * // By default anything added to a theme tag is run through htmlentities()
	 * // to prevent XSS attacks. If you need to use HTML you can set the $sanitize
	 * // parameter to false but if you are including any user generated content
	 * // in it you will need to sanitize it yourself!
	 * $themeengine->add_tag('foo', '<em>bar</em>'); // Adds the text "bar", wrapped
	 *                                           // in a text representation of em tags:
	 *                                           // "&lt;em&gt;bar&lt;/em&gt;"
	 * $themeengine->add_tag('foo', '<em>bar</em>', 0, true); // Same as previous
	 * $themeengine->add_tag('foo', '<em>bar</em>', 0, false); // Adds the text "bar",
	 *                                                     // wrapped in em tags:
	 *                                                     // "<em>bar</em>"
	 * ?>
	 * </code>
	 *
	 * @param string $name The name of the theme tag to define (sans %%)
	 * @param string $value The value to insert at the new theme tag
	 * @param integer $location Where to process the tag.  0 = Header and Footer, 1 = Header only, 2 = Footer only
	 * @param bool $sanitize If true, the $value is run through htmlentities() before inclusion in the page. Set to false if you need to include HTML, but you will need to sanitize any user input you include yourself!
	 */
	public function add_tag($name, $value, $location = 0, $sanitize = true) {

		$name = preg_replace('/^%|%$/', '', $name);

		if ($sanitize !== false) {
			$value = htmlentities($value);
		}

		$new_item = array('name' => $name, 'value' => $value);

		if ($location === 0 || $location === 1) {
			$this->tag_stack_head[] = $new_item;
		}
		if ($location === 0 || $location === 2) {
			$this->tag_stack_foot[] = $new_item;
		}
	}

	/**
	 * Sets/gets the canonical URL for the page to be inserted at the %canonical% tag.
	 *
	 * If called with no arguments or with null, returns the current canonical url. 
	 * If an empty string, removes the canonical url.  If it is anything else it 
	 * is set as the canonical url.
	 *
	 * Google's webmaster central has more information about {@link http://www.google.com/support/webmasters/bin/answer.py?answer=139066 canonical URLs}.
	 *
	 * <code>
	 * $themeengine->url('index.php');
	 * // or
	 * $themeengine->url('/');
	 * // or
	 * $themeengine->url('http://www.example.com/');
	 * </code>
	 *
	 * @param string $url The url to set as the canonical url.
	 */
	function url($url = null) {
		if ($url === null) {
			return $this->url;
		} else if ($url === '') {
			$this->url = null;
		} else {
			$this->url = $url;
		}
	}

	/**
	 * Returns a list of <link> tags for each CSS file in the css stack.
	 * @returns string
	 */
	private function get_css() {
		$out = '';
		foreach($this->css_stack as $stylesheet) {
			$out .= '<link href="' . $stylesheet['file'] . '" rel="' . $stylesheet['rel'] . '" type="text/css" media="' . $stylesheet['media'] . '" title="' . $stylesheet['title'] . '">';
		}
		return $out;
	}

	/**
	 * Returns a string containing <script> tags to add to the document via the %js% tag
	 * @returns string
	 */
	private function get_js() {
		$out = '';
		foreach($this->js_stack as $js_file) {
			$out .= '<script src="' . $js_file . '" type="text/javascript"></script>';
		}
		return $out;
	}
}
// Create our $themeengine object for scripts to invoke
$themeengine = ThemeEngine::singleton();
?>
