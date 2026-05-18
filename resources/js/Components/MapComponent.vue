<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';

/**
 * Composant MapComponent : Gère l'affichage de la carte Leaflet, 
 * les marqueurs de lieux et la position de l'utilisateur.
 */
const props = defineProps({
    locations: Array,     // Liste des lieux à afficher (latitude, longitude, radius)
    userPosition: Object, // Position GPS actuelle de l'utilisateur {lat, lng}
});

const emit = defineEmits(['locationReached']);

const mapContainer = ref(null);
let map = null;
let userMarker = null;
const markers = [];
let resizeObserver = null;

/**
 * Initialise la carte Leaflet avec un fond sombre et les marqueurs de lieux.
 */
const initMap = () => {
    if (!mapContainer.value) return;

    // Définit le centre initial (premier lieu de la liste ou coordonnées par défaut de Cotonou)
    const center = props.locations && props.locations.length > 0 
        ? [props.locations[0].latitude, props.locations[0].longitude] 
        : [6.366667, 2.433333];

    // Création de l'instance de la carte
    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView(center, 15);

    // Ajout du fond de carte sombre (CartoDB Dark Matter)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        noWrap: true,
        bounds: [[-90, -180], [90, 180]]
    }).addTo(map);

    // Forcer le chargement immédiat des tuiles
    map.whenReady(() => {
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    });

    // Ajout des lieux sous forme de cercles (zones de validation)
    if (props.locations) {
        props.locations.forEach(loc => {
            const marker = L.circle([loc.latitude, loc.longitude], {
                color: '#2563eb',       // Bleu gaming
                fillColor: '#2563eb',
                fillOpacity: 0.2,
                radius: loc.radius_meters || 50
            }).addTo(map);

            // Popup informative au clic
            marker.bindPopup(`<b class="text-gaming-blue-light">${loc.name}</b><br><span class="text-xs text-gray-400">${loc.category}</span>`);
            markers.push({ id: loc.id, marker });
        });
    }

    // --- SOLUTION POUR LES TUILES MANQUANTES ---
    // Un ResizeObserver écoute les changements de taille réels du conteneur HTML
    // et force Leaflet à recalculer les tuiles manquantes immédiatement.
    if (window.ResizeObserver) {
        resizeObserver = new ResizeObserver(() => {
            if (map) {
                // Utilisation d'un court délai pour s'assurer que le redimensionnement est fini
                requestAnimationFrame(() => {
                    map.invalidateSize({ animate: true });
                });
            }
        });
        resizeObserver.observe(mapContainer.value);
    }
};

/**
 * Met à jour le marqueur de position de l'utilisateur sur la carte.
 * @param {Object} pos - Nouvelles coordonnées {lat, lng}
 */
const updateUserMarker = (pos) => {
    if (!map || !pos) return;

    const latlng = [pos.lat, pos.lng];

    if (!userMarker) {
        // Création d'un marqueur personnalisé avec animation CSS
        const userIcon = L.divIcon({
            className: 'user-position-icon',
            html: `
                <div class="relative">
                    <div class="absolute -inset-2 bg-gaming-blue rounded-full animate-ping opacity-75"></div>
                    <div class="relative w-4 h-4 bg-white border-2 border-gaming-blue rounded-full shadow-gaming"></div>
                </div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        userMarker = L.marker(latlng, { icon: userIcon }).addTo(map);
        
        // Optionnel : Recentrer la carte sur l'utilisateur lors de sa première détection
        map.setView(latlng, map.getZoom());
    } else {
        // Simple mise à jour de la position si le marqueur existe déjà
        userMarker.setLatLng(latlng);
    }
};

/**
 * Permet de forcer manuellement le rafraîchissement depuis le parent si nécessaire
 */
const forceRefreshSize = () => {
    if (map) {
        map.invalidateSize();
    }
};

// Exposer la méthode au composant parent
defineExpose({ refreshSize: forceRefreshSize });

/**
 * Surveille les changements de position de l'utilisateur transmis par le parent.
 */
watch(() => props.userPosition, (newPos) => {
    if (newPos) {
        updateUserMarker(newPos);
    }
}, { deep: true });

onMounted(() => {
    // Petit délai pour s'assurer que le DOM et les animations de layout sont stables
    setTimeout(() => {
        initMap();
        if (props.userPosition) {
            updateUserMarker(props.userPosition);
        }
        // Force un second calcul après un court instant pour les cas complexes
        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 300);
    }, 100);
});

onUnmounted(() => {
    if (resizeObserver && mapContainer.value) {
        resizeObserver.unobserve(mapContainer.value);
    }
    if (map) {
        map.remove(); // Nettoyage de la mémoire
    }
});
</script>

<template>
    <!-- Conteneur HTML pour la carte -->
    <div ref="mapContainer" class="w-full h-full z-0"></div>
</template>

<style>
/* Styles spécifiques pour l'icône de position utilisateur */
.user-position-icon {
    background: transparent !important;
    border: none !important;
}
/* Sécurité additionnelle pour s'assurer que Leaflet s'adapte au conteneur CSS Tailwind */
.leaflet-container {
    width: 100% !important;
    height: 100% !important;
}
</style>