<?php

function custom_sort($a, $b) {
	return $a['start'] > $b['start'];
}

function format_time($t) {
	$t = floatval($t);
	$floor = floor($t/10000);
	$t -= $floor * 10000;
	//return $t;
	$t *= 1000; // convert from s to ms
	return $t;
	return round($t);
}

function pretty_print_json($obj) {
	$string = json_encode($obj);
	$pattern = array(',"', '{', '}');
	$replacement = array(",\n\t\"", "{\n\t", "\n}");
	return str_replace($pattern, $replacement, $string);
}

function tag_info($parse) {
	if ($parse['isStartTag'])
		return $parse['tag'] . "_S:" . $parse['pos'];
	return $parse['tag'] . "_E:" . $parse['pos'];
}

function getItemById($data, $id) {
	foreach ($data as $item) {
		if ($item['id'] == $id)
			return $item;
	}
	return null;
}

function getIndexById($data, $id) {
	foreach ($data as $i => $item) {
		if ($item['id'] == $id)
			return $i;
	}
	return null;
}

function mockup() {
	return <<<EOF
{"site":"data\/piigeon.org_test-webkit_4","loadTime":8738239,"data":[{"id":"download_2","event":"download","url":"http:\/\/piigeon.org\/test-webkit\/","start":8738089,"end":8738131,"prev":[],"type":"","same_group":"","info":"size: 369","code":"0x0"},{"id":"objhash_3","event":"parse","url":"http:\/\/piigeon.org\/test-webkit\/","start":8738137,"end":8738145,"prev":["download_2"],"type":"","same_group":"","info":"html_start:6=>script_end:143"},{"id":"download_17","event":"download","url":"http:\/\/piigeon.org\/test-webkit\/main1.js","start":8738145,"end":8738220,"prev":["objhash_3"],"type":"","same_group":"","info":"size: 11","code":"0x10a8a55a0"},{"id":"download_15","event":"download","url":"http:\/\/piigeon.org\/test-webkit\/test-htmlcss.css","start":8738146,"end":8738188,"prev":[],"type":"","same_group":"","info":"size: 65","code":"0x0"},{"id":"comp_0","event":"execute","url":"http:\/\/piigeon.org\/test-webkit\/","start":8738221,"end":8738223,"prev":["download_17"],"type":"","same_group":"objhash_3","info":"execScript","code":"0x10a8a55a0"},{"id":"objhash_8","event":"parse","url":"http:\/\/piigeon.org\/test-webkit\/","start":8738224,"end":8738229,"prev":["comp_0"],"type":"","same_group":"objhash_3","info":"link_start:207=>body_start:353"},{"id":"comp_1","event":"execute","url":"http:\/\/piigeon.org\/test-webkit\/","start":8738226,"end":8738226,"prev":[],"type":"","same_group":"","info":"recalcStyle","code":"0x11d0e9e40"},{"id":"download_19","event":"download","url":"http:\/\/piigeon.org\/img\/global256.png","start":8738230,"end":8738237,"prev":["download_15","objhash_8"],"type":"","same_group":"","info":"size: 8","code":"0x11d0f8c00"},{"id":"objhash_9","event":"parse","url":"http:\/\/piigeon.org\/test-webkit\/","start":8738231,"end":8738231,"prev":["objhash_8"],"type":"","same_group":"objhash_3","info":"body_end:361=>html_end:368"}]};
EOF;
}

/*
 * This function takes the first pass to parse the logs output
 * from our browser. 
 */
function parseContent($content, $debug) {
	// Fetch lines in the document
	$lines = explode("\n", $content);

	// Init params
	$loadTime = -1;
	$downloads = array();
	$downloads_by_url = array();
	$downloads_index_by_url = array();
	$downloads_by_code = array();

	$parses_trigger_downloads = array();
	$parses_raw = array();
	$parses_raw_by_code = array();
	$parses = array();
	$parses_hash = array();

	$comps = array();
	$comps_parsing = array();

	$hols_raw = array();
	$hols_raw_by_url = array();

	$preloads_raw = array();

	$img_css_raw = array();

	// init
	$url = "";
	$time = -1;
	$same_group_first_id = null;
	$prev_chunk_id = null;
	$has_html = true;

	// Constructs objects
	foreach ($lines as $i => $line) {
		$line = str_replace("'", "\"", $line);
		$item = json_decode($line, true);

		// Fetch the load time
		if ($item['DOMLoad']) {
			$loadTime = format_time($item['DOMLoad']);

		// Record the start of a resource
		} else if ($item['Resource']) {

			$id = "download_" . $i;
			$url = $item['Resource']['url'];
			$time = format_time($item['Resource']['sentTime']);
			$code = $item['Resource']['from'];
			$parses_trigger_downloads[$code] = true;

			$same_group_first_id = null;
			$prev_chunk_id = null;
			$mime = $item['Resource']['mimeType'];

			preg_match('/image/', $mime, $imgs, PREG_OFFSET_CAPTURE);
			preg_match('/css/', $mime, $csss, PREG_OFFSET_CAPTURE);
			preg_match('/javascript/', $mime, $jss, PREG_OFFSET_CAPTURE);
			preg_match('/html/', $mime, $htmls, PREG_OFFSET_CAPTURE);
			if (count($imgs) > 0)
				$mime = "image";
			else if (count($csss) > 0)
				$mime = "css";
			else if (count($jss) > 0)
				$mime = "js";
			else if (count($htmls) > 0)
				$mime = "html";

			$downloads_by_url[$url] = array(
				"id" => $id,
				"event" => "download",
				"url" => $url,
				"start" => $time,
				//"end" => $received_time,
				//"prev" => $prev_arr,
				"type" => "",
				"same_group" => "",
				//"same_group" => $same_group,
				//"info" => $mime . ":[" . $item['ReceivedChunk']['len'] . "]",
				"mime" => $mime,
				"bytes" => 0,
				"code" => $code,
			);

		// Record the time to receive a resource. This could be multiple
		// (format to fit viewer)
		} else if ($item['ReceivedChunk']) {

			$id = "download_" . $i;
			$received_time = format_time($item['ReceivedChunk']['receivedTime']);

			// For inferring dependency (download made from parse)
			$code = ($same_group_first_id == null) ? $code : "";

			// Group chunks for the same download
			$same_group_first_id = ($same_group_first_id == null) ? $id : $same_group_first_id;
			$same_group = ($same_group_first_id == $id) ? "" : $same_group_first_id;

			// Add dependency to previous chunk
			//$prev_arr = ($prev_chunk_id) ? array($prev_chunk_id) : array();
			$prev_arr = array();

			$download = $downloads_by_url[$url];

			// Add to downloads array
			$download['end'] = $received_time;
			$download['prev'] = $prev_arr;
			$download['bytes'] += $item['ReceivedChunk']['len'];
			$download['info'] = $mime . ":[" . $item['ReceivedChunk']['len'] . "]";
			$download['chunks'][] = array(
				'time' => $received_time,
				'bytes' => $item['ReceivedChunk']['len'],
			);
			$downloads_by_url[$url] = $download;

			// Prepare for the next chunk
			$time = $received_time;
			$prev_chunk_id = $id;

		// Fetch computation tasks (format to fit viewer)
		} else if ($item['Computation']) {
			// Skip invalid comp items
			if ($item['Computation']['endTime'] < 0)
				continue;

			$comp = $item['Computation'];
			$id = "comp_" . $i;
			$c = array(
				"id" => $id,
				"event" => "execute",
				"url" => $comp['docUrl'],
				"start" => format_time($comp['startTime']),
				"end" => format_time($comp['endTime']),
				"prev" => array(),
				"type" => "",
				"same_group" => "",
				"info" => $comp['type'],
				"code" => $comp['code'],
				"urlRecalcStyle" => $comp['urlRecalcStyle'],
			);
			if ($comp['urlRecalcStyle'])
				$comps[] = $c;
			else
				$comps_parsing[] = $c;

		// Fetch ObjectHash, almost unmodified
		} else if ($item['ObjectHash']) {
			$parse = $item['ObjectHash'];
			$parse_raw = array(
				"code" => $parse['code'],
				"docUrl" => $parse['doc'],
				"tag" => $parse['tagName'],
				"isStartTag" => $parse['isStartTag'],
				"time" => format_time($parse['time']),
				"toUrl" => $parse['url'],
				"pos" => $parse['pos'],
				"chunkLen" => $parse['chunkLen'],
			);
			$parses_raw[] = $parse_raw;
			$parses_raw_by_code[$parse['code']] = $parse_raw;
			$url = $parse['doc'];
			if (!$parses_hash[$url]) {
				$parses_hash[$url] = array(
					's' => $parse,
				);
			} else {
				$parses_hash[$url]['l'] = $parse;
				if ($parse['tagName'] == "html" and $parse['isStartTag'] == 0) {
					$parses_hash[$url]['e'] = $parse;
				}
			}

		// Fetch head-of-line data. Note that order is important here
		} else if ($item['HOL']) {
			$hols_raw[] = $item['HOL'];
			$hols_raw_by_url[$item['HOL']['url']] = $item['HOL'];

		// Fetch urls mades from preloads
		} else if ($item['Preload']) {
			$preloads_raw[] = $item['Preload'];

		// Fetch the dependency of img -> css
		// And add this dependency to downloads so that we don't need to explicit record
		} else if ($item['matchedCSSAndUrl']) {
			$img_css_raw[] = $item['matchedCSSAndUrl'];
		}
	}

	// Construct parses
	$i = 1;
	foreach ($parses_hash as $url => $parse) {

		$id = "parse_" . $i;
		$i++;

		// Connect download and evaluation
		$same_group = $downloads_by_url[$url]['id'];
		$downloads_by_url[$url]['evaluate'] = $id;

		$parses[] = array(
				"id" => $id,
				"event" => "parse",
				"url" => $parse['s']['doc'],
				"start" => format_time($parse['s']['time']),
				"end" => format_time($parse['e']['time']),
				"prev" => array(),
				"type" => "",
				"same_group" => $same_group,
				"info" => "",
				"last_parse" => $parse['l'],
		);
	}

	// Construct downloads
	$downloads = array_values($downloads_by_url);

	return array(
		"loadTime" => $loadTime,
		"downloads" => $downloads,
		"downloads_by_url" => $downloads_by_url,
		"downloads_index_by_url" => $downloads_index_by_url,
		"downloads_by_code" => $downloads_by_code,
		"downloads_index_by_code" => $downloads_index_by_code,
		"comps" => $comps,
		"comps_parsing" => $comps_parsing,
		"parses_raw" => $parses_raw,
		"parses_raw_by_code" => $parses_raw_by_code,
		"parses" => $parses,
		"parses_trigger_downloads" => $parses_trigger_downloads,
		"hols_raw" => $hols_raw,
		"hols_raw_by_url" => $hols_raw_by_url,
		"preloads_raw" => $preloads_raw,
		"img_css_raw" => $img_css_raw,
	);
}

function addCompsToParses(
	&$parses,
	&$comps,
	$debug) {

	foreach ($parses as $i => $parse) {
		$parse['evals'] = array();
		foreach ($comps as $j => $comp) {
			if ($comp['url'] != $parse['url'])
				continue;

			$parse['evals'][] = array(
				'id' => $comp['id'],
				'start' => $comp['start'],
				'end' => $comp['end'],
				'info' => $comp['info'],
				'code' => $comp['code'],
				'toUrl' => $comp['urlRecalcStyle'],
			);
		}

		$parses[$i] = $parse;
	}
}

function addPrevToDownloads(
	&$downloads,
	$parses,
	&$parses_raw_by_code,
	$debug) {
	foreach ($downloads as $i => $download) {
		$download = $downloads[$i];

		//
		// Need to know docUrl of downloads
		foreach ($parses as $j => $parse) {
			// Exclude wrong parses
			// TODO Should consider ancestors
			if ($download['start'] < $parse['start'] or $download['start'] > $parse['end'])
				continue;

			// We need to figure out where a resource is fetched from when it is fetched
			// We check the evaluations within HTML parsing. If an evaluation is found,
			// this means that this resource is from that evaluation.
			// If multiple evaluations are found, we chose the last evaluation (the one
			// that happens last, TODO because we assume that the one happens last blocks
			// previous overlapped evaluations.
			$eval_id = -1;

			// Find from evals
			foreach ($parse['evals'] as $k => $ev) {
				if (!$ev['urlRecalcStyle'] or $ev['urlRecalcStyle'] == "(null)")
					continue;
				if ($download['start'] > $ev['start'] and $download['start'] < $ev['end']) {
					$eval_id = $ev['id'];
				}
			}

			if ($eval_id == -1)
				$download['from_eval_id'] = $parse['id'];
			else
				$download['from_eval_id'] = $eval_id;

			// Each download is only from one html
			break;
		}

		// Catch downloads triggerd by 'load' event
		// TODO should consider ancestors
		// TODO this doesn't consider js made from js from onload
		foreach ($parses as $j => $parse) {
			if ($download['start'] < $parse['end'])
				continue;
			if ($download['mime'] != 'js')
				continue;

			//print_r($download);
			//print_r($parse);

			if ($download['code'] == $parse['last_parse']['code'])
				$eval_id = $parse['id'];
			$download['from_eval_id'] = $eval_id;
		}

		// After download

		// 
		$code = $download['code'];
		if (!$code) {
			continue;
		}
		$parse = $parses_raw_by_code[$code];

		$download['parse'] = $parse;
		$downloads[$i] = $download;
	}
}

function addPreloadsAndDependedDownloads(
	$preloads_raw,
	&$comps,
	$parses,
	&$downloads,
	$debug) {

	$had_preload = array();

	foreach ($preloads_raw as $i => $preload) {
		$code = $preload['code'];
		$url = $preload['url'];

		if ($had_preload[$url])
			continue;
		else
			$had_preload[$url] = 1;

		// Find the parse with the same code
		$has_parse = false;
		foreach ($parses as $j => $parse) {
			if ($parse['code'] == $code) {
				$has_parse = true;
				break;
			}
		}
		if (!$has_parse)
			continue;

		// This is ugly, but we need to move the chunk of script_start to script_end
		$parse = $parses[$j + 0];

		// Add preload to comp
		$id = "preload_" . $i;
		$c = array(
			"id" => $id,
			"event" => "execute",
			"url" => $preload['docUrl'],
			"start" => $parse['end'],
			"end" => format_time($preload['time']),
			"prev" => array($parse['id']),
			"type" => "preload",
			"same_group" => $parse['same_group'],
			"info" => "preload",
			"code" => $code,
			"urlRecalcStyle" => null,
		);
		$comps[] = $c;

		// Add dependency: download -> preload
		foreach ($downloads as $j => $download) {
			if ($download['url'] != $url)
				continue;
			$download['prev'][] = $id;
			$download['preload'] = array(
				'id' => $id,
				'time' => format_time($preload['time']),
				'info' => $parse['info'],
				'url' => $parse['toUrl'],
			);
			$downloads[$j] = $download;
		}
	}

	// We need to sort comps because we could have added new values
	usort($comps, "custom_sort");
}

function addDependencyExecScriptFromDownloads(
	&$parses,
	$parses_raw,
	&$comps,
	&$downloads,
	$hols_raw_by_url,
	$debug) {

	$exec_script_by_url = array();

	foreach ($comps as $i => $comp) {
		if ($comp['info'] != "execScript" and $comp['info'] != "recalcStyle")
			continue;
		$url = null;
		if ($comp['urlRecalcStyle'] != "(null)") {
			$url = $comp['urlRecalcStyle'];
		}

		if (!$url)
			continue;

		// Find out download
		$found = false;
		foreach ($downloads as $j => $download) {
			if ($download['url'] == $url) {
				$found = true;
				break;
			}
		}
		if (!$found)
			continue;

		// Add dependency: execScript -> js
		$comp['prev'][] = $download['id'];

		// Group JS to download
		$comp['same_group'] = $download['id'];

		$comps[$i] = $comp;
		$exec_script_by_url[$url] = $comp;
	}

	return array(
		"exec_script_by_url" => $exec_script_by_url,
	);
}

function addDependencyRecalcStyleFromDownloadsOrHTMLLinkElement(
	&$comps,
	$downloads,
	$parses,
	$debug) {

	$recalc_style_by_url = array();

	foreach ($comps as $i => $comp) {
		if ($comp['info'] == "recalcStyle" and $comp['urlRecalcStyle']) {
			$url = $comp['urlRecalcStyle'];
			if (!$url or $url == "(null)")
				continue;

			// Find the download
			foreach ($downloads as $download) {
				if ($download['url'] == $url)
					break;
			}

			// Find the comp
			foreach ($parses as $parse) {
				if ($parse['toUrl'] == $url)
					break;
			}

			// Set the dependency
			if ($download['end'] > $parse['end']) {
				// Depends on the download
				$prev = $comp['prev'];
				$prev[] = $download['id'];
				$comps[$i]['prev'] = $prev;
			} else {
				// Depends on the HTMLLinkElement
				$prev = $comp['prev'];
				$prev[] = $parse['id'];
				$comps[$i]['prev'] = $prev;
			}

			$recalc_style_by_url[$url] = $comp;
		}
	}

	return array(
		"recalc_style_by_url" => $recalc_style_by_url,
	);
}

function addDependencyHOL(
	$hols_raw,
	&$comps,
	$exec_script_by_url,
	$recalc_style_by_url,
	$debug) {

	$css_urls = array();

	foreach ($hols_raw as $i => $hol) {
		if ($hol['type'] == 4) {
			// Adds url to css_urls array for future processing
			$css_urls[] = $hol['url'];
		} else if ($hol['type'] == 1) {
			// current script depends on css in css_urls
			$execScript = $exec_script_by_url[$hol['url']];
			$id = $execScript['id'];
			$prev = $execScript['prev'];

			// Find corresponding comp
			$has_comp = false;
			foreach ($comps as $j => $comp) {
				if ($comp['id'] == $id) {
					$has_comp = true;
					break;
				}
			}
			if (!$has_comp)
				continue;

			foreach ($css_urls as $url) {
				// Add recalc style id to prev
				$prev[] = $recalc_style_by_url[$url]['id'];
			}
			// Push back to comps
			$execScript['prev'] = $prev;
			$comps[$j] = $execScript;

			// Empty the css_urls array to eliminate unnecessary dependencies
			$css_urls = array();
		}
	}
}

function splitExecScript(
	&$comps,
	&$parses,
	&$downloads,
	$debug) {

	foreach ($comps as $i => $comp) {
		if ($comp['info'] != "execScript")
			continue;
		// Parse chunks interleave execScript
		foreach ($parses as $j => $parse) {
			// Parse depends on comp but starts before comp ends
			if (in_array($comp['id'], $parse['prev']) and $parse['start'] < $comp['end']) {
				// split
				$comp_copy = $comp;
				$comp['end'] = $parse['start'];
				$comps[$i]['end'] = $parse['start'];

				$comp_copy['id'] .= "+1";
				$comp_copy['start'] = $parse['end'];
				$comp_copy['prev'] = Array($parse['id']);
				foreach ($parses as $k => $p) {
					foreach ($p['prev'] as $kk => $prev) {
						if ($prev == $parse['id']) {
							$p['prev'][$kk] = $comp_copy['id'];
							$parses[$k] = $p;
						}
					}
				}

				$comps = array_merge(
					array_slice($comps, 0, $i),
					Array($comp_copy),
					array_slice($comps, $i)
				);

				// Adjust timing of parsing
				if ($j + 1 < count($parses)) {
					$parses[$j + 1]['start'] = $comp_copy['end'];
				}
			}
		}

		// Download start interleaves execScript
		foreach ($downloads as $j => $download) {
			if ($download['start'] > $comp['start'] and $download['start'] < $comp['end']) {
				// split
				$comp_copy = $comp;
				$comps[$i]['end'] = $download['start'];

				$comp_copy['id'] .= "+2";
				$comp_copy['start'] = $download['start'];
				$comp_copy['prev'] = Array($comp['id']);
				foreach ($parses as $k => $p) {
					foreach ($p['prev'] as $kk => $prev) {
						if ($prev == $comp['id']) {
							$p['prev'][$kk] = $comp_copy['id'];
							$parses[$k] = $p;
						}
					}
				}
				$comps = array_merge(
					array_slice($comps, 0, $i),
					Array($comp_copy),
					array_slice($comps, $i)
				);

				// Add dependency if not: download -> execScript
				if (!in_array($comp['id'], $download['prev'])) {
					$download['prev'][] = $comp['id'];
					$downloads[$j] = $download;
				}
			}
		}
	}

	return array(
		"comps" => $comps,
		"parses" => $parses,
		"downloads" => $downloads,
	);
}

function mergeParseChunks(
	&$parses, // TODO
	$downloads,
	$debug) {

	$i = 0;
	while ($i < count($parses)) {
		$parse = $parses[$i];
		if ($i < 2) {
			++$i;
			continue;
		}

		if (count($parse['prev']) != 1) {
			++$i;
			continue;
		}

		$parse_prev = $parses[$i - 1];
		if ($parse['prev'][0] != $parse_prev['id']) {
			++$i;
			continue;
		}

		if ($parse['start'] != $parse_prev['end']) {
			++$i;
			continue;
		}

		$by_download = false;
		foreach ($downloads as $download) {
			if (in_array($parse_prev['id'], $download['prev'])) {
				$by_download = true;
				break;
			}
		}
		if ($by_download) {
			++$i;
			continue;
		}

		// Merge!
		// Set start time
		$parse['start'] = $parse_prev['start'];

		// Set info
		$arr1 = explode("=>", $parse_prev['info']);
		$arr2 = explode("=>", $parse['info']);
		$parse['info'] = $arr1[0] . "=>" . $arr2[1];

		// Set prev
		$parse['prev'] = $parse_prev['prev'];

		$parses = array_merge(
			array_slice($parses, 0, $i - 1),
			array($parse),
			array_slice($parses, $i + 1)
		);
	}
}

function groupRecalcStyle(
	&$data,
	$debug) {

	foreach ($data as $i => $recalcStyle) {
		if ($recalcStyle['info'] != "recalcStyle")
			continue;

		// Find the CSS download
		foreach ($recalcStyle['prev'] as $prev) {
			$download = getItemById($data, $prev);
			if ($download['event'] != "download")
				continue;

			// Group the CSS with its download
			$recalcStyle['same_group'] = $download['id'];
			$data[$i] = $recalcStyle;
			break;

			// Find the parse or preload that triggers the CSS download
			/*foreach ($download['prev'] as $prev2) {
				$parse_or_preload = getItemById($data, $prev2);
				if ($parse_or_preload['event'] != "parse" and $parse_or_preload['info'] != "preload")
					continue;
				$recalcStyle['same_group'] = $parse_or_preload['same_group'];
				$data[$i] = $recalcStyle;
				break;
			}*/
		}
	}
}

function fillParse(
	&$data,
	$debug) {

	// Fill the end
	foreach ($data as $i => $item) {
		if ($item['event'] != "parse")
			continue;

		// Fill end of parse
		$time = -1;
		foreach ($data as $item2) {
			if ($item2['event'] != "download" or !in_array($item['id'], $item2['prev']))
				continue;
			if ($item['end'] > $item2['start'])
				continue;
			if ($time == -1 or $item2['start'] < $time)
				$time = $item2['start'];
		}
		if ($time == -1)
			continue;
		$data[$i]['end'] = $time;

		// Fill start
		foreach ($data as $j => $item2) {
			if ($item2['event'] != "parse" or !in_array($item['id'], $item2['prev']))
				continue;
			$data[$j]['start'] = $time;
			break;
		}
	}
}
