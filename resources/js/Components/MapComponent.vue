<script setup>
import { onMounted, onUnmounted, ref, watch, defineExpose } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css'; // Assure-toi que le CSS est importé

/**
 * Composant MapComponent : Gère l'affichage de la carte Leaflet,
 * les marqueurs de lieux et la position de l'utilisateur.
 */
const props = defineProps({
    locations: Array,     // Liste des lieux à afficher (latitude, longitude, radius)
    userPosition: Object, // Position GPS actuelle de l'utilisateur {lat, lng}
    targetLocation: Object, // Lieu cible actuel
    teamMembers: Array,   // Liste des membres de l'équipe avec leur position {id, name, lat, lng}
});

const mapContainer = ref(null);
let map = null;
let userMarker = null;
let startMarker = null;
const teamMarkers = {}; // Stockage des marqueurs par ID de membre
let targetMarker = null;
let pathLine = null;
let radarCircle = null;
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

    // Création de l'instance de la carte sans fond de carte et sans zoom/drag
    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false,
        dragging: false, // Empêche le déplacement
        scrollWheelZoom: false, // Empêche le zoom à la molette
        doubleClickZoom: false, // Empêche le double clic
        boxZoom: false,
        touchZoom: false, // Empêche le zoom tactile
        keyboard: false // Désactive le clavier
    }).setView(center, 16);

    // Pas de L.tileLayer pour garder le fond noir/abstrait

    // Ajout des cercles de radar concentriques
    const radarOptions = {
        color: 'rgba(0, 112, 255, 0.3)',
        fillColor: 'transparent',
        weight: 1,
        interactive: false
    };

    [100, 250, 500, 1000].forEach(radius => {
        L.circle(center, { ...radarOptions, radius }).addTo(map);
    });

    // Ajout d'une ligne de scan tournante
    const scanIcon = L.divIcon({
        className: 'radar-scan-line',
        html: '<div class="absolute top-0 left-1/2 w-0.5 h-[1000px] bg-electric/40 origin-bottom"></div>',
        iconSize: [0, 0]
    });
    L.marker(center, { icon: scanIcon, interactive: false }).addTo(map);

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
                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-destructive border border-white/30 rounded text-[8px] font-black text-white uppercase whitespace-nowrap shadow-neon-error">
                        Cible
                    </div>
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
 * Dessine une ligne entre le départ, l'utilisateur et la cible
 */
const updatePathLine = () => {
    if (!map || !props.userPosition || !props.targetLocation) return;

    const startLatLng = startMarker ? startMarker.getLatLng() : [props.userPosition.lat, props.userPosition.lng];
    const userLatLng = [props.userPosition.lat, props.userPosition.lng];
    const targetLatLng = [props.targetLocation.latitude, props.targetLocation.longitude];

    if (pathLine) {
        pathLine.setLatLngs([startLatLng, userLatLng, targetLatLng]);
    } else {
        pathLine = L.polyline([startLatLng, userLatLng, targetLatLng], {
            color: '#0070ff',
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
const refreshSize = () => {
    if (map) {
        setTimeout(() => {
            map.invalidateSize();
        }, 200); // Petit délai pour laisser le layout se stabiliser
    }
};

const updateUserMarker = (pos) => {
    if (!map || !pos) return;

    const latlng = [pos.lat, pos.lng];

    // Enregistre le point de départ lors de la première réception de position
    if (!startMarker) {
        const startIcon = L.divIcon({
            className: 'start-position-icon',
            html: `
                <div class="relative">
                    <div class="h-3 w-3 bg-white/20 border border-white/40 rounded-full"></div>
                    <div class="absolute top-5 left-1/2 -translate-x-1/2 text-[6px] text-white/40 uppercase font-black whitespace-nowrap">Point d'entrée</div>
                </div>`,
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });
        startMarker = L.marker(latlng, { icon: startIcon, opacity: 0.6 }).addTo(map);
    }

    if (!userMarker) {
        const userIcon = L.divIcon({
            className: 'user-position-icon',
            html: `
                <div class="relative">
                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-electric border border-white/30 rounded text-[8px] font-black text-white uppercase whitespace-nowrap shadow-neon">
                        Vous
                    </div>
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
    if (!map || !props.teamMembers) return;

    props.teamMembers.forEach(member => {
        if (!member.lat || !member.lng) return;

        const latlng = [member.lat, member.lng];

        if (teamMarkers[member.id]) {
            teamMarkers[member.id].setLatLng(latlng);
        } else {
            const memberIcon = L.divIcon({
                className: 'team-member-icon',
                html: `
                    <div class="relative group">
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gaming-dark border border-electric/30 rounded text-[8px] text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">
                            ${member.name}
                        </div>
                        <div class="absolute -inset-2 bg-electric/20 rounded-full animate-pulse"></div>
                        <div class="relative w-3 h-3 bg-electric border border-white rounded-full shadow-neon"></div>
                    </div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });
            teamMarkers[member.id] = L.marker(latlng, { icon: memberIcon }).addTo(map);
        }
    });

    // Optionnel : Supprimer les marqueurs des membres qui ne sont plus dans la liste
    const currentMemberIds = props.teamMembers.map(m => m.id);
    Object.keys(teamMarkers).forEach(id => {
        if (!currentMemberIds.includes(parseInt(id))) {
            teamMarkers[id].remove();
            delete teamMarkers[id];
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

watch(() => props.teamMembers, () => {
    updateTeamMarkers();
}, { deep: true });

watch(() => props.userPosition, (newPos) => {
    if (newPos) updateUserMarker(newPos);
}, { deep: true });

watch(() => props.targetLocation, () => {
    updateTargetMarker();
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

// // On expose la fonction pour le parent
// defineExpose({ refreshSize });
</script>

<style>
.radar-scan-line {
    animation: rotate-scan 4s linear infinite;
    z-index: 1000;
}

@keyframes rotate-scan {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.target-marker-icon, .user-position-icon, .team-member-icon, .start-position-icon {
     border: none !important;
     background: none !important;
 }

.radar-path-line {
    stroke-dasharray: 8;
    animation: dash-move 20s linear infinite;
}

@keyframes dash-move {
    to { stroke-dashoffset: -1000; }
}

/* Background for the map container to look like a HUD */
.leaflet-container {
    background: oklch(0.96 0.01 240) !important;
}
</style>

<template>
    <div ref="mapContainer" class="w-full h-full relative overflow-hidden">
        <!-- Grille de fond optionnelle pour le look HUD -->
        <div class="absolute inset-0 grid-radar opacity-30 pointer-events-none z-10"></div>
    </div>
</template>

<style scoped>
.grid-radar {
    background-image:
        linear-gradient(rgba(0, 112, 255, 0.2) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 112, 255, 0.2) 1px, transparent 1px);
    background-size: 40px 40px;
}
/* Assure que Leaflet prend toute la place */
.leaflet-container {
    height: 100% !important;
    width: 100% !important;
    background: #0f172a !important;
}
</style>
