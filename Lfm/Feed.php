<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Feed {
    const URL = 'http://ws.audioscrobbler.com/1.0/user/%s/recenttracks.rss';

    static public function get($lastfm_user_name) {
        $t = new Lfm_ProgressTimer(__METHOD__);

        // U+2013
        $title_separator = mb_convert_encoding(chr(0x00) . chr(0x00) . chr(0x20) . chr(0x13), 'UTF-8', 'UTF-32');

        $feed = Zend_Feed::import(sprintf(self::URL, rawurlencode($lastfm_user_name)));
        $result = new Lfm_Feed_Contents();
        foreach($feed as $entry) {
            try {
                $artist_title = explode($title_separator, $entry->title(), 2);
                if(count($artist_title) === 2) {
                    $song_uri = Zend_Uri::factory(trim((string)$entry->link()));
                    if(preg_match('!^/music/!', $song_uri->getPath())) {
                        $result->addSong(
                            new Lfm_Song(
                                new Lfm_Artist(trim($artist_title[0]), self::songUri2ArtistUri($song_uri)),
                                trim($artist_title[1]),
                                $song_uri,
                                new Lfm_Datetime((string)$entry->pubDate()),
                                Lfm_Uuid::factoryV5(Lfm_Uuid::NAMESPACE_URL, trim((string)$entry->guid()))));
                    }
                }
            } catch(Exception $e) {
            }
        }
        return $result;
    }

    static private function songUri2ArtistUri(Zend_Uri $song_uri) {
        $artist_uri = clone $song_uri;
        if(!preg_match('!^(/music/[^/]+)/_/!', $artist_uri->getPath(), $match)) {
            throw new Lfm_Exception('曲URIからアーティストURIへの変換に失敗');
        }
        $artist_uri->setPath($match[1]);
        return $artist_uri;
    }
}
