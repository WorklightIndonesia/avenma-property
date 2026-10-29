import './bootstrap';

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

    /* Public filter: server-rendered lists, limited to regions that have a
       published listing. Nothing is applied until Terapkan is pressed. */
    Alpine.data('catalogFilter', (config = {}) => ({
        ...PICKER,

        provinces: config.provinces ?? [],
        regionCities: config.cities ?? {},
        regionDistricts: config.districts ?? {},
        selProv: config.province || null,
        qProv: config.province || '',
        selCity: config.city || null,
        qCity: config.city || '',
        selDist: config.district || null,
        qDistrict: config.district || '',

        init() {
            this.initRegions();
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
});

Alpine.start();

// Filter chips point at their field so a click lands on the input that owns the value.
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
