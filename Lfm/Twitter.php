<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Twitter {
    static public function factory() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $config = Lfm_Config::getInstance();

        $oauth_consumer =
            new Zend_Oauth_Consumer(
                array(
                    'consumerKey'       => (string)$config->twitter->consumer->key,
                    'consumerSecret'    => (string)$config->twitter->consumer->secret));
        $oauth_access_token = new Zend_Oauth_Token_Access();
        $oauth_access_token->setParams(
            array(
                Zend_Oauth_Token::TOKEN_PARAM_KEY           => (string)$config->twitter->access->token,
                Zend_Oauth_Token::TOKEN_SECRET_PARAM_KEY    => (string)$config->twitter->access->secret));
        return
            new Zend_Service_Twitter(
                array('accessToken' => $oauth_access_token),
                $oauth_consumer);
    }
}
