<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';

/**
 * Composant MapComponent : Gère l'affichage de la carte Leaflet, 
 * les marqueurs de lieux et la position de l'utilisateur.
 */
const props = defineProps({
    locations: Array,    // Liste des lieux à afficher (latitude, longitude, radius)
    userPosition: Object, // Position GPS actuelle de l'utilisateur {lat, lng}
});

const emit = defineEmits(['locationReached']);

const mapContainer = ref(null);
let map = null;
let userMarker = null;
const markers = [];

/**
 * Initialise la carte Leaflet avec un fond sombre et les marqueurs de lieux.
 */
const initMap = () => {
    if (!mapContainer.value) return;

    // Définit le centre initial (premier lieu de la liste ou coordonnées par défaut de Cotonou)
    const center = props.locations.length > 0 
        ? [props.locations[0].latitude, props.locations[0].longitude] 
        : [6.366667, 2.433333];

    // Création de l'instance de la carte
    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView(center, 15);

    // Ajout du fond de carte clair (CartoDB Voyager)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Ajout des lieux sous forme de cercles (zones de validation)
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
};

/**
 * Met à jour le marqueur de position de l'utilisateur sur la carte.
 * @param {Object} pos - Nouvelles coordonnées {lat, lng}
 */
const updateUserMarker = (pos) => {
    if (!map) return;

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
    } else {
        // Simple mise à jour de la position si le marqueur existe déjà
        userMarker.setLatLng(latlng);
    }
};

/**
 * Surveille les changements de position de l'utilisateur transmis par le parent.
 */
watch(() => props.userPosition, (newPos) => {
    if (newPos) {
        updateUserMarker(newPos);
    }
}, { deep: true });

onMounted(() => {
    initMap();
    if (props.userPosition) {
        updateUserMarker(props.userPosition);
    }
});

onUnmounted(() => {
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
</style>
