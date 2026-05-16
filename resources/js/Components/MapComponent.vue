<script setup>
import { onMounted, onUnmounted, ref, watch, defineExpose } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css'; // Assure-toi que le CSS est importé

const props = defineProps({
    locations: Array,
    userPosition: Object,
});

const mapContainer = ref(null);
let map = null;
let userMarker = null;
const markers = [];

/**
 * Initialise la carte Leaflet
 */
const initMap = () => {
    if (!mapContainer.value) return;

    const center = props.locations.length > 0
        ? [props.locations[0].latitude, props.locations[0].longitude]
        : [6.366667, 2.433333];

    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView(center, 15);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
    }).addTo(map);

    props.locations.forEach(loc => {
        const marker = L.circle([loc.latitude, loc.longitude], {
            color: '#2563eb',
            fillColor: '#2563eb',
            fillOpacity: 0.2,
            radius: loc.radius_meters || 50
        }).addTo(map);

        marker.bindPopup(`<b style="color: #2563eb">${loc.name}</b><br><span style="font-size: 10px; color: #666">${loc.category}</span>`);
        markers.push({ id: loc.id, marker });
    });
};

/**
 * Force Leaflet à recalculer la taille du conteneur (Correctif tuiles manquantes)
 */
const refreshSize = () => {
    if (map) {
        setTimeout(() => {
            map.invalidateSize();
        }, 200); // Petit délai pour laisser le layout se stabiliser
    }
};

const updateUserMarker = (pos) => {
    if (!map) return;
    const latlng = [pos.lat, pos.lng];

    if (!userMarker) {
        const userIcon = L.divIcon({
            className: 'user-position-icon',
            html: `
                <div class="relative">
                    <div class="absolute -inset-2 bg-gaming-blue rounded-full animate-ping opacity-75"></div>
                    <div class="relative w-4 h-4 bg-white border-2 border-gaming-blue rounded-full shadow-lg"></div>
                </div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        userMarker = L.marker(latlng, { icon: userIcon }).addTo(map);
    } else {
        userMarker.setLatLng(latlng);
    }
};

watch(() => props.userPosition, (newPos) => {
    if (newPos) updateUserMarker(newPos);
}, { deep: true });

onMounted(() => {
    initMap();
    if (props.userPosition) updateUserMarker(props.userPosition);
});

onUnmounted(() => {
    if (map) map.remove();
});

// On expose la fonction pour le parent
defineExpose({ refreshSize });
</script>

<template>
    <div ref="mapContainer" class="w-full h-full min-h-full bg-gaming-dark"></div>
</template>

<style>
.user-position-icon {
    background: transparent !important;
    border: none !important;
}
/* Assure que Leaflet prend toute la place */
.leaflet-container {
    height: 100% !important;
    width: 100% !important;
    background: #0f172a !important;
}
</style>
