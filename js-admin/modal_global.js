/* ===== GLOBAL MODAL SYSTEM ===== */
(function () {
    function getOverlay() {
        let el = document.getElementById('sm-overlay');
        if (!el) {
            el = document.createElement('div');
            el.id = 'sm-overlay';
            el.className = 'sm-overlay';
            document.body.appendChild(el);
        }
        return el;
    }

    window.SphinxModal = {
        _onConfirm: null,
        _onCancel:  null,
        _onOk:      null,
        _clearTimer: null,

        _show(html) {
            const ov = getOverlay();
            if (this._clearTimer) {
                clearTimeout(this._clearTimer);
                this._clearTimer = null;
            }
            ov.innerHTML = html;
            ov.classList.add('sm-show');
        },

        hide() {
            const ov = document.getElementById('sm-overlay');
            if (ov) {
                if (this._clearTimer) {
                    clearTimeout(this._clearTimer);
                    this._clearTimer = null;
                }
                ov.classList.remove('sm-show');
                this._clearTimer = setTimeout(() => {
                    if (ov && !ov.classList.contains('sm-show')) ov.innerHTML = '';
                    this._clearTimer = null;
                }, 220);
            }
            this._onConfirm = null;
            this._onCancel  = null;
            this._onOk      = null;
        },

        _doConfirm() { const cb = this._onConfirm; this.hide(); if (cb) setTimeout(cb, 0); },
        _doCancel()  { const cb = this._onCancel;  this.hide(); if (cb) setTimeout(cb, 0); },
        _doOk()      { const cb = this._onOk;      this.hide(); if (cb) setTimeout(cb, 0); },

        /* ===== Frame 5 — Hapus Data ===== */
        hapus(msg, onYes, onBatal) {
            this._onConfirm = onYes   || null;
            this._onCancel  = onBatal || null;
            this._show(`
                <div class="sm-box">
                    <div class="sm-icon-wrap sm-icon-red">
                        <i class="fa-solid fa-trash"></i>
                    </div>
                    <div class="sm-title sm-red">Hapus Data?</div>
                    <div class="sm-sub">${_smEsc(msg ?? 'Tindakan ini tidak dapat dibatalkan.')}</div>
                    <div class="sm-actions">
                        <button class="sm-btn sm-btn-outline" onclick="SphinxModal._doCancel()">Batal</button>
                        <button class="sm-btn sm-btn-red" onclick="SphinxModal._doConfirm()">
                            <i class="fa-solid fa-trash"></i> Ya, Hapus
                        </button>
                    </div>
                </div>
            `);
        },

        /* ===== Frame 6 — Berhasil ===== */
        berhasil(msg, onOk) {
            this._onOk = onOk || null;
            this._show(`
                <div class="sm-box">
                    <div class="sm-icon-wrap sm-icon-green">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="sm-title sm-green">Berhasil</div>
                    <div class="sm-sub">${_smEsc(msg ?? 'Data telah berhasil disimpan ke sistem.')}</div>
                    <div class="sm-actions sm-single">
                        <button class="sm-btn sm-btn-green" onclick="SphinxModal._doOk()">Ok</button>
                    </div>
                </div>
            `);
        },

        /* ===== Frame 7 — Konfirmasi Tindakan ===== */
        konfirmasi(title, msg, onLanjut, onBatal) {
            this._onConfirm = onLanjut || null;
            this._onCancel  = onBatal  || null;
            this._show(`
                <div class="sm-box">
                    <div class="sm-title">${_smEsc(title ?? 'Konfirmasi Tindakan')}</div>
                    <div class="sm-sub">${_smEsc(msg ?? 'Apakah kamu yakin ingin melanjutkan perubahan ini?')}</div>
                    <div class="sm-actions">
                        <button class="sm-btn sm-btn-outline" onclick="SphinxModal._doCancel()">Batal</button>
                        <button class="sm-btn sm-btn-purple" onclick="SphinxModal._doConfirm()">Lanjutkan</button>
                    </div>
                </div>
            `);
        },

        /* ===== Peringatan / Validasi sederhana ===== */
        peringatan(msg, onOk) {
            this._onOk = onOk || null;
            this._show(`
                <div class="sm-box">
                    <div class="sm-icon-wrap sm-icon-purple">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="sm-title">Perhatian</div>
                    <div class="sm-sub">${_smEsc(msg ?? 'Terjadi kesalahan.')}</div>
                    <div class="sm-actions sm-single">
                        <button class="sm-btn sm-btn-purple" onclick="SphinxModal._doOk()">Ok</button>
                    </div>
                </div>
            `);
        }
    };

    function _smEsc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Close on backdrop click
    document.addEventListener('click', function (e) {
        const ov = document.getElementById('sm-overlay');
        if (ov && e.target === ov) SphinxModal.hide();
    });
})();
