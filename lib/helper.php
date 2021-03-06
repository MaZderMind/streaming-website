<?php

require_once('program.php');

function link_index()
{
	return '';
}

function link_room($room)
{
	return rawurlencode($room).'/';
}

function link_player($room, $format = 'video', $translated = false)
{
	$defaultformat = room_has_hd($room) ? 'hd' : 'sd';

	return rawurlencode($room).'/'.(($defaultformat == $format || 'video' == $format) ? '' : rawurlencode($format).'/').($translated ? 'translated/' : '');
}

function link_stream($protocol, $room, $format, $translated = false)
{
	$language = $translated ? 'translated' : 'native';

	switch ($protocol) {
		case 'rtmp':
			return 'rtmp://cdn.c3voc.de/stream/'.rawurlencode(get("ROOMS.$room.STREAM", $room)).'_'.rawurlencode($language).'_'.rawurlencode($format);

		case 'hls':
			return 'http://cdn.c3voc.de/hls/'.rawurlencode(get("ROOMS.$room.STREAM", $room)).'_'.rawurlencode($language).($format == 'auto' ? '' : '_'.rawurlencode($format)).'.m3u8';

		case 'webm':
			return 'http://cdn.c3voc.de/'.rawurlencode(get("ROOMS.$room.STREAM", $room)).'_'.rawurlencode($language).'_'.rawurlencode($format).'.webm';

		case 'music':
			return 'http://cdn.c3voc.de/'.rawurlencode(get("ROOMS.$room.STREAM", $room)).'.'.rawurlencode($format);

		case 'audio':
			return 'http://cdn.c3voc.de/'.rawurlencode(get("ROOMS.$room.STREAM", $room)).'_'.rawurlencode($language).'.'.rawurlencode($format);

		case 'slide':
			return 'http://cdn.c3voc.de/slides/'.rawurlencode(get("ROOMS.$room.STREAM", $room)).'/current.png';
	}

	return '#';
}

function link_vod($id)
{
	return 'relive/'.rawurlencode($id).'/';
}

function baseurl()
{
	if(isset($GLOBALS['CONFIG']['baseurl']))
		return $GLOBALS['CONFIG']['baseurl'];

	$base  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https://' : 'http://';
	$base .= $_SERVER['HTTP_HOST'];
	$base .=  forceslash(dirname($_SERVER['SCRIPT_NAME']));

	return $base;
}

function forceslash($url)
{
	$url =  rtrim($url, '/');
	if(strlen($url) > 0)
		$url .= '/';

	return $url;
}

function strtoduration($str)
{
	$parts = explode(':', $str);
	return ((int)$parts[0] * 60 + (int)$parts[1]) * 60;
}

function has($keychain)
{
	return _has($GLOBALS['CONFIG'], $keychain);
}
function _has($array, $keychain)
{
	if(!is_array($keychain))
		$keychain = explode('.', $keychain);

	$key = $keychain[0];
	if(!isset($array[$key]))
		return false;

	if(count($keychain) == 1)
		return true;

	return _has($array[$key], array_slice($keychain, 1));
}

function get($keychain, $default = null)
{
	return _get($GLOBALS['CONFIG'], $keychain, $default);
}
function _get($array, $keychain, $default)
{
	if(!is_array($keychain))
		$keychain = explode('.', $keychain);

	$key = $keychain[0];
	if(!isset($array[$key]))
		return $default;

	if(count($keychain) == 1)
		return $array[$key];

	return _get($array[$key], array_slice($keychain, 1), $default);
}



function room_has_hd($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('rtmp-hd', 'hls-hd', 'webm-hd'), $formats)) > 0;
}

function room_has_sd($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('rtmp-sd', 'hls-sd', 'webm-sd'), $formats)) > 0;
}

function room_has_video($room)
{
	return room_has_hd($room) || room_has_sd($room);
}

function room_has_audio($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('audio-mp3', 'audio-opus', 'audio-ogg'), $formats)) > 0;
}

function room_has_music($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('music-mp3', 'music-opus', 'music-ogg'), $formats)) > 0;
}

function room_has_slides($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('slides'), $formats)) > 0;
}

function room_get_possible_selections($room)
{
	$selections = array();

	if(room_has_hd($room)) $selections[] = 'hd';
	if(room_has_sd($room)) $selections[] = 'sd';
	if(room_has_audio($room)) $selections[] = 'audio';
	if(room_has_music($room)) $selections[] = 'music';
	if(room_has_slides($room)) $selections[] = 'slides';

	return $selections;
}

function room_has_rtmp($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('rtmp-hd', 'rtmp-sd'), $formats)) > 0;
}

function room_has_webm($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('webm-hd', 'webm-sd'), $formats)) > 0;
}

function room_has_hls($room)
{
	$formats = get("ROOMS.$room.FORMATS");
	return count(array_intersect(array('hls-hd', 'hls-sd'), $formats)) > 0;
}

function room_has_irc($room)
{
	return get("ROOMS.$room.IRC") && has("IRC");
}

function room_has_twitter($room)
{
	return get("ROOMS.$room.TWITTER") && has("TWITTER");
}

function room_has_chat($room)
{
	return room_has_irc($room) || room_has_twitter($room);
}

function room_get_irc_url($room)
{
	$cfg = get("ROOMS.$room.IRC_CONFIG", get("IRC"));
	return sprintf($cfg['URL'], rawurlencode($room));
}

function room_get_irc_display($room)
{
	$cfg = get("ROOMS.$room.IRC_CONFIG", get("IRC"));
	return sprintf($cfg['DISPLAY'], $room);
}

function room_get_twitter_hashtag($room)
{
	$cfg = get("ROOMS.$room.TWITTER_CONFIG", get("TWITTER"));
	return sprintf($cfg['TEXT'], $room);
}

function room_get_twitter_display($room)
{
	$cfg = get("ROOMS.$room.TWITTER_CONFIG", get("TWITTER"));
	return sprintf($cfg['DISPLAY'], $room);
}

function startswith($needle, $haystack)
{
	return substr($haystack, 0, strlen($needle)) == $needle;
}

function relive_talks()
{
	$talks = @file_get_contents(get('OVERVIEW.RELIVE_JSON'));
	$talks = utf8_decode($talks);
	$talks = (array)json_decode($talks, true);

	usort($talks, function($a, $b) {
		$sort = array('live', 'recorded', 'released');
		return array_search($a['status'], $sort) > array_search($b['status'], $sort);
	});

	$talks_by_id = array();
	foreach ($talks as $value)
	{
		$talks_by_id[$value['id']] = $value;
	}

	return $talks_by_id;
}
