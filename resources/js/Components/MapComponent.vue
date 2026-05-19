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
    targetLocation: Object, // Lieu cible actuel
    teamMembers: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['locationReached']);

const mapContainer = ref(null);
let map = null;
let userMarker = null;
let targetMarker = null;
let pathLine = null;
let radarCircle = null;
const teamMarkers = {}; // Stockage des marqueurs par ID utilisateur
const markers = [];
let resizeObserver = null;

/**
 * Initialise la carte Leaflet avec un fond abstrait futuriste.
 */
const initMap = () => {
    if (!mapContainer.value) return;

    // Définit le centre initial
    const center = props.targetLocation
        ? [props.targetLocation.latitude, props.targetLocation.longitude]
        : [6.366667, 2.433333];

    // Création de l'instance de la carte sans fond de carte
    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false,
        dragging: true,
        scrollWheelZoom: true,
        doubleClickZoom: true,
        boxZoom: false,
        touchZoom: true
    }).setView(center, 16);

    // Pas de L.tileLayer pour garder le fond noir/abstrait

    // Ajout d'un cercle de radar fixe au centre de la vue (décoratif)
    radarCircle = L.circle(center, {
        color: 'rgba(34, 211, 238, 0.2)',
        fillColor: 'transparent',
        weight: 1,
        radius: 500,
        interactive: false
    }).addTo(map);

    // Forcer le rafraîchissement
    map.whenReady(() => {
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    });

    if (window.ResizeObserver) {
        resizeObserver = new ResizeObserver(() => {
            if (map) {
                requestAnimationFrame(() => {
                    map.invalidateSize({ animate: true });
                });
            }
        });
        resizeObserver.observe(mapContainer.value);
    }

    updateTargetMarker();
};

/**
 * Met à jour ou crée le marqueur de la cible (lieu à découvrir)
 */
const updateTargetMarker = () => {
    if (!map || !props.targetLocation) return;

    const latlng = [props.targetLocation.latitude, props.targetLocation.longitude];

    if (targetMarker) {
        targetMarker.setLatLng(latlng);
    } else {
        const targetIcon = L.divIcon({
            className: 'target-marker-icon',
            html: `
                <div class="relative">
                    <div class="absolute -inset-4 border-2 border-electric rounded-full animate-ping opacity-50"></div>
                    <div class="absolute -inset-2 border border-electric rounded-full animate-spin-slow opacity-30"></div>
                    <div class="relative h-6 w-6 bg-gaming-dark border-2 border-electric rotate-45 flex items-center justify-center shadow-neon">
                        <div class="h-1 w-1 bg-electric rounded-full"></div>
                    </div>
                </div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        targetMarker = L.marker(latlng, { icon: targetIcon }).addTo(map);
    }

    updatePathLine();
};

/**
 * Dessine une ligne entre l'utilisateur et la cible
 */
const updatePathLine = () => {
    if (!map || !props.userPosition || !props.targetLocation) return;

    const userLatLng = [props.userPosition.lat, props.userPosition.lng];
    const targetLatLng = [props.targetLocation.latitude, props.targetLocation.longitude];

    if (pathLine) {
        pathLine.setLatLngs([userLatLng, targetLatLng]);
    } else {
        pathLine = L.polyline([userLatLng, targetLatLng], {
            color: '#22d3ee',
            weight: 2,
            dashArray: '5, 10',
            opacity: 0.5,
            className: 'radar-path-line'
        }).addTo(map);
    }
};

/**
 * Met à jour le marqueur de position de l'utilisateur sur la carte.
 */
const updateUserMarker = (pos) => {
    if (!map || !pos) return;

    const latlng = [pos.lat, pos.lng];

    if (!userMarker) {
        const userIcon = L.divIcon({
            className: 'user-position-icon',
            html: `
                <div class="relative">
                    <div class="absolute -inset-8 bg-electric/10 rounded-full animate-pulse"></div>
                    <div class="absolute -inset-4 bg-electric/20 rounded-full animate-ping"></div>
                    <div class="relative w-4 h-4 bg-white border-2 border-electric rounded-full shadow-neon">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-electric rounded-full"></div>
                    </div>
                </div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        userMarker = L.marker(latlng, { icon: userIcon }).addTo(map);
    } else {
        userMarker.setLatLng(latlng);
    }

    updatePathLine();
};

/**
 * Met à jour les marqueurs des membres de l'équipe
 */
const updateTeamMarkers = () => {
    if (!map) return;

    // Supprimer les marqueurs des membres qui ne sont plus dans la liste
    const currentMemberIds = props.teamMembers.map(m => m.id);
    Object.keys(teamMarkers).forEach(id => {
        if (!currentMemberIds.includes(parseInt(id))) {
            teamMarkers[id].remove();
            delete teamMarkers[id];
        }
    });

    // Ajouter ou mettre à jour les marqueurs
    props.teamMembers.forEach(member => {
        const latlng = [member.latitude, member.longitude];
        
        if (teamMarkers[member.id]) {
            teamMarkers[member.id].setLatLng(latlng);
        } else {
            const memberIcon = L.divIcon({
                className: 'team-member-icon',
                html: `
                    <div class="relative group">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gaming-darker border border-primary/30 px-2 py-1 rounded text-[8px] whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            ${member.name}
                        </div>
                        <div class="absolute -inset-2 bg-primary/20 rounded-full animate-pulse"></div>
                        <div class="relative h-5 w-5 rounded-full border-2 border-primary overflow-hidden bg-gaming-darker">
                            ${member.avatar ? `<img src="${member.avatar}" class="w-full h-full object-cover" />` : `<div class="w-full h-full flex items-center justify-center text-[10px] font-black">${member.name.charAt(0)}</div>`}
                        </div>
                    </div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            teamMarkers[member.id] = L.marker(latlng, { icon: memberIcon }).addTo(map);
        }
    });
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

watch(() => props.userPosition, (newPos) => {
    if (newPos) {
        updateUserMarker(newPos);
    }
}, { deep: true });

watch(() => props.targetLocation, () => {
    updateTargetMarker();
}, { deep: true });

watch(() => props.teamMembers, () => {
    updateTeamMarkers();
}, { deep: true });

onMounted(() => {
    // Petit délai pour s'assurer que le DOM et les animations de layout sont stables
    setTimeout(() => {
        initMap();
        if (props.userPosition) {
            updateUserMarker(props.userPosition);
        }
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
    <!-- Conteneur HTML pour le radar -->
    <div class="relative w-full h-full bg-gaming-dark overflow-hidden group">
        <!-- Fond de radar abstrait -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute inset-0 grid-bg opacity-20"></div>
            <!-- Cercles concentriques de radar -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80%] aspect-square border border-electric/10 rounded-full"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[60%] aspect-square border border-electric/10 rounded-full"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] aspect-square border border-electric/10 rounded-full"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[20%] aspect-square border border-electric/10 rounded-full"></div>

            <!-- Scanner radar -->
            <div class="absolute top-1/2 left-1/2 w-full h-full -translate-x-1/2 -translate-y-1/2 origin-center animate-radar-scan">
                <div class="absolute top-0 left-1/2 w-1/2 h-1/2 bg-gradient-to-tr from-electric/20 to-transparent origin-bottom-left"></div>
            </div>
        </div>

        <div ref="mapContainer" class="w-full h-full z-10 bg-transparent"></div>

        <!-- Overlay de bruit/grain (Local Data URI pour éviter les 404) -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.03] mix-blend-overlay bg-[url('data:image/svg+xml,%3Csvg viewBox=\'0 0 200 200\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cfilter id=\'noiseFilter\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.65\' numOctaves=\'3\' stitchTiles=\'stitch\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23noiseFilter)\'/%3E%3C/svg%3E')]"></div>
    </div>
</template>

<style>
/* Styles spécifiques pour l'icône de position utilisateur */
.user-position-icon, .target-marker-icon {
    background: transparent !important;
    border: none !important;
}

.animate-spin-slow {
    animation: spin 8s linear infinite;
}

.animate-radar-scan {
    animation: radar-scan 4s linear infinite;
}

@keyframes radar-scan {
    from { transform: translate(-50%, -50%) rotate(0deg); }
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Masquer les éléments Leaflet par défaut */
.leaflet-container {
    background: transparent !important;
}

.radar-path-line {
    filter: drop-shadow(0 0 5px #22d3ee);
    stroke-dasharray: 10;
    animation: dash 20s linear infinite;
}

@keyframes dash {
    to { stroke-dashoffset: -1000; }
}
</style>
