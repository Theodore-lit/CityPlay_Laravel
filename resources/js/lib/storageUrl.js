/**
 * Convertit un chemin stocké en BDD en URL publique accessible.
 * Gère : chemins relatifs, préfixe /storage/, URLs externes.
 */
export function storageUrl(path) {
    if (!path || typeof path !== 'string') {
        return null;
    }

    const trimmed = path.trim();
    if (!trimmed) {
        return null;
    }

    if (/^https?:\/\//i.test(trimmed)) {
        return trimmed;
    }

    let normalized = trimmed.startsWith('/') ? trimmed.slice(1) : trimmed;

    if (normalized.startsWith('storage/')) {
        return `/${normalized}`;
    }

    return `/storage/${normalized}`;
}

export function storageUrls(paths) {
    if (!Array.isArray(paths)) {
        return [];
    }

    return paths.map(storageUrl).filter(Boolean);
}

export function firstStorageUrl(paths) {
    return storageUrls(paths)[0] ?? null;
}
