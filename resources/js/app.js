import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* Region names are matched loosely on the way into a picker and strictly on
   the way out: a picker can only return a row that exists in the region table,
   so "South Tangerang" can never be stored. */
function norm(value) {
    return String(value ?? '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .replace(/\b(kota|kabupaten|administrasi|dki|daerah|khusus|ibukota)\b/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Shared by the admin form and the public filter: same fields, same behaviour,
 * so the two pickers cannot drift apart. The public filter reads its lists from
 * the page; the admin form overrides loadCities/loadDistricts to fetch them.
 */
const PICKER = {
    open: '',
    active: 0,

    qProv: '',
    qCity: '',
    qDistrict: '',
    selProv: null,
    selCity: null,
    selDist: null,
    selLocation: null,

    cities: [],
    districts: [],
    provinces: [],
    regionCities: {},
    regionDistricts: {},
    busy: false,

    nameOf(item) {
        return typeof item === 'string' ? item : item.name;
    },

    // Prefix first, substring only as a fallback. Typing "p" under Jakarta
    // Utara must offer Penjaringan and Pademangan, not Tanjung Priok.
    options(list, query, selected) {
        if (!query || query === selected) {
            return list;
        }

        const needle = norm(query);

        if (!needle) {
            return list;
        }

        const starts = list.filter((item) => norm(this.nameOf(item)).startsWith(needle));

        return starts.length ? starts : list.filter((item) => norm(this.nameOf(item)).includes(needle));
    },

    focusField(field) {
        this.open = field;
        this.active = 0;
    },

    keys(event, list, pick, field) {
        if (event.key === 'Escape') {
            this.open = '';
            return;
        }

        if (!list.length) {
            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();

            if (this.open !== field) {
                this.focusField(field);
                return;
            }

            const step = event.key === 'ArrowDown' ? 1 : -1;
            this.active = (this.active + step + list.length) % list.length;
            event.target.parentElement.querySelector('ul')?.children[this.active]?.scrollIntoView({ block: 'nearest' });
        } else if (event.key === 'Enter') {
            event.preventDefault();
            pick.call(this, list[Math.min(this.active, list.length - 1)]);
        } else if (event.key === 'Tab') {
            this.open = '';
        }
    },

    initRegions() {
        this.loadCities();
        this.loadDistricts();
    },

    loadCities() {
        this.cities = this.selProv ? (this.regionCities[this.selProv] ?? []) : [];
    },

    loadDistricts() {
        this.districts = this.selCity ? (this.regionDistricts[`${this.selProv}|${this.selCity}`] ?? []) : [];
    },

    resetBelow(level) {
        if (level <= 1) {
            this.selCity = null;
            this.qCity = '';
            this.districts = [];
        }
        if (level <= 2) {
            this.selDist = null;
            this.qDistrict = '';
            this.selLocation = null;
        }
    },

    typeProvince(value) {
        this.qProv = value;
        this.open = 'province';
        this.active = 0;

        if (this.selProv && value !== this.selProv) {
            this.selProv = null;
            this.cities = [];
            this.resetBelow(1);
            this.fillNote = '';
        }
    },

    pickProvince(item) {
        this.selProv = this.nameOf(item);
        this.qProv = this.selProv;
        this.fillNote = '';
        this.resetBelow(1);
        this.loadCities();
        this.open = 'city';
        this.active = 0;
    },

    typeCity(value) {
        this.qCity = value;
        this.open = 'city';
        this.active = 0;

        if (this.selCity && value !== this.selCity) {
            this.selCity = null;
            this.resetBelow(2);
        }
    },

    pickCity(item) {
        this.selCity = this.nameOf(item);
        this.qCity = this.selCity;
        this.fillNote = '';
        this.resetBelow(2);
        this.loadDistricts();
        this.open = 'district';
        this.active = 0;
    },

    typeDistrict(value) {
        this.qDistrict = value;
        this.open = 'district';
        this.active = 0;

        if (this.selDist && value !== this.selDist) {
            this.selDist = null;
            this.selLocation = null;
        }
    },

    pickDistrict(item) {
        this.selDist = this.nameOf(item);
        this.qDistrict = this.selDist;
        this.selLocation = item && typeof item === 'object' && item.id ? item : null;
        this.fillNote = '';
        this.open = '';
        this.active = 0;
    },
};

/* Both the admin price field and the KPR widget rewrite the value with
   thousands dots on every keystroke, which moves the caret to the end; this
   puts it back after the same count of digits. Returns the digits typed, so
   each caller keeps its own numeric state. On window because the KPR widget
   lives in a Blade template. */
function formatRupiah(input) {
    const digits = input.value.replace(/\D/g, '');
    const digitsLeft = input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;

    input.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    let pos = 0;
    for (let seen = 0; pos < input.value.length && seen < digitsLeft; pos++) {
        if (/\d/.test(input.value[pos])) seen++;
    }

    input.setSelectionRange(pos, pos);

    return digits;
}

window.formatRupiah = formatRupiah;

document.addEventListener('alpine:init', () => {
    /* One listbox for every dropdown: the location picker's panel look, applied
       to plain option lists. The hidden input is what actually submits. */
    Alpine.data('selectBox', (config = {}) => ({
        open: false,
        value: String(config.value ?? ''),
        options: config.options ?? [],
        submit: config.submit === true,
        header: config.header === true,
        active: 0,

        init() {
            this.active = Math.max(0, this.options.findIndex((option) => option.value === this.value));

            // Without Alpine the plain <select> in <noscript> is what submits.
            if (this.$refs.input && ! this.$refs.input.name) {
                this.$refs.input.name = this.$refs.input.dataset.name;
            }
        },

        get label() {
            const hit = this.options.find((option) => option.value === this.value);

            return hit ? hit.label : '';
        },

        toggle() {
            this.open = !this.open;

            if (this.open) {
                this.active = Math.max(0, this.options.findIndex((option) => option.value === this.value));
                this.$nextTick(() => this.$refs.panel.focus());
            }
        },

        move(step) {
            if (!this.open) {
                this.toggle();

                return;
            }

            const count = this.options.length;

            this.active = (this.active + step + count) % count;
        },

        pick(option) {
            if (!option) {
                return;
            }

            this.value = option.value;
            this.open = false;
            this.$refs.input.value = option.value;
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
            this.$root.dispatchEvent(new CustomEvent('select', { bubbles: true, detail: { value: option.value } }));
            this.$refs.button.focus();

            if (this.submit) {
                this.$nextTick(() => this.$refs.input.form?.requestSubmit());
            }
        },

        keydown(event) {
            if (event.key === 'Escape') {
                this.open = false;
                this.$refs.button.focus();

                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                this.move(event.key === 'ArrowDown' ? 1 : -1);

                return;
            }

            if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                this.active = event.key === 'Home' ? 0 : this.options.length - 1;

                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();

                if (this.open) {
                    this.pick(this.options[this.active]);
                } else {
                    this.toggle();
                }
            }
        },
    }));

    /* Admin/agen form: three pickers plus the address suggestions and the pin
       preview. The hidden location_id is set only by pickDistrict, so typed
       text that was never picked cannot be saved. */
    Alpine.data('listingLocation', (config = {}) => ({
        ...PICKER,

        addressUrl: config.addressUrl,
        regionUrl: config.regionUrl,

        address: config.address ?? '',
        selProv: config.province ?? null,
        qProv: config.province ?? '',
        selCity: config.city ?? null,
        qCity: config.city ?? '',
        selDist: config.district ?? null,
        qDistrict: config.district ?? '',
        selLocation: config.locationId ? { id: config.locationId, name: config.district } : null,
        pin: config.latitude != null && config.latitude !== '' && config.longitude != null && config.longitude !== ''
            ? { lat: config.latitude, lng: config.longitude }
            : null,

        pinSrc: '',
        pinBusy: false,
        pinNote: '',

        suggestions: [],
        suggestOpen: false,
        suggestActive: -1,
        suggestBusy: false,
        suggestNote: '',

        fillNote: '',
        fillOk: false,

        init() {
            this.loadProvinces();
            this.initRegions();
            this.pinSrc = this.pin ? this.pinUrl() : '';
        },

        async loadProvinces() {
            this.provinces = await this.getJson('level=province');
        },

        /** @param {string} query */
        async getJson(query) {
            this.busy = true;

            try {
                const response = await fetch(`${this.regionUrl}?${query}`, { headers: { Accept: 'application/json' } });

                return response.ok ? (await response.json()).options ?? [] : [];
            } catch {
                return [];
            } finally {
                this.busy = false;
            }
        },

        async loadCities() {
            const province = this.selProv;
            this.cities = [];

            if (!province) {
                return;
            }

            const options = await this.getJson(`level=city&province=${encodeURIComponent(province)}`);

            if (this.selProv === province) {
                this.cities = options;
            }
        },

        async loadDistricts() {
            const province = this.selProv;
            const city = this.selCity;
            this.districts = [];

            if (!province || !city) {
                return;
            }

            const options = await this.getJson(
                `level=district&province=${encodeURIComponent(province)}&city=${encodeURIComponent(city)}`,
            );

            if (this.selProv === province && this.selCity === city) {
                this.districts = options;
            }
        },

        onAddressInput() {
            // The pin belongs to the address it was found for.
            this.pin = null;
            this.pinSrc = '';
            this.suggestActive = -1;
            window.clearTimeout(this.suggestTimer);

            const term = this.address.trim();

            if (term.length < 3) {
                this.suggestions = [];
                this.suggestOpen = false;
                this.suggestNote = '';
                return;
            }

            this.suggestTimer = window.setTimeout(() => this.searchAddress(term), 300);
        },

        /**
         * Enter only takes a suggestion that was highlighted first. A fully
         * typed address must survive Enter untouched.
         */
        addressKeys(event) {
            if (event.key === 'Escape') {
                this.suggestOpen = false;
                this.suggestActive = -1;
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                if (!this.suggestions.length) {
                    return;
                }

                event.preventDefault();
                this.suggestOpen = true;
                const step = event.key === 'ArrowDown' ? 1 : -1;
                const count = this.suggestions.length;

                this.suggestActive = this.suggestActive === -1
                    ? (step === 1 ? 0 : count - 1)
                    : (this.suggestActive + step + count) % count;

                event.target.parentElement.querySelector('ul')?.children[this.suggestActive]?.scrollIntoView({ block: 'nearest' });
                return;
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                const picked = this.suggestions[this.suggestActive];

                if (picked) {
                    this.pickSuggestion(picked);
                } else {
                    this.suggestOpen = false;
                }
            }
        },

        async searchAddress(term) {
            // Cancel the request the keystroke before this one started; Photon
            // answers slowly enough that they overlap.
            this.suggestAbort?.abort();
            const controller = (this.suggestAbort = new AbortController());
            this.suggestBusy = true;

            try {
                const response = await fetch(`${this.addressUrl}?q=${encodeURIComponent(term)}`, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                });
                const json = await response.json();

                // A slower earlier request must not overwrite newer typing.
                if (this.address.trim() !== term) {
                    return;
                }

                this.suggestions = json.results ?? [];
                this.suggestActive = -1;
                this.suggestOpen = this.suggestions.length > 0;
                this.suggestNote = this.suggestions.length
                    ? ''
                    : 'Photon tidak menemukan saran. Alamat tetap bisa diketik sendiri dan pin tetap bisa dicek.';
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                this.suggestions = [];
                this.suggestOpen = false;
                this.suggestNote = 'Layanan saran tidak merespons. Alamat tetap bisa diketik sendiri.';
            } finally {
                if (this.suggestAbort === controller) {
                    this.suggestBusy = false;
                }
            }
        },

        contextOf(suggestion) {
            const official = [suggestion.district, suggestion.city, suggestion.province].filter(Boolean);

            return official.length
                ? official.join(', ')
                : `${suggestion.context || 'Wilayah tidak dikenali'} · belum cocok dengan data resmi`;
        },

        pickSuggestion(suggestion) {
            this.suggestOpen = false;
            this.suggestActive = -1;
            this.pin = { lat: suggestion.lat, lng: suggestion.lng };
            this.fillNote = this.autoFill(suggestion);
            this.address = this.composeAddress(suggestion);
            this.pinSrc = this.pinUrl();
            this.pinNote = 'Titik diambil dari saran yang dipilih.';
        },

        /** The server already matched the result to official rows. */
        autoFill(suggestion) {
            if (suggestion.province) {
                this.selProv = suggestion.province;
                this.qProv = suggestion.province;
                this.resetBelow(1);
            }
            if (suggestion.city) {
                this.selCity = suggestion.city;
                this.qCity = suggestion.city;
                this.resetBelow(2);
            }
            if (suggestion.district && suggestion.location_id) {
                this.selDist = suggestion.district;
                this.qDistrict = suggestion.district;
                this.selLocation = { id: suggestion.location_id, name: suggestion.district };
            }

            this.loadCities();
            this.loadDistricts();

            // Report the levels that ended up empty, not the lookups that
            // failed: a region already filled by hand is not "tidak dikenali".
            const missing = [];

            if (!this.selProv) {
                missing.push('provinsi');
            }
            if (!this.selCity) {
                missing.push('kota/kabupaten');
            }
            if (!this.selDist) {
                missing.push('kecamatan');
            }

            this.fillOk = missing.length === 0;

            return this.fillOk
                ? 'Provinsi, kota, dan kecamatan terisi otomatis. Periksa lagi sebelum simpan.'
                : `Terisi sebagian. ${missing.join(', ')} belum terisi — pilih manual dari daftar resmi.`;
        },

        composeAddress(suggestion) {
            // Region names come from the official pickers, never from the map
            // service. The place name matters: an apartment's street alone
            // ("Jl. Danau Sunter Selatan") loses the building.
            const label = (suggestion.label || '').trim();
            const street = (suggestion.street || '').trim();
            const parts = [];

            if (label) {
                parts.push(label);
            }
            if (street && !norm(label).includes(norm(street))) {
                parts.push(street);
            }
            if (this.selDist) {
                parts.push(this.selDist);
            }
            if (this.selCity) {
                parts.push(this.selCity);
            }
            if (this.selProv) {
                parts.push(this.selProv);
            }

            return parts.filter(Boolean).join(', ');
        },

        pinUrl() {
            return this.pin
                ? `https://maps.google.com/maps?q=${this.pin.lat},${this.pin.lng}&z=17&output=embed`
                : `https://maps.google.com/maps?q=${encodeURIComponent(this.address.trim())}&output=embed`;
        },

        /** Picked point first, then a lookup of the typed text, then the text. */
        async lihatPin() {
            const term = this.address.trim();

            if (!term) {
                this.pinNote = 'Isi alamat dulu.';
                return;
            }

            if (this.pin) {
                this.pinSrc = this.pinUrl();
                this.pinNote = 'Titik dari saran yang dipilih (paling tepat).';
                return;
            }

            this.pinBusy = true;

            try {
                const response = await fetch(`${this.addressUrl}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                });
                const hit = ((await response.json()).results ?? [])[0];

                if (hit) {
                    this.pin = { lat: hit.lat, lng: hit.lng };
                    this.pinSrc = this.pinUrl();
                    this.pinNote = `Titik hasil pencarian alamat: ${hit.label} (${hit.context || 'tanpa konteks'}).`;
                } else {
                    this.pinSrc = this.pinUrl();
                    this.pinNote = 'Pencarian titik tidak menemukan lokasi. Peta memakai teks alamat — perkiraan, bukan titik pasti.';
                }
            } catch {
                this.pinSrc = this.pinUrl();
                this.pinNote = 'Layanan pencarian tidak merespons. Peta memakai teks alamat — perkiraan.';
            } finally {
                this.pinBusy = false;
            }
        },
    }));

    /* Admin/agen form: the price field is rewritten with thousands dots, so the
       shared formatter is all this component needs. */
    Alpine.data('priceInput', () => ({
        format() {
            formatRupiah(this.$el);
        },
    }));

    /* Admin photo list: reorder by dragging the six-dot handle, or with the
       arrow keys on it. Only the handle makes a row draggable, so selecting
       text inside the label and alt fields still behaves. */
    Alpine.data('photoOrder', () => ({
        row: null,

        grab(event) {
            const row = event.target.closest('[data-sort-row]');

            if (row) {
                row.draggable = true;
            }
        },

        begin(event) {
            const row = event.target.closest('[data-sort-row]');

            if (!row) {
                return;
            }

            this.row = row;
            row.classList.add('opacity-60');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', '');
        },

        over(event) {
            const target = event.target.closest('[data-sort-row]');

            if (!this.row || !target || target === this.row) {
                return;
            }

            const box = target.getBoundingClientRect();
            const after = event.clientY > box.top + box.height / 2;
            target.parentNode.insertBefore(this.row, after ? target.nextSibling : target);
        },

        end() {
            const row = this.row;

            if (!row) {
                return;
            }

            row.draggable = false;
            row.classList.remove('opacity-60');
            this.row = null;
            this.renumber(row.parentElement);
        },

        // A click on the handle leaves the row draggable; clear it so a later
        // text selection cannot drag the whole row. A running drag is cleaned
        // up by dragend instead.
        release(event) {
            if (this.row) {
                return;
            }

            const root = event.currentTarget.closest('[data-photo-order]');

            root.querySelectorAll('[data-sort-row][draggable="true"]').forEach((row) => {
                row.draggable = false;
            });
        },

        keys(event) {
            if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown') {
                return;
            }

            const row = event.target.closest('[data-sort-row]');

            if (!row) {
                return;
            }

            const sibling = event.key === 'ArrowUp' ? row.previousElementSibling : row.nextElementSibling;

            if (!sibling) {
                return;
            }

            event.preventDefault();

            if (event.key === 'ArrowUp') {
                row.parentNode.insertBefore(row, sibling);
            } else {
                row.parentNode.insertBefore(sibling, row);
            }

            this.renumber(row.parentElement);
            event.target.focus();
        },

        renumber(root) {
            [...root.querySelectorAll('[data-sort-row]')].forEach((row, index) => {
                row.querySelector('[data-sort-input]').value = index + 1;
                row.querySelector('[data-sort-index]').textContent = index + 1;
            });
        },
    }));

    /* Admin hero picker: one listing per page, so the selection has to survive
       a reload. Ticks stay local and are copied into the pager links and the
       search form before either is used. Every lookup starts from the root
       found through the event: inside a handler $el is that element, not the
       component. */
    Alpine.data('galleryPicker', (max) => ({
        timer: null,

        init() {
            this.refresh(this.$el);
        },

        sync(event) {
            const root = event.currentTarget.closest('[data-gallery-picker]');

            // The change already happened, so one over the cap means this tick.
            if (event.target.checked && this.picked(root).length > max) {
                event.target.checked = false;
                this.warn(root);

                return;
            }

            this.refresh(root);
        },

        unpick(event, id) {
            const root = event.currentTarget.closest('[data-gallery-picker]');
            const box = root.querySelector(`input[type="checkbox"][name="gallery[]"][value="${id}"]`);

            if (box) {
                box.checked = false;
            }

            // A pick from another page has no box here, only its hidden field.
            root.querySelector(`input[type="hidden"][name="gallery[]"][value="${id}"]`)?.remove();

            this.refresh(root);
        },

        /** Carried picks first, then the boxes ticked on this page. */
        picked(root) {
            const carried = [...root.querySelectorAll('input[type="hidden"][name="gallery[]"]')];
            const ticked = [...root.querySelectorAll('input[type="checkbox"][name="gallery[]"]')]
                .filter((box) => box.checked);

            return [...carried, ...ticked].map((input) => input.value);
        },

        warn(root) {
            const warning = root.querySelector('[data-limit-warning]');

            if (!warning) {
                return;
            }

            window.clearTimeout(this.timer);
            warning.hidden = false;
            this.timer = window.setTimeout(() => {
                warning.hidden = true;
            }, 3000);
        },

        refresh(root) {
            const picked = this.picked(root);
            const field = root.querySelector('[data-picks-field]');

            if (field) {
                field.value = picked.join(',');
            }

            root.querySelectorAll('[data-pager] a').forEach((link) => {
                const url = new URL(link.getAttribute('href'), window.location.origin);
                url.searchParams.set('pilih', picked.join(','));
                link.setAttribute('href', url.pathname + url.search);
            });

            root.querySelector('[data-count]').textContent = String(picked.length);

            // Every chip is already in the markup; the strip only shows picks.
            root.querySelectorAll('[data-picked-id]').forEach((chip) => {
                chip.hidden = ! picked.includes(chip.dataset.pickedId);
            });

            const empty = root.querySelector('[data-picked-empty]');

            if (empty) {
                empty.hidden = picked.length > 0;
            }
        },
    }));

    /* Public filter: server-rendered lists, limited to regions that have a
       published listing. Nothing is applied until Terapkan is pressed. */
    /* Public catalog: one search across provinsi, kota and kecamatan. Picking a
       place fills the three fields the server still filters on, so the query
       string and the chips stay exactly as they were. */
    Alpine.data('catalogFilter', (config = {}) => ({
        open: '',
        active: 0,
        places: config.places ?? [],
        picked: null,
        query: '',

        init() {
            this.picked = this.places.find((place) =>
                place.province === config.province
                && place.city === (config.city || null)
                && place.district === (config.district || null)
            ) ?? null;

            this.query = this.picked ? this.picked.label : '';
        },

        matches() {
            if (! this.query || (this.picked && this.query === this.picked.label)) {
                return this.places;
            }

            const needle = norm(this.query);

            if (! needle) {
                return this.places;
            }

            const starts = this.places.filter((place) => norm(place.label).startsWith(needle));

            return starts.length ? starts : this.places.filter((place) => norm(place.label).includes(needle));
        },

        focusField() {
            this.open = 'place';
            this.active = 0;
        },

        // Typing drops the picked place, so an edited box never submits a value
        // the buyer no longer sees.
        type() {
            this.picked = null;
            this.focusField();
        },

        keys(event) {
            if (event.key === 'Escape') {
                this.open = '';
                return;
            }

            const list = this.matches();

            if (! list.length) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();

                if (this.open !== 'place') {
                    this.focusField();
                    return;
                }

                const step = event.key === 'ArrowDown' ? 1 : -1;
                this.active = (this.active + step + list.length) % list.length;
                event.target.parentElement.querySelector('ul')?.children[this.active]?.scrollIntoView({ block: 'nearest' });
            } else if (event.key === 'Enter') {
                event.preventDefault();
                this.pick(list[Math.min(this.active, list.length - 1)]);
            } else if (event.key === 'Tab') {
                this.open = '';
            }
        },

        pick(place) {
            this.picked = place;
            this.query = place.label;
            this.open = '';
        },

        clear() {
            this.picked = null;
            this.query = '';
            this.open = 'place';
            this.active = 0;
        },
    }));

    /* Click-and-drag sideways scrolling for a single row, mouse only so touch
       keeps its native momentum scroll. A drag swallows the click that follows
       it, so dragging across a card does not open it. */
    Alpine.data('dragScroll', () => ({
        startX: 0,
        startLeft: 0,
        moving: false,
        dragged: false,
        index: 0,

        // The scrolling element may be a child when the component also owns
        // the dot row underneath the cards.
        row() {
            return this.$refs.row || this.$el;
        },

        begin(event) {
            if (event.pointerType !== 'mouse') {
                return;
            }

            this.moving = true;
            this.dragged = false;
            this.startX = event.clientX;
            this.startLeft = this.row().scrollLeft;
        },

        move(event) {
            if (! this.moving) {
                return;
            }

            const distance = event.clientX - this.startX;

            if (Math.abs(distance) > 4) {
                this.dragged = true;
            }

            this.row().scrollLeft = this.startLeft - distance;
        },

        end() {
            this.moving = false;
        },

        // One dot per screenful. The row scrolls freely, so the dots follow the
        // scroll position instead of owning it; the last dot lands at the end
        // where the scroll clamps.
        pages: 1,

        init() {
            this.measure = () => {
                const row = this.row();
                this.pages = Math.max(1, Math.ceil((row.scrollWidth - 1) / row.clientWidth));
                this.sync();
            };

            this.observer = new ResizeObserver(this.measure);
            this.observer.observe(this.row());
            this.$nextTick(() => this.measure());
        },

        destroy() {
            this.observer?.disconnect();
        },

        // The row can only scroll (scrollWidth - clientWidth), which is often
        // less than a full page, so dots spread over that distance.
        maxScroll() {
            const row = this.row();

            return Math.max(1, row.scrollWidth - row.clientWidth);
        },

        sync() {
            this.index = this.pages > 1
                ? Math.max(0, Math.min(this.pages - 1, Math.round(this.row().scrollLeft / this.maxScroll() * (this.pages - 1))))
                : 0;
        },

        go(index) {
            this.row().scrollTo({
                left: this.pages > 1 ? index * this.maxScroll() / (this.pages - 1) : 0,
                behavior: 'smooth',
            });
        },

        swallowClick(event) {
            if (! this.dragged) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            this.dragged = false;
        },
    }));

    /* Property photo viewer. The gallery on the page stays inline; this is the
       fullscreen layer, so it is the only place that reads the keyboard. */
    Alpine.data('photoGallery', (photos = []) => ({
        photos,
        active: 0,
        isOpen: false,

        open(index = 0) {
            this.active = Math.max(0, Math.min(index, this.photos.length - 1));
            this.isOpen = true;
            document.body.classList.add('overflow-y-hidden');
            this.$nextTick(() => this.$refs.close?.focus());
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-y-hidden');
            this.$refs.trigger?.focus();
        },

        next() {
            this.active = (this.active + 1) % this.photos.length;
        },

        prev() {
            this.active = (this.active - 1 + this.photos.length) % this.photos.length;
        },

        // Focus stays on the three overlay buttons while the dialog is open.
        trap(event) {
            const nodes = [...this.$refs.overlay.querySelectorAll('button')];

            if (nodes.length === 0) {
                return;
            }

            const first = nodes[0];
            const last = nodes[nodes.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    /* Admin listing form: the denah editor. Rooms sit on a fixed 12 x 16 grid so
       every plan keeps the sample's proportions. Dragging empty space draws a
       room, dragging its body moves it, and the corner handle resizes it.
       Arrow keys nudge and Shift + arrows resize, so a room can be placed
       without a pointer. */
    Alpine.data('floorPlan', (initial = [], floorCount = 1) => ({
        cols: 12,
        rows: 16,
        rooms: [],
        active: null,
        floor: 1,
        floors: Math.max(1, Number(floorCount) || 1),
        mode: '',
        anchor: null,
        base: null,
        history: [],
        nextId: 1,

        toneOptions: [
            { value: 'biasa', label: 'Biasa' },
            { value: 'utama', label: 'Ruang utama' },
            { value: 'luar', label: 'Luar (carport / taman)' },
        ],

        init() {
            this.rooms = (Array.isArray(initial) ? initial : []).map((room) => ({
                id: this.nextId++,
                floor: Number(room.floor) || 1,
                label: String(room.label ?? ''),
                x: Number(room.x) || 0,
                y: Number(room.y) || 0,
                w: Number(room.w) || 1,
                h: Number(room.h) || 1,
                tone: room.tone || 'biasa',
            }));

            this.floors = Math.max(this.floors, ...this.rooms.map((room) => room.floor), 1);
            this.floor = this.rooms.length ? this.rooms[0].floor : 1;
            this.active = this.rooms[0] ?? null;
        },

        get visible() {
            return this.rooms.filter((room) => room.floor === this.floor);
        },

        get payload() {
            return JSON.stringify(this.rooms.map(({ id, ...room }) => room));
        },

        roomStyle(room) {
            return `left:${room.x / this.cols * 100}%;top:${room.y / this.rows * 100}%;width:${room.w / this.cols * 100}%;height:${room.h / this.rows * 100}%`;
        },

        cellOf(event) {
            const box = this.$refs.canvas.getBoundingClientRect();
            const x = Math.floor((event.clientX - box.left) / (box.width / this.cols));
            const y = Math.floor((event.clientY - box.top) / (box.height / this.rows));

            return {
                x: Math.max(0, Math.min(this.cols - 1, x)),
                y: Math.max(0, Math.min(this.rows - 1, y)),
            };
        },

        snapshot() {
            this.history.push(JSON.stringify(this.rooms.map(({ id, ...room }) => room)));

            if (this.history.length > 50) {
                this.history.shift();
            }
        },

        // The form's dirty guard listens for input events, and a pointer drag
        // fires none, so the hidden field announces every change.
        sync() {
            this.$refs.field?.dispatchEvent(new Event('input', { bubbles: true }));
        },

        start(event) {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            this.snapshot();

            const cell = this.cellOf(event);
            const room = {
                id: this.nextId++,
                floor: this.floor,
                label: '',
                x: cell.x,
                y: cell.y,
                w: 1,
                h: 1,
                tone: 'biasa',
            };

            this.rooms.push(room);
            this.active = room;
            this.mode = 'draw';
            this.anchor = cell;
            this.$refs.canvas.focus();
            this.$refs.canvas.setPointerCapture?.(event.pointerId);
            this.sync();
        },

        startMove(event, room) {
            this.snapshot();
            this.active = room;
            this.mode = 'move';
            this.anchor = this.cellOf(event);
            this.base = { x: room.x, y: room.y };
            this.$refs.canvas.setPointerCapture?.(event.pointerId);
        },

        startResize(event, room) {
            this.snapshot();
            this.active = room;
            this.mode = 'resize';
            this.$refs.canvas.setPointerCapture?.(event.pointerId);
        },

        drag(event) {
            if (!this.mode || !this.active) {
                return;
            }

            const room = this.active;
            const cell = this.cellOf(event);

            if (this.mode === 'draw') {
                room.x = Math.min(this.anchor.x, cell.x);
                room.y = Math.min(this.anchor.y, cell.y);
                room.w = Math.abs(cell.x - this.anchor.x) + 1;
                room.h = Math.abs(cell.y - this.anchor.y) + 1;
            } else if (this.mode === 'move') {
                room.x = Math.max(0, Math.min(this.cols - room.w, this.base.x + cell.x - this.anchor.x));
                room.y = Math.max(0, Math.min(this.rows - room.h, this.base.y + cell.y - this.anchor.y));
            } else {
                room.w = Math.max(1, Math.min(this.cols - room.x, cell.x - room.x + 1));
                room.h = Math.max(1, Math.min(this.rows - room.y, cell.y - room.y + 1));
            }

            this.sync();
        },

        end() {
            if (!this.mode) {
                return;
            }

            this.mode = '';
            this.anchor = null;
            this.base = null;
            this.sync();
        },

        keys(event) {
            if (!this.active) {
                return;
            }

            const moves = {
                ArrowLeft: [-1, 0],
                ArrowRight: [1, 0],
                ArrowUp: [0, -1],
                ArrowDown: [0, 1],
            }[event.key];

            if (!moves) {
                return;
            }

            event.preventDefault();
            this.snapshot();

            const room = this.active;

            if (event.shiftKey) {
                room.w = Math.max(1, Math.min(this.cols - room.x, room.w + moves[0]));
                room.h = Math.max(1, Math.min(this.rows - room.y, room.h + moves[1]));
            } else {
                room.x = Math.max(0, Math.min(this.cols - room.w, room.x + moves[0]));
                room.y = Math.max(0, Math.min(this.rows - room.h, room.y + moves[1]));
            }

            this.sync();
        },

        addRoom() {
            this.snapshot();

            const spot = this.freeSpot();
            const room = {
                id: this.nextId++,
                floor: this.floor,
                label: '',
                x: spot.x,
                y: spot.y,
                w: 1,
                h: 1,
                tone: 'biasa',
            };

            this.rooms.push(room);
            this.active = room;
            this.sync();
        },

        // The first cell no room covers, so a keyboard-added room never lands on
        // top of another one and gets refused on save.
        freeSpot() {
            const taken = (x, y) => this.rooms.some((room) => room.floor === this.floor
                && x >= room.x && x < room.x + room.w
                && y >= room.y && y < room.y + room.h);

            for (let y = 0; y < this.rows; y++) {
                for (let x = 0; x < this.cols; x++) {
                    if (!taken(x, y)) {
                        return { x, y };
                    }
                }
            }

            return { x: 0, y: 0 };
        },

        remove() {
            if (!this.active) {
                return;
            }

            this.snapshot();
            this.rooms = this.rooms.filter((room) => room.id !== this.active.id);
            this.active = null;
            this.sync();
        },

        undo() {
            const last = this.history.pop();

            if (!last) {
                return;
            }

            this.rooms = JSON.parse(last).map((room) => ({ id: this.nextId++, ...room }));
            this.active = this.rooms.find((room) => room.floor === this.floor) ?? null;
            this.sync();
        },

        selectFloor(n) {
            this.floor = n;
            this.active = this.rooms.find((room) => room.floor === n) ?? null;
        },

        addFloor() {
            if (this.floors >= 99) {
                return;
            }

            this.floors += 1;
            this.selectFloor(this.floors);
        },
    }));
});

Alpine.start();

// Admin forms: the listing form is over 2,000px tall, and the links that leave
// it are one click away. In-app navigation gets the site's own dialog; a real
// unload (tab close, reload) still falls to the browser prompt, which cannot be
// styled or translated from here.
(() => {
    const forms = [...document.querySelectorAll('[data-dirty-guard]')];

    if (! forms.length) {
        return;
    }

    let pendingHref = null;
    let pendingForm = null;
    let pendingSubmitter = null;
    let bypass = false;

    const isDirty = () => forms.some((form) => form.dataset.dirty === '1');

    forms.forEach((form) => {
        form.addEventListener('input', () => { form.dataset.dirty = '1'; });
        form.addEventListener('change', () => { form.dataset.dirty = '1'; });
        form.addEventListener('submit', () => { form.dataset.dirty = '0'; bypass = true; });
    });

    // Capture, so a link's own handler never runs before the question is asked.
    document.addEventListener('click', (event) => {
        if (bypass || ! isDirty() || ! (event.target instanceof Element)) {
            return;
        }

        const link = event.target.closest('a[href]');

        if (! link || link.target === '_blank') {
            return;
        }

        const url = new URL(link.href, location.href);
        const samePage = url.pathname === location.pathname && url.search === location.search;

        // A jump inside the same page is not leaving it.
        if (samePage && url.hash !== '') {
            return;
        }

        event.preventDefault();
        pendingHref = link.href;
        pendingForm = null;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'unsaved-changes' }));
    }, true);

    // Logout and the photo-delete dialogs navigate by submitting a form that is
    // not the guarded one, so they need the same question before they go.
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (bypass || ! (form instanceof HTMLFormElement) || ! isDirty()) {
            return;
        }

        if (form.hasAttribute('data-dirty-guard')) {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        pendingSubmitter = event.submitter ?? null;
        pendingHref = null;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'unsaved-changes' }));
    }, true);

    window.addEventListener('beforeunload', (event) => {
        if (bypass || ! isDirty()) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    });

    window.addEventListener('leave-confirmed', () => {
        bypass = true;

        if (pendingForm) {
            const form = pendingForm;
            const submitter = pendingSubmitter;
            pendingForm = null;
            pendingSubmitter = null;

            // requestSubmit keeps browser validation and the button's own value.
            if (submitter) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }

            return;
        }

        if (pendingHref) {
            location.href = pendingHref;
        }
    });
})();
document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-focus]');

    if (!link) {
        return;
    }

    const field = document.querySelector(link.dataset.focus);

    if (!field) {
        return;
    }

    event.preventDefault();

    // The panel may be collapsed, and an anchor cannot scroll to a hidden field.
    field.closest('details')?.setAttribute('open', '');
    field.focus({ preventScroll: true });
    field.scrollIntoView({ block: 'center' });
});
