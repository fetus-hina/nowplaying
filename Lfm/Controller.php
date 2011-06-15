<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
class Lfm_Controller {
    static public function run() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        // 再生履歴を取得し、DB を更新、最新の曲を取得する
        $songs = self::getSongsFromLastFm((string)Lfm_Config::getInstance()->lastfm->user_name);
        if(!$songs instanceof Lfm_Feed_Contents || count($songs) < 1) {
            echo "再生曲情報なし\n";
            return false;
        }
        if(!$playing = self::updatePlayDatas($songs)) {
            echo "再生曲情報なし\n";
            return false;
        }

        // ツイートに値する情報か確認する
        if(time() - $playing->song->getTime()->getInteger() >= 3 * 60) {
            echo "再生開始が昔すぎる\n";
            return false;
        }
        if($last_tweet_info = self::getLastTweetInfo()) {
            if(time() - $last_tweet_info->tweet_at->getInteger() < (int)Lfm_Config::getInstance()->lastfm->interval) {
                echo "前回のツイートから時間が経っていない\n";
                echo "    現在時間: " . Lfm_Datetime::factory(time())->__toString() . "\n";
                echo "    前回時間: " . $last_tweet_info->tweet_at->__toString() . "\n";
                return false;
            }
        }

        // 確認完了
        // ツイートの作成
        $tweet = self::buildTweet($playing, $last_tweet_info);
        if($tweet == '') {
            echo "ツイートの構築に失敗\n";
            return false;
        }

        // ツイート情報の保存
        self::setLastTweetInfo(
            $playing->song->getUuid(),
            new Lfm_Datetime(time()));

        for($i = 0; $i < 3; ++$i) {
            try {
                Lfm_Twitter::factory()->status->update($tweet);
                return;
            } catch(Zend_Exception $e) {
            }
            sleep(1);
        }
    }

    // Last.fm から最近の再生曲一覧を取得する
    static private function getSongsFromLastFm($user_name) {
        $t = new Lfm_ProgressTimer(__METHOD__);
        return Lfm_Feed::get($user_name);
    }

    // 最近の再生曲一覧を基に再生情報 DB を更新し、最新の再生曲を返す
    // どんなに古い再生曲でも返す
    static private function updatePlayDatas(Lfm_Feed_Contents $songs) {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $latest =
            array(
                'song'              => null,    // Lfm_Song
                'play_count'        => 0,
                'current_play_at'   => null,    // 今回の再生日時 Lfm_Datetime
                'last_play_at'      => null);   // 前回の再生日時 Lfm_Datetime

        $db_connection = Lfm_Db::getInstance();
        $model_songs = new Lfm_Model_Songs(array('db' => $db_connection));
        $model_artists = new Lfm_Model_Artists(array('db' => $db_connection));
        $model_logs = new Lfm_Model_PlayLogs(array('db' => $db_connection));
        foreach($songs as $song) {
            echo '    [' . $song->getUuid() . "]\n";
            echo '        ' . $song->getArtist()->getName() . ' - ' . $song->getTitle() . "\n";

            // アーティスト処理
            $rows = $model_artists->find($song->getArtist()->getUuid()->__toString());
            if(count($rows) < 1) {
                echo "      => 新しいアーティストを登録\n";
                $model_artists->insert(
                    array(
                        'id'    => $song->getArtist()->getUuid()->__toString(),
                        'name'  => $song->getArtist()->getName(),
                        'uri'   => $song->getArtist()->getLink()->__toString()));
            }

            // 曲処理
            $rows = $model_songs->find($song->getUuid()->__toString());
            if(count($rows) < 1) {
                echo "      => 新しい曲を登録\n";
                $model_songs->insert(
                    array(
                        'id'                => $song->getUuid()->__toString(),
                        'artist_id'         => $song->getArtist()->getUuid()->__toString(),
                        'title'             => $song->getTitle(),
                        'uri'               => $song->getLink()->__toString(),
                        'play_count'        => 1,
                        'first_played_at'   => $song->getTime()->__toString(),
                        'last_played_at'    => $song->getTime()->__toString(),
                        'last_play_id'      => ($songs->getPlayingSong()->getUuid()->__toString() === $song->getUuid()->__toString())
                                                    ? Lfm_Uuid::NIL_UUID
                                                    : $song->getPlayId()->__toString(),
                        'last_play_etag'    => $songs->getETag()->__toString()));

                $model_logs->insert(
                    array(
                        'song_id'           => $song->getUuid()->__toString(),
                        'play_at'           => $song->getTime()->__toString(),
                        'proc_at'           => Lfm_Datetime::factory(time())->__toString()));

                $latest['song']             = $song;
                $latest['play_count']       = 1;
                $latest['current_play_at']  = $song->getTime();
                $latest['last_play_at']     = null;
            } else {
                // 以前も再生したことがある、または、既に取り込んだデータ
                echo "      => 曲情報存在\n";
                $row = $rows[0];

                $latest['song']             = $song;
                $latest['play_count']       = (int)$row->play_count;
                $latest['current_play_at']  = $song->getTime();
                $latest['last_play_at']     = new Lfm_Datetime($row->last_played_at);

                // 更新された
                if($row->last_play_id !== $song->getPlayId()->__toString()) {
                    // この時点では last_play_id が NIL_UUID かもしれない（再生中）
                    echo "      => 更新されたかも？\n";
                    echo "           現在の再生ID : " . $song->getPlayId()->__toString() . "\n";
                    echo "           前回の再生ID : " . $row->last_play_id . "\n";
                    if($row->last_play_id === Lfm_Uuid::NIL_UUID &&                 // 前回更新時に「再生中」
                       $songs->getPlayingSong()->getUuid()->__toString()            // 前回更新時と同じ曲を再生中
                                        === $song->getUuid()->__toString() &&
                       $songs->getETag()->__toString() === $row->last_play_etag)    // RSS 自体更新されていない
                    {
                        echo "        => 更新されていない\n";
                    } else {
                        echo "        => 更新された\n";
                        // 再生が終わっただけ
                        if($row->last_play_id === Lfm_Uuid::NIL_UUID) {
                            echo "          => 再生終了\n";
                            $model_songs->update(
                                array(
                                    'last_played_at'    => $song->getTime()->__toString(),
                                    'last_play_id'      => $song->getPlayId()->__toString(),
                                    'last_play_etag'    => $songs->getETag()->__toString()),
                                $model_songs->getAdapter()->quoteInto('id = ?', $song->getUuid()->__toString()));
                        } elseif($songs->getPlayingSong()->getUuid()->__toString() === $song->getUuid()->__toString()) {
                            echo "          => 再生開始\n";
                            $model_songs->update(
                                array(
                                    'play_count'        => ((int)$row->play_count) + 1,
                                    'last_played_at'    => $song->getTime()->__toString(),
                                    'last_play_id'      => Lfm_Uuid::NIL_UUID,
                                    'last_play_etag'    => $songs->getETag()->__toString()),
                            $model_songs->getAdapter()->quoteInto('id = ?', $song->getUuid()->__toString()));
                            $model_logs->insert(
                                array(
                                    'song_id'           => $song->getUuid()->__toString(),
                                    'play_at'           => $song->getTime()->__toString(),
                                    'proc_at'           => Lfm_Datetime::factory(time())->__toString()));
                            ++$latest['play_count'];
                        } else {
                            echo "          => 再生して終了済\n";
                            $model_songs->update(
                                array(
                                    'play_count'        => ((int)$row->play_count) + 1,
                                    'last_played_at'    => $song->getTime()->__toString(),
                                    'last_play_id'      => $song->getPlayId()->__toString(),
                                    'last_play_etag'    => $songs->getETag()->__toString()),
                            $model_songs->getAdapter()->quoteInto('id = ?', $song->getUuid()->__toString()));
                            $model_logs->insert(
                                array(
                                    'song_id'           => $song->getUuid()->__toString(),
                                    'play_at'           => $song->getTime()->__toString(),
                                    'proc_at'           => Lfm_Datetime::factory(time())->__toString()));
                            ++$latest['play_count'];
                        }
                    }
                } else {
                    echo "      => 更新なし\n";
                }
            }
        }
        return $latest['song'] == null ? null : (object)$latest;
    }

    // 最後につぶやいた情報を取得
    static private function getLastTweetInfo() {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $model_tweet_info = new Lfm_Model_TweetInfo(array('db' => Lfm_Db::getInstance()));
        $rows = $model_tweet_info->fetchAll(); // 全行取得
        if(count($rows) === 1) {
            return 
                (object)array(
                    'song_id'   => new Lfm_Uuid($rows[0]->song_id),
                    'tweet_at'  => new Lfm_Datetime($rows[0]->tweet_at));
        } elseif(count($rows) > 1) {
            // バグ
            $model_tweet_info->delete('');
        }
        return null;
    }

    static private function setLastTweetInfo(Lfm_Uuid $song_id, Lfm_Datetime $tweet_at) {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $model_tweet_info = new Lfm_Model_TweetInfo(array('db' => Lfm_Db::getInstance()));
        $model_tweet_info->delete('');
        $model_tweet_info->insert(
            array(
                'song_id'   => $song_id->__toString(),
                'tweet_at'  => $tweet_at->__toString()));
    }

    // ツイートを作成する
    static private function buildTweet(stdclass $playing) {
        $t = new Lfm_ProgressTimer(__METHOD__);
        $parts = array();
        $parts[] = '[再生中]';
        $parts[] = $playing->song->getTitle();
        $parts[] = '/';
        $parts[] = $playing->song->getArtist()->getName();
        // ～ぶり～回目
        if($playing->play_count < 2) {
            $parts[] = '（初めての再生）';
        } else {
            $tmp = '（';
            if($playing->current_play_at instanceof Lfm_Datetime &&
               $playing->last_play_at instanceof Lfm_Datetime &&
               $playing->current_play_at->getInteger() > 0 &&
               $playing->last_play_at->getInteger() > 0)
            {
                $tdiff = $playing->current_play_at->getInteger() - $playing->last_play_at->getInteger();
                if($tdiff < 180 * 60) {
                    $tmp .= number_format(floor($tdiff / 60)) . '分';
                } elseif($tdiff < 48 * 3600) {
                    $tmp .= number_format(floor($tdiff / 3600)) . '時間';
                } elseif($tdiff < 2 * 365 * 86400) {
                    $tmp .= number_format(floor($tdiff / 86400)) . '日';
                } else {
                    $tmp .= number_format(floor($tdiff / (365.2422 * 86400))) . '年';
                }
                $tmp .= 'ぶり';
            }
            $tmp .= number_format($playing->play_count) . '回目';
            $tmp .= '）';
            $parts[] = $tmp;
        }
        if($uri = Lfm_ShortUrl::shorten($playing->song->getLink())) {
            $parts[] = $uri->__toString();
        }
        $parts[] = '#nowplaying';
        $parts[] = '#lastfm';
        $result = '';
        foreach($parts as $part) {
            $tmp = trim($result . ' ' . $part);
            if(mb_strlen($tmp, 'UTF-8') > 140) {
                break;
            }
            $result = $tmp;
        }
        return $result;
    }
}
