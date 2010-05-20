<?php
//require_once $THINKTANK_CFG['source_root_path'].'webapp/model/OAuth.php';

/**
 * Twitter OAuth class mock
 */
class TwitterOAuth {/*{{{*/
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {/*{{{*/
    }


    function oAuthRequest($url, $args = array(), $method = NULL) {/*{{{*/
        global $SOURCE_ROOT_PATH;
        $FAUX_DATA_PATH = $SOURCE_ROOT_PATH . 'webapp/plugins/twitter/tests/testdata/';
        $url = str_replace('https://twitter.com/', '', $url);
        $url = str_replace('http://search.twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        //echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$url;
        return file_get_contents($FAUX_DATA_PATH.$url);
    }

    function http($url) {/*{{{*/
        global $SOURCE_ROOT_PATH;
        $FAUX_DATA_PATH = $SOURCE_ROOT_PATH . 'webapp/plugins/twitter/tests/testdata/';
        $url = str_replace('https://twitter.com/', '', $url);
        $url = str_replace('http://search.twitter.com/', '', $url);
        $url = str_replace('/', '_', $url);
        $url = str_replace('&', '-', $url);
        $url = str_replace('?', '-', $url);
        //echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH.$url;
        return file_get_contents($FAUX_DATA_PATH.$url);
    }

    function lastStatusCode() {
        return 200;
    }
}
?>
