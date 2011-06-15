<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_ShortUrl {
    static public function shorten(Zend_Uri $uri) {
        echo __METHOD__ . '() ... ';
        $t = microtime(true);
        try {
            $config = Lfm_Config::getInstance();
            $req_uri =
                sprintf(
                    'http://api.j.mp/shorten?%s',
                    http_build_query(
                        array(
                            'version'   => '2.0.1',
                            'longUrl'   => $uri->__toString(),
                            'login'     => (string)$config->bitly->login,
                            'apiKey'    => (string)$config->bitly->apikey,
                            'format'    => 'json')));
            $client = new Zend_Http_Client($req_uri);
            $resp = $client->request('GET');
            if($resp->isSuccessful()) {
                $data = Zend_Json::decode($resp->getBody(), Zend_Json::TYPE_OBJECT);
                if(isset($data->results)) {
                    foreach($data->results as $result) {
                        if(isset($result->shortUrl)) {
                            try {
                                $result = Zend_Uri::factory($result->shortUrl);
                                printf("%.3f s\n", microtime(true) - $t);
                                return $result;
                            } catch(Exception $e) {
                            }
                        }
                    }
                }
            }
        } catch(Exception $e) {
        }
        printf("%.3f s\n", microtime(true) - $t);
        return null;
    }
}
