<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Model_TweetInfo extends Zend_Db_Table_Abstract {
    protected $_name = 'tweet_info';
    protected $_primary = 'song_id';
    protected $_sequence = false;
}
