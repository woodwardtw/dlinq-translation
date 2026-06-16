/*!
 * Understrap v1.2.4 (https://understrap.com)
 * Copyright 2013-2026 The Understrap Authors (https://github.com/understrap/understrap/graphs/contributors)
 * Licensed under GPL-3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 */
(function () {
	'use strict';

	/* VTT-synced audio player — requires WaveSurfer loaded as a global */

	var audio = document.getElementById('tt-audio');
	var trackEl = audio ? audio.querySelector('track') : null;
	if (audio && trackEl) {
	  var vttUrl = trackEl.src;
	  var cues = [];
	  var activeCueIndex = -1;
	  function parseTime(str) {
	    var parts = str.trim().split(':');
	    if (parts.length === 3) {
	      return parseFloat(parts[0]) * 3600 + parseFloat(parts[1]) * 60 + parseFloat(parts[2]);
	    }
	    return parseFloat(parts[0]) * 60 + parseFloat(parts[1]);
	  }
	  function parseVTT(text) {
	    var result = [];
	    text.trim().split(/\n{2,}/).forEach(function (block) {
	      var lines = block.trim().split('\n');
	      var tiLine = lines.find(function (l) {
	        return l.indexOf('-->') !== -1;
	      });
	      if (!tiLine) {
	        return;
	      }
	      var sides = tiLine.split('-->');
	      if (sides.length < 2) {
	        return;
	      }
	      result.push({
	        start: parseTime(sides[0]),
	        end: parseTime(sides[1].trim().split(/\s/)[0])
	      });
	    });
	    return result;
	  }
	  function setActive(idx) {
	    if (idx === activeCueIndex) {
	      return;
	    }
	    activeCueIndex = idx;
	    document.querySelectorAll('.line.tt-current-phrase').forEach(function (l) {
	      l.classList.remove('tt-current-phrase');
	    });
	    if (idx < 0) {
	      return;
	    }
	    var lineNum = String(idx + 1);
	    var scrollTarget = null;
	    document.querySelectorAll('[data-line="' + lineNum + '"]').forEach(function (l) {
	      l.classList.add('tt-current-phrase');
	      if (!scrollTarget && l.closest('.original')) {
	        scrollTarget = l;
	      }
	    });
	    if (scrollTarget) {
	      scrollTarget.scrollIntoView({
	        behavior: 'smooth',
	        block: 'nearest'
	      });
	    }
	  }
	  audio.addEventListener('timeupdate', function () {
	    var t = audio.currentTime;
	    var idx = -1;
	    for (var i = 0; i < cues.length; i++) {
	      if (t >= cues[i].start && t < cues[i].end) {
	        idx = i;
	        break;
	      }
	    }
	    setActive(idx);
	  });
	  audio.addEventListener('ended', function () {
	    setActive(-1);
	  });
	  fetch(vttUrl).then(function (r) {
	    return r.text();
	  }).then(function (vttText) {
	    cues = parseVTT(vttText);
	    document.querySelectorAll('.original .line').forEach(function (line, i) {
	      if (!cues[i]) {
	        return;
	      }
	      line.style.cursor = 'pointer';
	      line.addEventListener('click', function () {
	        audio.currentTime = cues[i].start;
	        if (audio.paused) {
	          audio.play();
	        }
	      });
	    });
	  }).catch(function (err) {
	    console.warn('VTT load failed:', err);
	  });
	  var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
	  var ws = WaveSurfer.create({
	    // eslint-disable-line no-undef
	    container: '#waveform',
	    waveColor: prefersDark ? '#888' : '#bbb',
	    progressColor: prefersDark ? '#ddd' : '#1a1a1a',
	    cursorColor: prefersDark ? '#fff' : '#1a1a1a',
	    cursorWidth: 2,
	    height: 64,
	    normalize: true,
	    media: audio,
	    barWidth: 2,
	    barRadius: 2
	  });
	  var playBtn = document.getElementById('play-btn');
	  var timeEl = document.getElementById('player-time');
	  function fmt(s) {
	    var m = Math.floor(s / 60);
	    return m + ':' + String(Math.floor(s % 60)).padStart(2, '0');
	  }
	  ws.on('ready', function (dur) {
	    timeEl.textContent = '0:00 / ' + fmt(dur);
	  });
	  ws.on('timeupdate', function (t) {
	    timeEl.textContent = fmt(t) + ' / ' + fmt(ws.getDuration());
	  });
	  ws.on('play', function () {
	    playBtn.innerHTML = '&#9646;&#9646;';
	    playBtn.setAttribute('aria-label', 'Pause');
	  });
	  ws.on('pause', function () {
	    playBtn.innerHTML = '&#9654;';
	    playBtn.setAttribute('aria-label', 'Play');
	  });
	  ws.on('finish', function () {
	    playBtn.innerHTML = '&#9654;';
	    playBtn.setAttribute('aria-label', 'Play');
	  });
	  playBtn.addEventListener('click', function () {
	    ws.playPause();
	  });
	}

})();
//# sourceMappingURL=vtt-player.js.map
