<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Feed_Contents implements Countable, IteratorAggregate {
    const UUID_DUMMY_ID_FOR_PLAYING = Lfm_Uuid::NIL_UUID;
    const UUID_NAMESPACE_ETAG       = '6c9d0780-5775-45bf-a859-c58d90e13c04';
    const UUID_NAMESPACE_SONG_ETAG  = 'a21d9658-4767-45f1-93ba-4af01481e912';

    private $data;  // array, 最近の曲が最初

    public function __construct() {
        $this->data = array();
    }

    public function count() {
        return count($this->data);
    }

    // 最近の曲が最後
    public function getIterator() {
        return new ArrayIterator($this->toArray());
    }

    // 最近の曲が最後
    public function toArray() {
        return array_reverse($this->data);
    }

    // RSSにあわせて最近の曲から追加
    public function addSong(Lfm_Song $song) {
        $this->data[] = $song;
        return $this;
    }

    public function getPlayingSong() {
        return count($this->data) > 0 ? $this->data[0] : null;
    }

    // 曲一覧から取得したコンテンツ識別タグ
    public function getETag() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $content_binary = '';
        foreach($this->data as $i => $song) {
            $content_binary .= $this->getSongPlayIdForETag($song, $i === 0)->binary;
        }
        return
            Lfm_Uuid::factoryV5(
                self::UUID_NAMESPACE_ETAG,
                $content_binary);
    }

    private function getSongPlayIdForETag(Lfm_Song $song, $is_playing) {
        $uuid_song = $song->getUuid();
        $uuid_play = $is_playing ? new Lfm_Uuid(self::UUID_DUMMY_ID_FOR_PLAYING) : $song->getPlayId();
        return
            Lfm_Uuid::factoryV5(
                self::UUID_NAMESPACE_SONG_ETAG,
                $uuid_song->binary . $uuid_play->binary);
    }
}
