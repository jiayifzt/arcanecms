<?php
    // Track your page with...
    // Stats::track($some_page_title);

    class Stats
    {
        private static $me;

        private function __construct()
        {

        }

        public function getStats()
        {
            if(is_null(self::$me))
                self::$me = new Stats();
            return self::$me;
        }

        public static function track($page_title = '')
        {
            $db = Database::getDatabase();

            $dt               = dater();
            $referer          = getenv('HTTP_REFERER');
            $referer_is_local = self::refererIsLocal($referer);
            $url              = full_url();
            $search_terms     = self::searchTerms();
            $img_search       = '';
            $ip               = self::getIP();
            $info             = getBrowser();
            $browser_family   = $info['name'];
            $browser_version  = $info['version'];
            $os               = $info['platform'];
            $os_version       = '';
            $user_agent       = $info['userAgent'];

            $exec_time = defined('START_TIME') ? microtime(true) - START_TIME : 0;
            $num_queries = $db->numQueries();

            $sql = "INSERT INTO stats (dt, referer, referer_is_local, url, page_title, search_terms, img_search, browser_family, browser_version, os, os_version, ip, user_agent, exec_time, num_queries)
                    VALUES (:dt, :referer, :referer_is_local, :url, :page_title, :search_terms, :img_search, :browser_family, :browser_version, :os, :os_version, :ip, :user_agent, :exec_time, :num_queries)";
            $vals = array('dt'               => $dt,
                          'referer_is_local' => $referer_is_local,
                          'referer'          => $referer,
                          'url'              => $url,
                          'page_title'       => $page_title,
                          'search_terms'     => $search_terms,
                          'img_search'       => $img_search,
                          'ip'               => $ip,
                          'browser_family'   => $browser_family,
                          'browser_version'  => $browser_version,
                          'os_version'       => $os_version,
                          'os'               => $os,
                          'user_agent'       => $user_agent,
                          'exec_time'        => $exec_time,
                          'num_queries'      => $num_queries);
            $db->query($sql, $vals);
        }

        public static function refererIsLocal($referer = null)
        {
            if(is_null($referer)) $referer = getenv('HTTP_REFERER');
            if(!strlen($referer)) return 0;
            $regex_host = preg_quote(getenv('HTTP_HOST'));
            return (preg_match("!^https?://$regex_host!i", $referer) !== false) ? 1 : 0;
        }

        public static function getIP()
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            if(!$ip) $ip = getenv('HTTP_CLIENT_IP');
            if(!$ip) $ip = getenv('REMOTE_ADDR');
            return $ip;
        }

        public static function searchTerms($url = null)
        {
            if(is_null($url)) $url = full_url();
            // if(self::refererIsLocal($url)) return;

            $arr = array();
            parse_str(parse_url($url, PHP_URL_QUERY), $arr);

            return isset($arr['q']) ? $arr['q'] : '';
        }
    }
?>
