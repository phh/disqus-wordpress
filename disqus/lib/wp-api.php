<?php
/**
 * Implementation of the Disqus API designed for WordPress.
 *
 * @author        Disqus <help@disqus.com>
 * @copyright    2007-2013 Disqus
 * @link        http://disqus.com/
 * @package        Disqus
 * @subpackage    DisqusWordPressAPI
 * @version        2.0
 */

require_once(ABSPATH.WPINC.'/http.php');
require_once(dirname(__FILE__) . '/api/disqusapi/disqusapi.php');

/** @#+
 * Constants
 */
/**
 * Base URL for Disqus.
 */
define('DISQUS_ALLOWED_HTML', '<b><u><i><h1><h2><h3><code><blockquote><br><hr>');

/**
 * Helper methods for all of the Disqus v3 API methods.
 *
 * @package        Disqus
 * @subpackage    DisqusWordPressAPI
 * @author        DISQUS.com <help@disqus.com>
 * @copyright    2007-2013 Disqus
 * @version        2.0
 */
class DisqusWordPressAPI {
    function DisqusWordPressAPI($dsq_secret_key=null) {
        $this->dsq_secret_key = $dsq_secret_key;
        $this->api = new DisqusAPI($secret_key);
    }

    function get_forum_list() {
        return $this->api->users->listForums();
    }
    
    function get_forum_posts($start_id=0) {
        $response = $this->api->get_forum_posts(null, array(
            'filter' => 'approved',
            'start_id' => $start_id,
            'limit' => 100,
            'order' => 'asc',
            'full_info' => 1
        ));
        return $response;
    }

    function import_wordpress_comments(&$wxr, $timestamp, $eof=true) {
        $http = new WP_Http();
        $response = $http->request(
            DISQUS_IMPORTER_URL . 'api/import-wordpress-comments/',
            array(
                'method' => 'POST',
                'body' => array(
                    'forum_url' => $this->short_name,
                    'forum_api_key' => $this->forum_api_key,
                    'response_type'    => 'php',
                    'wxr' => $wxr,
                    'timestamp' => $timestamp,
                    'eof' => (int)$eof
                )
            )
        );
        if ($response->errors) {
            // hack
            $this->api->last_error = $response->errors;
            return -1;
        }
        $data = unserialize($response['body']);
        if (!$data || $data['stat'] == 'fail') {
            return -1;
        }
        
        return $data;
    }
}

?>
