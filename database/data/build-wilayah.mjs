// Regenerates wilayah.csv from the wilayah.id API (Kemendagri codes). Run once,
// commit the CSV: `node database/data/build-wilayah.mjs`.
import { writeFile } from 'node:fs/promises';

const BASE = 'https://wilayah.id/api';
const CONCURRENCY = 16;

async function get(path) {
    const response = await fetch(`${BASE}/${path}`);

    if (!response.ok) {
        throw new Error(`${path}: HTTP ${response.status}`);
    }

    return (await response.json()).data;
}

async function pool(tasks) {
    const results = new Array(tasks.length);
    let next = 0;

    const worker = async () => {
        while (next < tasks.length) {
            const index = next++;
            results[index] = await tasks[index]();
        }
    };

    await Promise.all(Array.from({ length: CONCURRENCY }, worker));
    return results;
}

const provinces = await get('provinces.json');
const regencies = (await pool(provinces.map((province) => () => get(`regencies/${province.code}.json`)))).flat();
const districts = await pool(regencies.map((regency) => () => get(`districts/${regency.code}.json`)));

// A few names ship with stray whitespace ("Kota Administrasi Jakarta Utara ").
const clean = (name) => name.replace(/\s+/g, ' ').trim();

const rows = [];
regencies.forEach((regency, index) => {
    const province = provinces.find((item) => regency.code.startsWith(item.code));

    for (const district of districts[index]) {
        rows.push([district.code, clean(province.name), clean(regency.name), clean(district.name)]);
    }
});

rows.sort((a, b) => a[0].localeCompare(b[0]));

const csv = ['code,province,city,district', ...rows.map((row) => row.map((value) => `"${value}"`).join(','))].join('\n');

await writeFile(new URL('wilayah.csv', import.meta.url), `${csv}\n`);

console.log(`${rows.length} kecamatan from ${regencies.length} kota/kabupaten, ${provinces.length} provinsi`);
