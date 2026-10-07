/**
 * FitTermin – klijentski JavaScript
 *
 * - AJAX pozivi ka veb servisima aplikacije (rezervacija, otkazivanje, brisanje, pretraga, upload)
 * - Izmena sadržaja stranice iz JavaScript-a (DOM): kartice, brojači mesta, toast poruke
 * - Spoljni veb servis: Open-Meteo API (vremenska prognoza)
 */
(() => {
    'use strict';

    // ---------------------------------------------------------------
    // Podaci iz <meta> tagova (postavlja ih PHP u header.php)
    // ---------------------------------------------------------------
    const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content ?? '';
    const BASE_URL = meta('base-url');
    const CSRF_TOKEN = meta('csrf-token');
    const USER_ROLE = meta('user-role'); // 'admin' | 'clan' | 'gost'
    const [GYM_LAT, GYM_LON] = meta('gym-location').split(',');

    const url = (path) => `${BASE_URL}/${path.replace(/^\//, '')}`;

    /** Zaštita od XSS-a kada podatke ubacujemo u HTML */
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[ch]));

    // ---------------------------------------------------------------
    // AJAX helper – svi pozivi ka našim veb servisima idu kroz ovu funkciju
    // ---------------------------------------------------------------
    async function api(path, { method = 'GET', data = null } = {}) {
        const options = {
            method,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': CSRF_TOKEN,
                'Accept': 'application/json',
            },
        };
        if (data) {
            options.body = data instanceof FormData ? data : new URLSearchParams(data);
        }

        const response = await fetch(url(path), options);
        let json;
        try {
            json = await response.json();
        } catch {
            throw new Error('Server je vratio neočekivan odgovor.');
        }
        if (!response.ok || json.success === false) {
            throw new Error(json.message || 'Došlo je do greške.');
        }
        return json;
    }

    // ---------------------------------------------------------------
    // Toast obaveštenja (element se pravi iz JavaScript-a)
    // ---------------------------------------------------------------
    function toast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${escapeHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Zatvori"></button>
            </div>`;
        container.appendChild(el);

        const bsToast = new bootstrap.Toast(el, { delay: 3500 });
        bsToast.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    // ---------------------------------------------------------------
    // Osvežavanje prikaza treninga posle rezervacije / otkazivanja
    // ---------------------------------------------------------------
    function updateTreningState(t) {
        if (!t) return;
        document.querySelectorAll(`[data-trening-card="${t.id}"]`).forEach((card) => {
            const slobodno = card.querySelector('[data-slobodno]');
            if (slobodno) slobodno.textContent = `${t.slobodno} / ${t.kapacitet}`;

            const bar = card.querySelector('[data-progress]');
            if (bar) {
                bar.style.width = `${t.kapacitet ? Math.round((t.zauzeto / t.kapacitet) * 100) : 100}%`;
                bar.classList.toggle('bg-danger', t.slobodno === 0);
                bar.classList.toggle('bg-warning', t.slobodno > 0);
            }

            const btn = card.querySelector('[data-action="rezervisi"], [data-action="otkazi"]');
            if (btn) setReserveButton(btn, t);
        });
    }

    /** Menja dugme Rezerviši <-> Otkaži */
    function setReserveButton(btn, t) {
        const big = btn.classList.contains('w-100');
        btn.disabled = false;
        btn.classList.remove('btn-warning', 'btn-danger');

        if (t.rezervisano) {
            btn.dataset.action = 'otkazi';
            btn.classList.add('btn-danger');
            btn.textContent = big ? 'Otkaži rezervaciju' : 'Otkaži';
        } else {
            btn.dataset.action = 'rezervisi';
            btn.classList.add('btn-warning');
            if (t.slobodno === 0) {
                btn.disabled = true;
                btn.textContent = 'Popunjeno';
            } else {
                btn.textContent = big ? 'Rezerviši mesto' : 'Rezerviši';
            }
        }
    }

    function setLoading(btn, loading) {
        if (loading) {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        } else if (btn.dataset.originalHtml !== undefined) {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalHtml;
            delete btn.dataset.originalHtml;
        }
    }

    // ---------------------------------------------------------------
    // Klikovi na akcije (event delegation – radi i za kartice dodate kasnije)
    // ---------------------------------------------------------------
    document.addEventListener('click', async (event) => {
        const btn = event.target.closest('[data-action]');
        if (!btn) return;

        const id = btn.dataset.id;
        const action = btn.dataset.action;

        if (action === 'obrisi' && !confirm(`Obrisati trening "${btn.dataset.naziv}" i sve njegove rezervacije?`)) {
            return;
        }

        setLoading(btn, true);
        try {
            if (action === 'rezervisi') {
                const res = await api('api/rezervacije', { method: 'POST', data: { trening_id: id } });
                delete btn.dataset.originalHtml;
                updateTreningState(res.trening);
                toast(res.message, 'success');
            }

            if (action === 'otkazi') {
                const res = await api(`api/rezervacije/${id}/otkazi`, { method: 'POST' });
                delete btn.dataset.originalHtml;

                if (btn.hasAttribute('data-remove-row')) {
                    removeReservationRow(btn.closest('tr'));
                } else {
                    updateTreningState(res.trening);
                }
                toast(res.message, 'secondary');
            }

            if (action === 'obrisi') {
                const res = await api(`api/treninzi/${id}/obrisi`, { method: 'POST' });
                const card = btn.closest('[data-trening-card]');
                card?.classList.add('fade-out');
                setTimeout(() => {
                    card?.remove();
                    updateResultCount();
                }, 300);
                toast(res.message, 'success');
            }
        } catch (err) {
            setLoading(btn, false);
            toast(err.message, 'danger');
        }
    });

    function removeReservationRow(row) {
        if (!row) return;
        row.classList.add('fade-out');
        setTimeout(() => {
            const tbody = row.parentElement;
            row.remove();
            if (tbody && tbody.children.length === 0) {
                tbody.closest('.card')?.remove();
                document.getElementById('nemaRezervacija')?.classList.remove('d-none');
            }
        }, 300);
    }

    // ---------------------------------------------------------------
    // Pretraga i filtriranje rasporeda preko AJAX-a
    // ---------------------------------------------------------------
    const filterForm = document.getElementById('filterForm');
    const lista = document.getElementById('treninziLista');

    function updateResultCount(count) {
        if (!lista) return;
        const n = count ?? lista.querySelectorAll('[data-trening-card]').length;
        const counter = document.getElementById('resultCount');
        if (counter) counter.textContent = `Pronađeno treninga: ${n}`;
        document.getElementById('praznaLista')?.classList.toggle('d-none', n > 0);
    }

    /** Pravi HTML kartice treninga (isti izgled kao app/views/treninzi/_card.php) */
    function renderCard(t) {
        const procenat = t.kapacitet ? Math.round((t.zauzeto / t.kapacitet) * 100) : 100;
        const slika = t.slika_url
            ? `<img src="${escapeHtml(t.slika_url)}" class="card-img-top trening-img" alt="${escapeHtml(t.naziv)}">`
            : `<div class="trening-img trening-placeholder kat-${escapeHtml(t.kategorija.toLowerCase())}"><i class="bi bi-activity"></i></div>`;

        let akcije = `<a href="${escapeHtml(t.url)}" class="btn btn-outline-dark btn-sm">Detalji</a>`;
        if (USER_ROLE === 'clan' && !t.proslo) {
            akcije += t.rezervisano
                ? `<button class="btn btn-danger btn-sm" data-action="otkazi" data-id="${t.id}">Otkaži</button>`
                : `<button class="btn btn-warning btn-sm" data-action="rezervisi" data-id="${t.id}" ${t.slobodno === 0 ? 'disabled' : ''}>${t.slobodno === 0 ? 'Popunjeno' : 'Rezerviši'}</button>`;
        } else if (USER_ROLE === 'gost' && !t.proslo) {
            akcije += `<a href="${url('login')}" class="btn btn-warning btn-sm">Prijavi se za rezervaciju</a>`;
        }
        if (USER_ROLE === 'admin') {
            akcije += `
                <a href="${url(`treninzi/${t.id}/izmeni`)}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-outline-danger btn-sm" data-action="obrisi" data-id="${t.id}" data-naziv="${escapeHtml(t.naziv)}"><i class="bi bi-trash"></i></button>`;
        }

        return `
            <div class="col" data-trening-card="${t.id}">
                <div class="card h-100 shadow-sm trening-card">
                    ${slika}
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="badge text-bg-dark">${escapeHtml(t.kategorija)}</span>
                            ${t.proslo ? '<span class="badge text-bg-secondary">Završen</span>' : ''}
                        </div>
                        <h3 class="h5 card-title mb-1">${escapeHtml(t.naziv)}</h3>
                        <p class="small text-muted mb-2">
                            <i class="bi bi-calendar-event"></i> ${escapeHtml(t.dan)}, ${escapeHtml(t.datum)}<br>
                            <i class="bi bi-person"></i> ${escapeHtml(t.trener)} &middot; <i class="bi bi-clock"></i> ${t.trajanje_min} min
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Slobodna mesta</span>
                                <strong data-slobodno>${t.slobodno} / ${t.kapacitet}</strong>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar ${t.slobodno === 0 ? 'bg-danger' : 'bg-warning'}" data-progress style="width: ${procenat}%"></div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">${akcije}</div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    let filterTimer = null;
    let filterRequest = 0;

    async function loadTreninzi() {
        const params = new URLSearchParams(new FormData(filterForm));
        const requestId = ++filterRequest;
        lista.classList.add('loading');

        try {
            const res = await api(`api/treninzi?${params}`);
            if (requestId !== filterRequest) return; // stigao je noviji odgovor

            lista.innerHTML = res.data.map(renderCard).join('');
            updateResultCount(res.count);
            // URL u adresnoj traci prati filtere (bez ponovnog učitavanja stranice)
            history.replaceState(null, '', `${url('treninzi')}${params.toString() ? '?' + params : ''}`);
        } catch (err) {
            toast(err.message, 'danger');
        } finally {
            lista.classList.remove('loading');
        }
    }

    if (filterForm && lista) {
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            loadTreninzi();
        });
        filterForm.querySelector('#pretraga')?.addEventListener('input', () => {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(loadTreninzi, 300);
        });
        filterForm.querySelector('#kategorija')?.addEventListener('change', loadTreninzi);
    }

    // ---------------------------------------------------------------
    // Upload slike preko veb servisa /api/upload
    // ---------------------------------------------------------------
    const slikaInput = document.getElementById('slikaInput');
    if (slikaInput) {
        const hidden = document.getElementById('slikaFile');
        const previewWrap = document.getElementById('slikaPreviewWrap');
        const preview = document.getElementById('slikaPreview');
        const progress = document.getElementById('uploadProgress');

        slikaInput.addEventListener('change', async () => {
            const file = slikaInput.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                toast('Slika je veća od 2 MB.', 'danger');
                slikaInput.value = '';
                return;
            }

            const data = new FormData();
            data.append('slika', file);
            progress.classList.remove('d-none');
            slikaInput.classList.remove('is-invalid');

            try {
                const res = await api('api/upload', { method: 'POST', data });
                hidden.value = res.file;
                preview.src = res.url;
                previewWrap.classList.remove('d-none');
                toast(res.message, 'success');
            } catch (err) {
                toast(err.message, 'danger');
                slikaInput.value = '';
            } finally {
                progress.classList.add('d-none');
            }
        });

        document.getElementById('slikaUkloni')?.addEventListener('click', () => {
            hidden.value = '';
            slikaInput.value = '';
            preview.removeAttribute('src');
            previewWrap.classList.add('d-none');
        });
    }

    // ---------------------------------------------------------------
    // Registracija – jačina lozinke i provera poklapanja (uživo, dok korisnik kuca)
    // ---------------------------------------------------------------
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const pass = registerForm.querySelector('#lozinka');
        const confirmPass = registerForm.querySelector('#lozinka_potvrda');
        const bar = document.getElementById('passwordStrengthBar');
        const text = document.getElementById('passwordStrengthText');

        pass.addEventListener('input', () => {
            const v = pass.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (/[A-Za-z]/.test(v) && /\d/.test(v)) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) score++;

            const levels = [
                ['0%', 'bg-danger', 'Najmanje 8 karaktera, slova i brojevi.'],
                ['25%', 'bg-danger', 'Slaba lozinka'],
                ['50%', 'bg-warning', 'Srednja lozinka'],
                ['75%', 'bg-info', 'Dobra lozinka'],
                ['100%', 'bg-success', 'Jaka lozinka'],
            ];
            const [width, color, label] = levels[score];
            bar.style.width = width;
            bar.className = `progress-bar ${color}`;
            text.textContent = label;
        });

        const checkMatch = () => {
            confirmPass.classList.toggle('is-invalid', confirmPass.value !== '' && confirmPass.value !== pass.value);
        };
        confirmPass.addEventListener('input', checkMatch);
        pass.addEventListener('input', checkMatch);

        registerForm.addEventListener('submit', (e) => {
            if (pass.value !== confirmPass.value) {
                e.preventDefault();
                confirmPass.classList.add('is-invalid');
                confirmPass.focus();
            }
        });
    }

    // ---------------------------------------------------------------
    // Spoljni veb servis: Open-Meteo (https://open-meteo.com) – besplatan, bez API ključa
    // ---------------------------------------------------------------
    const WEATHER = {
        0: ['Vedro', 'bi-sun'],
        1: ['Pretežno vedro', 'bi-cloud-sun'],
        2: ['Delimično oblačno', 'bi-cloud-sun'],
        3: ['Oblačno', 'bi-cloud'],
        45: ['Magla', 'bi-cloud-fog'],
        48: ['Magla sa injem', 'bi-cloud-fog'],
        51: ['Slaba rosulja', 'bi-cloud-drizzle'],
        53: ['Rosulja', 'bi-cloud-drizzle'],
        55: ['Jaka rosulja', 'bi-cloud-drizzle'],
        56: ['Ledena rosulja', 'bi-cloud-drizzle'],
        57: ['Ledena rosulja', 'bi-cloud-drizzle'],
        61: ['Slaba kiša', 'bi-cloud-rain'],
        63: ['Kiša', 'bi-cloud-rain'],
        65: ['Jaka kiša', 'bi-cloud-rain-heavy'],
        66: ['Ledena kiša', 'bi-cloud-sleet'],
        67: ['Ledena kiša', 'bi-cloud-sleet'],
        71: ['Slab sneg', 'bi-snow'],
        73: ['Sneg', 'bi-snow'],
        75: ['Jak sneg', 'bi-snow2'],
        77: ['Snežna zrna', 'bi-snow'],
        80: ['Pljuskovi', 'bi-cloud-rain-heavy'],
        81: ['Pljuskovi', 'bi-cloud-rain-heavy'],
        82: ['Jaki pljuskovi', 'bi-cloud-rain-heavy'],
        85: ['Snežni pljuskovi', 'bi-cloud-snow'],
        86: ['Snežni pljuskovi', 'bi-cloud-snow'],
        95: ['Grmljavina', 'bi-cloud-lightning-rain'],
        96: ['Grmljavina sa gradom', 'bi-cloud-lightning-rain'],
        99: ['Grmljavina sa gradom', 'bi-cloud-lightning-rain'],
    };
    const describeWeather = (code) => WEATHER[code] ?? ['Nepoznato', 'bi-question-circle'];

    function showWeather(box, temp, code) {
        const [desc, icon] = describeWeather(code);
        box.querySelector('[data-weather-temp]').textContent = `${Math.round(temp)}°C`;
        box.querySelector('[data-weather-desc]').textContent = desc;
        const iconEl = box.querySelector('[data-weather-icon]');
        iconEl.className = iconEl.className.replace(/bi-[\w-]+/g, '').trim() + ` bi ${icon}`;
    }

    function weatherError(box, message) {
        box.querySelector('[data-weather-desc]').textContent = message;
        const iconEl = box.querySelector('[data-weather-icon]');
        iconEl.className = iconEl.className.replace(/bi-[\w-]+/g, '').trim() + ' bi bi-cloud-slash';
    }

    const OPEN_METEO = 'https://api.open-meteo.com/v1/forecast';

    // Trenutno vreme na početnoj strani
    const weatherNow = document.getElementById('weatherNow');
    if (weatherNow) {
        const params = new URLSearchParams({
            latitude: GYM_LAT, longitude: GYM_LON,
            current: 'temperature_2m,weather_code',
            timezone: 'Europe/Belgrade',
        });
        fetch(`${OPEN_METEO}?${params}`)
            .then((r) => (r.ok ? r.json() : Promise.reject()))
            .then((data) => {
                const { temperature_2m: temp, weather_code: code } = data.current;
                showWeather(weatherNow, temp, code);

                const tip = weatherNow.querySelector('[data-weather-tip]');
                if (code >= 51) tip.textContent = 'Napolju je kišovito – savršen dan za trening u sali!';
                else if (temp < 5) tip.textContent = 'Hladno je napolju – zagrej se kod nas.';
                else if (temp > 28) tip.textContent = 'Vruće je – u sali je klimatizovano.';
                else tip.textContent = 'Lep dan za trening!';
            })
            .catch(() => weatherError(weatherNow, 'Prognoza trenutno nije dostupna.'));
    }

    // Prognoza za vreme konkretnog treninga (stranica detalja)
    const forecastBox = document.querySelector('[data-forecast]');
    if (forecastBox) {
        const iso = forecastBox.dataset.datetime;      // npr. 2026-10-07T18:00:00+02:00 (lokalno vreme)
        const date = iso.slice(0, 10);
        const hour = iso.slice(11, 13);
        const start = new Date(iso);
        const daysAhead = (start - new Date()) / 86400000;

        if (daysAhead < 0) {
            weatherError(forecastBox, 'Trening je završen.');
        } else if (daysAhead > 15) {
            weatherError(forecastBox, 'Prognoza je dostupna najviše 16 dana unapred.');
        } else {
            const params = new URLSearchParams({
                latitude: GYM_LAT, longitude: GYM_LON,
                hourly: 'temperature_2m,weather_code',
                timezone: 'Europe/Belgrade',
                start_date: date, end_date: date,
            });
            fetch(`${OPEN_METEO}?${params}`)
                .then((r) => (r.ok ? r.json() : Promise.reject()))
                .then((data) => {
                    const i = data.hourly.time.indexOf(`${date}T${hour}:00`);
                    if (i === -1) throw new Error();
                    showWeather(forecastBox, data.hourly.temperature_2m[i], data.hourly.weather_code[i]);
                })
                .catch(() => weatherError(forecastBox, 'Prognoza trenutno nije dostupna.'));
        }
    }
})();
