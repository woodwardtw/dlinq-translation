/*!
 * Understrap v1.2.4 (https://understrap.com)
 * Copyright 2013-2026 The Understrap Authors (https://github.com/understrap/understrap/graphs/contributors)
 * Licensed under GPL-3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 */
(function () {
  'use strict';

  (function () {

    const cfg = window.vttAdjustData || {};
    const VTT_URL = cfg.vttUrl || '';
    const FILENAME = cfg.filename || 'adjusted.vtt';
    const REST_URL = cfg.restUrl || '';
    const REST_NONCE = cfg.restNonce || '';
    const TRANSLATION_ID = cfg.translationId || 0;
    const CAN_AUTOSAVE = !!(REST_URL && REST_NONCE && TRANSLATION_ID);
    const audio = document.getElementById('vtt-adj-audio');
    const transcript = document.getElementById('vtt-adj-transcript');
    const currentTime = document.getElementById('vtt-adj-current-time');
    const statusMsg = document.getElementById('vtt-adj-status');
    if (!audio || !transcript) return;
    let originalVTT = '';
    let cues = [];
    let activeRow = null;
    let statusTimer = null;
    let saveTimer = null;
    let seekStopAt = null;
    let seekListener = null;
    let seekTimer = null;
    const pendingRows = new Set();

    // ── Seek helpers ─────────────────────────────────────────────────────────

    function cancelSeek() {
      clearTimeout(seekTimer);
      seekTimer = null;
      if (seekListener) {
        audio.removeEventListener('timeupdate', seekListener);
        seekListener = null;
      }
      seekStopAt = null;
    }

    // ── Time helpers ────────────────────────────────────────────────────────

    function parseSec(str) {
      str = String(str || '').trim();
      const parts = str.split(':');
      if (parts.length === 3) {
        return parseFloat(parts[0]) * 3600 + parseFloat(parts[1]) * 60 + parseFloat(parts[2]);
      }
      if (parts.length === 2) {
        return parseFloat(parts[0]) * 60 + parseFloat(parts[1]);
      }
      return parseFloat(str);
    }
    function fmtTime(sec) {
      if (!isFinite(sec) || sec < 0) sec = 0;
      const h = Math.floor(sec / 3600);
      const m = Math.floor(sec % 3600 / 60);
      const s = (sec % 60).toFixed(3).padStart(6, '0');
      return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${s}`;
    }

    // ── VTT parser ──────────────────────────────────────────────────────────

    function parseVTT(text) {
      const result = [];
      const lines = text.split('\n');
      let i = 0;
      while (i < lines.length && !lines[i].startsWith('WEBVTT')) i++;
      i++;
      while (i < lines.length) {
        while (i < lines.length && lines[i].trim() === '') i++;
        if (i >= lines.length) break;
        let id = '';
        if (!lines[i].includes('-->')) {
          id = lines[i].trim();
          i++;
        }
        if (i >= lines.length || !lines[i].includes('-->')) {
          i++;
          continue;
        }
        const timingLine = lines[i];
        i++;
        const arrow = timingLine.indexOf('-->');
        const startStr = timingLine.slice(0, arrow).trim();
        const endStr = timingLine.slice(arrow + 3).trim().split(/\s/)[0];
        const textLines = [];
        while (i < lines.length && lines[i].trim() !== '') {
          textLines.push(lines[i]);
          i++;
        }
        result.push({
          id: id || String(result.length + 1),
          start: parseSec(startStr),
          end: parseSec(endStr),
          text: textLines.join('\n').trim()
        });
      }
      return result;
    }

    // ── VTT generator ───────────────────────────────────────────────────────

    function buildVTT() {
      let out = 'WEBVTT\n\n';
      const rows = transcript.querySelectorAll('.vtt-phrase-row');
      cues.forEach((cue, i) => {
        const row = rows[i];
        const start = parseSec(row.querySelector('.vtt-start-inp').value);
        const end = parseSec(row.querySelector('.vtt-end-inp').value);
        out += `${cue.id}\n${fmtTime(start)} --> ${fmtTime(end)}\n${cue.text}\n\n`;
      });
      return out;
    }

    // ── Auto-save ────────────────────────────────────────────────────────────

    function markRowDirty(row) {
      if (!CAN_AUTOSAVE) return;
      pendingRows.add(row);
      setRowStatus(row, 'pending');
      clearTimeout(saveTimer);
      saveTimer = setTimeout(autoSave, 900);
    }
    function setRowStatus(row, status) {
      const icon = row.querySelector('.vtt-row-status');
      if (!icon) return;
      icon.className = 'vtt-row-status' + (status ? ' vtt-status-' + status : '');
    }
    function autoSave() {
      const snapshot = [...pendingRows];
      pendingRows.clear();
      fetch(`${REST_URL}${TRANSLATION_ID}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': REST_NONCE
        },
        body: JSON.stringify({
          vtt: buildVTT()
        })
      }).then(r => r.json()).then(data => {
        if (data.success) {
          for (const row of transcript.querySelectorAll('.vtt-phrase-row')) {
            setRowStatus(row, 'saved');
          }
        } else {
          for (const row of snapshot) setRowStatus(row, 'error');
          showStatus('Save failed: ' + (data.message || 'server error'));
        }
      }).catch(err => {
        for (const row of snapshot) setRowStatus(row, 'error');
        showStatus('Save error: ' + err.message);
      });
    }

    // ── Rendering ────────────────────────────────────────────────────────────

    function makeInput(cls, sec) {
      const inp = document.createElement('input');
      inp.type = 'text';
      inp.className = `vtt-time-inp ${cls}`;
      inp.value = fmtTime(sec);
      inp.spellcheck = false;
      inp.autocomplete = 'off';
      inp.setAttribute('aria-label', cls === 'vtt-start-inp' ? 'Start time' : 'End time');
      inp.addEventListener('blur', () => {
        const v = parseSec(inp.value);
        if (isNaN(v)) {
          inp.classList.add('invalid');
        } else {
          inp.classList.remove('invalid');
          inp.value = fmtTime(v);
        }
      });
      return inp;
    }
    function makeBtn(label, title, extraClass, handler) {
      const btn = document.createElement('button');
      btn.textContent = label;
      btn.title = title;
      btn.className = `vtt-ph-btn${extraClass ? ' ' + extraClass : ''}`;
      btn.addEventListener('click', handler);
      return btn;
    }
    function renderTranscript() {
      transcript.innerHTML = '';
      activeRow = null;
      pendingRows.clear();
      clearTimeout(saveTimer);
      cues.forEach(cue => {
        const row = document.createElement('div');
        row.className = 'vtt-phrase-row';
        const textSpan = document.createElement('span');
        textSpan.className = 'vtt-phrase-text';
        textSpan.textContent = cue.text;
        textSpan.addEventListener('click', () => {
          const t = parseSec(row.querySelector('.vtt-start-inp').value);
          if (isFinite(t)) {
            cancelSeek();
            audio.currentTime = t;
            audio.play();
            setTimeout(() => scrollRowIntoView(row), 0);
          }
        });
        row.appendChild(textSpan);
        const controls = document.createElement('div');
        controls.className = 'vtt-phrase-controls';
        const startInp = makeInput('vtt-start-inp', cue.start);
        startInp.addEventListener('blur', () => {
          if (!startInp.classList.contains('invalid')) markRowDirty(row);
        });
        const arrow = document.createElement('span');
        arrow.className = 'vtt-time-arrow';
        arrow.textContent = '→';
        arrow.setAttribute('aria-hidden', 'true');
        const endInp = makeInput('vtt-end-inp', cue.end);
        endInp.addEventListener('blur', () => {
          if (endInp.classList.contains('invalid')) return;
          markRowDirty(row);
          // Cascade: push the next row's start forward if this end overlaps it.
          const thisEnd = parseSec(endInp.value);
          if (!isFinite(thisEnd)) return;
          const nextRow = row.nextElementSibling;
          if (!nextRow) return;
          const nextStart = nextRow.querySelector('.vtt-start-inp');
          if (nextStart && parseSec(nextStart.value) < thisEnd) {
            nextStart.value = fmtTime(thisEnd);
            nextStart.classList.remove('invalid');
            flashRow(nextRow);
            markRowDirty(nextRow);
          }
        });
        controls.appendChild(startInp);
        controls.appendChild(arrow);
        controls.appendChild(endInp);
        controls.appendChild(makeBtn('▶ Seek', 'Seek to this cue and stop at its end time', 'vtt-seek-btn', () => {
          const t = parseSec(startInp.value);
          const end = parseSec(endInp.value);
          if (!isFinite(t)) return;
          cancelSeek();
          audio.currentTime = t;
          if (isFinite(end) && end > t) {
            seekStopAt = end;
            // setTimeout is the primary stop — wall-clock accurate regardless of timeupdate frequency.
            seekTimer = setTimeout(() => {
              audio.pause();
              cancelSeek();
            }, Math.max(0, Math.round((end - t) * 1000)));
            // timeupdate as fallback in case the timer fires slightly early.
            seekListener = () => {
              if (audio.currentTime >= seekStopAt) {
                audio.pause();
                cancelSeek();
              }
            };
            audio.addEventListener('timeupdate', seekListener);
          }
          audio.play();
          setTimeout(() => scrollRowIntoView(row), 0);
        }));
        controls.appendChild(makeBtn('Mark Start', 'Set start time to current audio position', null, () => {
          startInp.value = fmtTime(audio.currentTime);
          startInp.classList.remove('invalid');
          flashRow(row);
          markRowDirty(row);
        }));
        controls.appendChild(makeBtn('Mark End', 'Set end time to current audio position', null, () => {
          endInp.value = fmtTime(audio.currentTime);
          endInp.classList.remove('invalid');
          flashRow(row);
          markRowDirty(row);
        }));
        if (CAN_AUTOSAVE) {
          const statusIcon = document.createElement('span');
          statusIcon.className = 'vtt-row-status';
          statusIcon.setAttribute('aria-hidden', 'true');
          controls.appendChild(statusIcon);
        }
        row.appendChild(controls);
        transcript.appendChild(row);
      });
    }
    function scrollRowIntoView(row) {
      const playerEl = document.getElementById('vtt-adj-player');
      if (!playerEl) return;
      const playerBottom = playerEl.getBoundingClientRect().bottom;
      const rowTop = row.getBoundingClientRect().top;
      if (rowTop > playerBottom + 4) {
        window.scrollBy({
          top: rowTop - playerBottom - 8,
          behavior: 'smooth'
        });
      }
    }
    function flashRow(row) {
      row.classList.remove('vtt-flash');
      void row.offsetWidth;
      row.classList.add('vtt-flash');
    }

    // ── Playback sync ────────────────────────────────────────────────────────

    audio.addEventListener('timeupdate', () => {
      const t = audio.currentTime;
      if (currentTime) currentTime.textContent = fmtTime(t);
      if (seekStopAt !== null) return; // seeking — don't advance the active row

      let found = null;
      for (const row of transcript.querySelectorAll('.vtt-phrase-row')) {
        const start = parseSec(row.querySelector('.vtt-start-inp').value);
        const end = parseSec(row.querySelector('.vtt-end-inp').value);
        if (t >= start && t < end) {
          found = row;
          break;
        }
      }
      if (found !== activeRow) {
        if (activeRow) activeRow.classList.remove('is-current');
        activeRow = found;
        if (found) {
          found.classList.add('is-current');
          scrollRowIntoView(found);
        }
      }
    });

    // ── Spacebar play / pause ────────────────────────────────────────────────

    document.addEventListener('keydown', e => {
      if (e.code !== 'Space') return;
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
      e.preventDefault();
      audio.paused ? audio.play() : audio.pause();
    });

    // ── Toolbar ──────────────────────────────────────────────────────────────

    const applyShiftBtn = document.getElementById('vtt-adj-apply-shift');
    if (applyShiftBtn) {
      applyShiftBtn.addEventListener('click', () => {
        const delta = parseFloat(document.getElementById('vtt-adj-shift-input').value);
        if (!isFinite(delta) || delta === 0) return;
        for (const row of transcript.querySelectorAll('.vtt-phrase-row')) {
          const si = row.querySelector('.vtt-start-inp');
          const ei = row.querySelector('.vtt-end-inp');
          si.value = fmtTime(Math.max(0, parseSec(si.value) + delta));
          ei.value = fmtTime(Math.max(0, parseSec(ei.value) + delta));
          si.classList.remove('invalid');
          ei.classList.remove('invalid');
          markRowDirty(row);
        }
        showStatus(`Shifted all ${cues.length} cues by ${delta > 0 ? '+' : ''}${delta}s`);
      });
    }
    const downloadBtn = document.getElementById('vtt-adj-download');
    if (downloadBtn) {
      downloadBtn.addEventListener('click', () => {
        const blob = new Blob([buildVTT()], {
          type: 'text/vtt;charset=utf-8'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = FILENAME;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showStatus(FILENAME + ' downloaded');
      });
    }
    const resetBtn = document.getElementById('vtt-adj-reset');
    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        if (!confirm('Reset all timing to the original loaded file?')) return;
        cues = parseVTT(originalVTT);
        renderTranscript();
        showStatus('Reset to original');
        if (CAN_AUTOSAVE) autoSave();
      });
    }
    function showStatus(msg) {
      if (!statusMsg) return;
      statusMsg.textContent = msg;
      clearTimeout(statusTimer);
      statusTimer = setTimeout(() => {
        statusMsg.textContent = '';
      }, 3500);
    }

    // ── Bootstrap ────────────────────────────────────────────────────────────

    if (!VTT_URL) {
      transcript.innerHTML = '<p class="vtt-adj-load-error">No VTT URL configured for this translation.</p>';
      return;
    }
    fetch(VTT_URL).then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.text();
    }).then(text => {
      originalVTT = text;
      cues = parseVTT(text);
      renderTranscript();
      showStatus(`${cues.length} cues loaded`);
    }).catch(err => {
      transcript.innerHTML = `<p class="vtt-adj-load-error">Could not load VTT — ${err.message}.</p>`;
    });
  })();

})();
//# sourceMappingURL=vtt-adjustment.js.map
