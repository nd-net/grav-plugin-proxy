<?php
/**
 * The Proxy Part of this plugin is based on https://github.com/softius/php-cross-domain-proxy
 */
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class ProxyPlugin
 * @package Grav\Plugin
 */
class ProxyPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onPageInitialized' => ['onPageInitialized', 0]
        ]);
    }
    
    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onPageInitialized()
    {
        $grav = $this->grav;
        $debugger = $grav[ 'debugger' ];
        $header = $grav[ 'page' ]->header();
        if (!isset($header->proxy)) {
	        return;
	    }
        $request_url = $header->proxy;
        if (!filter_var($request_url, FILTER_VALIDATE_URL)) {
	        $debugger->addMessage('Proxy url is not a valid url.');
	        return;
		}
		
		$curl_options = array(
		    // CURLOPT_SSL_VERIFYPEER => false,
		    // CURLOPT_SSL_VERIFYHOST => 2,
		);
		
		$request_headers = array( );
		foreach ($_SERVER as $key => $value) {
		    if (strpos($key, 'HTTP_') === 0  ||  strpos($key, 'CONTENT_') === 0) {
		        $headername = str_replace('_', ' ', str_replace('HTTP_', '', $key));
		        $headername = str_replace(' ', '-', ucwords(strtolower($headername)));
		        if (!in_array($headername, array( 'Host' ))) {
		            $request_headers[] = "$headername: $value";
		        }
		    }
		}
		
		// identify request method, url and params
		$request_method = $_SERVER['REQUEST_METHOD'];
		if ('GET' == $request_method) {
		    $request_params = $_GET;
		} elseif ('POST' == $request_method) {
		    $request_params = $_POST;
		    if (empty($request_params)) {
		        $data = file_get_contents('php://input');
		        if (!empty($data)) {
		            $request_params = $data;
		        }
		    }
		} elseif ('PUT' == $request_method || 'DELETE' == $request_method) {
		    $request_params = file_get_contents('php://input');
		} else {
		    $request_params = null;
		}
		
		// append query string for GET requests
		if ($request_method == 'GET' && count($request_params) > 0 && (!array_key_exists('query', $p_request_url) || empty($p_request_url['query']))) {
		    $request_url .= '?' . http_build_query($request_params);
		}
		// let the request begin
		$ch = curl_init($request_url);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);   // (re-)send headers
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // return response
		curl_setopt($ch, CURLOPT_HEADER, true);       // enabled response headers
		// add data for POST, PUT or DELETE requests
		if ('POST' == $request_method) {
		    $post_data = is_array($request_params) ? http_build_query($request_params) : $request_params;
		    curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_POSTFIELDS,  $post_data);
		} elseif ('PUT' == $request_method || 'DELETE' == $request_method) {
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
		}
		
		// Set multiple options for curl according to configuration
		if (is_array($curl_options) && 0 <= count($curl_options)) {
		    curl_setopt_array($ch, $curl_options);
		}
		
		// retrieve response (headers and content)
		$response = curl_exec($ch);
		curl_close($ch);
		
		// split response to header and content
		list($response_headers, $response_content) = preg_split('/(\r\n){2}/', $response, 2);
		
		// (re-)send the headers
		$response_headers = preg_split('/(\r\n){1}/', $response_headers);
		foreach ($response_headers as $key => $response_header) {
		    // Rewrite the `Location` header, so clients will also use the proxy for redirects.
		    if (preg_match('/^Location:/', $response_header)) {
		        list($header, $value) = preg_split('/: /', $response_header, 2);
		        $response_header = 'Location: ' . $_SERVER['REQUEST_URI'] . '?csurl=' . $value;
		    }
		    if (!preg_match('/^(Transfer-Encoding):/', $response_header)) {
		        header($response_header, false);
		    }
		}
		// finally, output the content
		print($response_content);
		exit;
    }
}
