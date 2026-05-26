<script setup>
// kamal
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import AppImage from '@/Components/AppImage.vue';
import { firstStorageUrl } from '@/lib/storageUrl';
import GlowInput from '@/Components/GlowInput.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Plus, ChevronLeft, Trash2, Image as ImageIcon,
    Save, Clock, MapPin, X, Edit2, Ticket, Gem, Gift, Eye, EyeOff, Trophy
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    city: Object,
});

const showEventModal = ref(false);
const showDeleteConfirm = ref(false);
const eventToDelete = ref(null);
const imagePreviews = ref([]);

/**
 * Types de récompenses disponibles pour les événements.
 * Ces lots sont échangeables par les joueurs via leurs diamants.
 */
const rewardTypes = [
    { value: 'ticket', label: 'Ticket Entrée' },
    { value: 'meal', label: 'Repas / Snack' },
    { value: 'discount', label: 'Réduction Boutique' },
];

/**
 * Formulaire réactif pour la création et l'édition d'événements.
 * Utilise useForm d'Inertia pour gérer les états de chargement et les erreurs.
 */
const eventForm = useForm({
    id: null,
    title: '',
    description: '',
    event_date: '',
    location_name: '',
    diamond_price: 0,
    has_vip_pass: false,
    reward_type: '',
    is_active: true,
    images: [],
    existing_images: [],
});

/**
 * Ouvre le modal de configuration d'événement.
 * Initialise les données en cas d'édition d'un événement existant.
 */
const openEventModal = (event = null) => {
    if (event) {
        eventForm.id = event.id;
        eventForm.title = event.title;
        eventForm.description = event.description;
        const date = new Date(event.event_date);
        eventForm.event_date = date.toISOString().slice(0, 16);
        eventForm.location_name = event.location_name;
        eventForm.diamond_price = event.diamond_price || 0;
        eventForm.has_vip_pass = !!event.has_vip_pass;
        eventForm.reward_type = event.reward_type || '';
        eventForm.is_active = !!event.is_active;
        eventForm.existing_images = event.images || [];
        eventForm.images = [];
    } else {
        eventForm.reset();
        eventForm.id = null;
    }
    imagePreviews.value = [];
    showEventModal.value = true;
};

/**
 * Gère le téléchargement d'images et génère des prévisualisations locales (blobs).
 */
const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    eventForm.images = files;
    imagePreviews.value = files.map(file => URL.createObjectURL(file));
};

/**
 * Supprime une image déjà présente sur le serveur lors de l'édition.
 */
const removeExistingImage = (index) => {
    eventForm.existing_images.splice(index, 1);
};

/**
 * Soumet le formulaire au serveur.
 * Utilise la méthode POST avec un override _method: PUT pour l'édition (support multipart/form-data).
 */
const submitEvent = () => {
    const url = eventForm.id
        ? route('mairie.events.edit', eventForm.id)
        : route('mairie.events.store', props.city.id);

    eventForm.transform((data) => ({
        ...data,
        _method: eventForm.id ? 'PUT' : 'POST',
    })).post(url, {
        forceFormData: true,
        onSuccess: () => {
            showEventModal.value = false;
            eventForm.reset();
        },
    });
};

/**
 * Supprime définitivement un événement après confirmation.
 */
const deleteEvent = (event) => {
    eventToDelete.value = event;
    showDeleteConfirm.value = true;
};

const executeDelete = () => {
    if (eventToDelete.value) {
        eventForm.delete(route('mairie.events.delete', eventToDelete.value.id), {
            onSuccess: () => {
                showDeleteConfirm.value = false;
                eventToDelete.value = null;
            }
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Événements - ${city.name}`" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div class="flex items-center gap-6">
                    <Link :href="route('mairie.city.hub', city.id)" class="h-12 w-12 rounded-2xl glass grid place-items-center text-primary hover:scale-110 transition-all border border-primary/20">
                        <ChevronLeft class="h-6 w-6" />
                    </Link>
                    <div>
                        <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">District Management</div>
                        <h1 class="font-display text-4xl md:text-5xl text-foreground uppercase italic font-black">Events Portal</h1>
                    </div>
                </div>
                <NeonButton @click="openEventModal()" variant="primary" size="sm" class="hover-game shadow-neon">
                    <Plus class="h-4 w-4 mr-2" />New Event
                </NeonButton>
            </div>

            <div v-if="city.events.length > 0" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="event in city.events" :key="event.id"
                     class="group relative overflow-hidden rounded-[2.5rem] glass-strong border-border hover:border-primary/40 transition-all duration-500"
                     :class="{ 'opacity-50 grayscale': !event.is_active }">

                    <div class="aspect-video overflow-hidden relative">
                        <AppImage
                            :src="firstStorageUrl(event.image_urls) || firstStorageUrl(event.images)"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        <div class="absolute top-4 left-4 flex flex-col gap-2">
                            <div v-if="event.has_vip_pass" class="px-3 py-1 rounded-lg bg-primary text-primary-foreground text-[8px] font-black uppercase tracking-widest flex items-center gap-1.5 shadow-neon-sm">
                                <Ticket class="h-3 w-3" /> VIP ACCESS
                            </div>
                            <div class="px-3 py-1 rounded-lg glass text-[8px] font-black uppercase tracking-widest flex items-center gap-1.5 border-white/10"
                                 :class="event.is_active ? 'text-emerald-400' : 'text-foreground/40'">
                                <component :is="event.is_active ? Eye : EyeOff" class="h-3 w-3" />
                                {{ event.is_active ? 'Online' : 'Draft' }}
                            </div>
                        </div>

                        <div class="absolute top-4 right-4 flex gap-2">
                            <button @click="openEventModal(event)" class="h-9 w-9 rounded-xl glass border-white/10 flex items-center justify-center text-foreground hover:text-primary transition-all">
                                <Edit2 class="h-4 w-4" />
                            </button>
                            <button @click="deleteEvent(event)" class="h-9 w-9 rounded-xl glass border-white/10 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                <Trash2 class="h-4 w-4" />
                            </button>
                            <Link :href="route('mairie.events.competitions', event.id)" class="h-9 w-9 rounded-xl glass border-white/10 flex items-center justify-center text-amber-400 hover:text-amber-300 transition-all" title="Manage Competitions">
                                <Trophy class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                        <Link :href="route('mairie.events.show', { city: city.id, event: event.id })" class="group/title">

                    <div class="p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-primary text-[10px] font-black uppercase tracking-widest">
                                <Clock class="h-3.5 w-3.5" /> {{ formatDate(event.event_date) }}
                            </div>
                            <div v-if="event.diamond_price > 0" class="flex items-center gap-1 text-sky-400 font-black text-xs">
                                <Gem class="h-3.5 w-3.5" /> {{ event.diamond_price }}
                            </div>
                        </div>

                            <h3 class="font-display text-xl text-foreground uppercase italic font-black mb-3 line-clamp-1 tracking-tight group-hover/title:text-primary transition-colors italic">
                                {{ event.title }}
                            </h3>


                        <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed mb-6">{{ event.description }}</p>

                        <div class="flex items-center justify-between pt-4 border-t border-white/5">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2 text-[10px] font-black text-foreground/40 uppercase">
                                    <MapPin class="h-3.5 w-3.5 text-primary" /> {{ event.location_name || 'Downtown' }}
                                </div>
                                <div v-if="event.reward_type" class="flex items-center gap-1 text-[9px] text-emerald-400 font-bold uppercase tracking-tighter">
                                    <Gift class="h-3 w-3" /> Reward Active
                                </div>
                            </div>

                            <Link :href="route('mairie.events.show', { city: city.id, event: event.id })">
                                <button class="group/btn relative px-4 py-2 overflow-hidden rounded-lg glass border border-primary/20 hover:border-primary transition-all">
                                    <div class="absolute inset-0 bg-primary/10 translate-y-full group-hover/btn:translate-y-0 transition-transform"></div>
                                    <div class="relative flex items-center gap-2 text-[10px] font-black text-primary uppercase tracking-widest">
                                        <!-- <span>Intel</span> -->
                                        <Eye class="h-4.5 w-4.5" />
                                    </div>
                                </button>
                            </Link>
                        </div>
                    </div>
                    </Link>
                </div>
            </div>

            <div v-else class="text-center py-24 glass-strong rounded-[3rem] border border-white/5">
                <ImageIcon class="h-16 w-16 text-foreground/5 mx-auto mb-6" />
                <h3 class="font-display text-2xl text-foreground uppercase italic font-black opacity-40">No Missions Logged</h3>
                <NeonButton @click="openEventModal()" variant="primary" class="mt-8 shadow-neon">INITIATE EVENT</NeonButton>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <ConfirmModal 
            :show="showDeleteConfirm"
            title="Supprimer l'événement ?"
            message="Êtes-vous sûr de vouloir supprimer cet événement ? Toutes les compétitions liées seront également supprimées."
            variant="danger"
            confirmText="Supprimer"
            @close="showDeleteConfirm = false"
            @confirm="executeDelete"
        />

        <!-- MODAL EDITOR (Gaming Style) -->
        <div v-if="showEventModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gaming-dark/60 backdrop-blur-md" @click="showEventModal = false"></div>

            <div class="relative w-full max-w-5xl bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[3rem] shadow-[0_8px_32px_0_rgba(0,0,0,0.5)] overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

                <div class="p-8 md:p-12 relative z-10">
                    <div class="flex justify-between items-center mb-12 px-4">
                        <div>
                            <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">Mission Protocol</div>
                            <h2 class="font-display text-3xl md:text-4xl text-foreground uppercase italic font-black tracking-tighter">
                                <span class="text-primary mr-2">//</span>Event Editor
                            </h2>
                        </div>
                        <button @click="showEventModal = false" class="group h-12 w-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center transition-all hover:border-primary/50">
                            <X class="h-6 w-6 text-foreground/40 group-hover:text-primary transition-colors" />
                        </button>
                    </div>

                    <form @submit.prevent="submitEvent" class="space-y-8">
                        <div class="grid lg:grid-cols-3 gap-8">
                            <!-- Left Column: Core Data -->
                            <div class="lg:col-span-2 space-y-8">
                                <div class="bg-white/5 border border-white/5 p-8 rounded-[2.5rem] hover:border-primary/20 transition-all duration-500">
                                    <label class="text-[10px] text-primary font-black uppercase tracking-[0.2em] mb-6 block">Primary Information</label>
                                    <div class="space-y-6">
                                        <GlowInput v-model="eventForm.title" placeholder="Mission Title..." required />

                                        <div class="grid md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-muted-foreground uppercase font-black ml-2 tracking-widest">Operation Timestamp</span>
                                                <input type="datetime-local" v-model="eventForm.event_date" class="w-full h-12 bg-white/5 border border-white/10 rounded-2xl px-5 text-xs text-foreground focus:border-primary outline-none transition-all" />
                                            </div>
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-muted-foreground uppercase font-black ml-2 tracking-widest">Vector / Location</span>
                                                <GlowInput v-model="eventForm.location_name" placeholder="Coordinates or Name..." />
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <span class="text-[9px] text-muted-foreground uppercase font-black ml-2 tracking-widest">Mission Briefing</span>
                                            <textarea v-model="eventForm.description" rows="5" placeholder="Detailed instructions for the citizens..." class="w-full bg-white/5 border border-white/10 rounded-[2rem] p-6 text-foreground text-sm focus:border-primary outline-none resize-none leading-relaxed transition-all"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white/5 border border-white/5 p-8 rounded-[2.5rem] hover:border-primary/20 transition-all duration-500">
                                    <label class="text-[10px] text-primary font-black uppercase tracking-[0.2em] mb-6 block">Visual Intel (Images)</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        <div v-for="(img, idx) in eventForm.existing_images" :key="idx" class="relative aspect-square rounded-2xl overflow-hidden border border-white/10 group">
                                            <AppImage :src="img" class="w-full h-full object-cover" />
                                            <button @click.prevent="removeExistingImage(idx)" class="absolute inset-0 bg-red-600/80 text-white opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity backdrop-blur-sm">
                                                <X class="h-6 w-6" />
                                            </button>
                                        </div>
                                        <div v-for="(prev, idx) in imagePreviews" :key="'prev-'+idx" class="relative aspect-square rounded-2xl overflow-hidden border border-primary/30 group">
                                            <img :src="prev" class="w-full h-full object-cover" />
                                            <div class="absolute inset-0 bg-primary/20 backdrop-blur-[2px] flex items-center justify-center">
                                                <span class="text-[8px] font-black text-white uppercase bg-primary px-2 py-1 rounded">NEW</span>
                                            </div>
                                        </div>
                                        <label class="aspect-square rounded-2xl border-2 border-dashed border-white/10 hover:border-primary/40 flex flex-col items-center justify-center gap-2 cursor-pointer transition-all bg-white/5 group">
                                            <Plus class="h-6 w-6 text-muted-foreground group-hover:text-primary transition-colors" />
                                            <span class="text-[8px] font-black text-muted-foreground uppercase group-hover:text-primary transition-colors">Add Intel</span>
                                            <input type="file" multiple @change="handleImageUpload" class="hidden" accept="image/*" />
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Settings -->
                            <div class="space-y-8">
                                <div class="p-8 bg-primary/5 border border-primary/10 rounded-[2.5rem] relative overflow-hidden group">
                                    <div class="absolute -top-12 -right-12 h-32 w-32 bg-primary/10 rounded-full blur-3xl group-hover:bg-primary/20 transition-colors"></div>

                                    <div class="flex items-center justify-between mb-8 relative z-10">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 rounded-xl bg-primary/20 grid place-items-center shadow-neon-sm">
                                                <Ticket class="h-5 w-5 text-primary" />
                                            </div>
                                            <span class="text-xs font-black text-foreground uppercase tracking-widest">VIP Pass</span>
                                        </div>
                                        <input type="checkbox" v-model="eventForm.has_vip_pass" class="sr-only peer" id="has-vip">
                                        <label for="has-vip" class="relative w-12 h-6 bg-white/10 rounded-full peer peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6 cursor-pointer"></label>
                                    </div>

                                    <div v-if="eventForm.has_vip_pass" class="space-y-6 relative z-10 animate-in slide-in-from-top-4 duration-500">
                                        <div class="space-y-2">
                                            <span class="text-[9px] text-sky-400 font-black uppercase ml-1 tracking-widest">Price (💎)</span>
                                            <input type="number" v-model="eventForm.diamond_price" class="w-full h-12 bg-white/5 border border-sky-500/20 rounded-2xl px-5 text-foreground outline-none focus:border-sky-400 font-black text-lg" />
                                        </div>
                                        <div class="space-y-2">
                                            <span class="text-[9px] text-primary font-black uppercase ml-1 tracking-widest">Reward Class</span>
                                            <select v-model="eventForm.reward_type" class="w-full h-12 bg-white/5 border border-white/10 rounded-2xl px-5 text-[10px] text-foreground outline-none focus:border-primary appearance-none">
                                                <option value="" class="bg-gaming-dark">No Reward</option>
                                                <option v-for="type in rewardTypes" :key="type.value" :value="type.value" class="bg-gaming-dark">{{ type.label }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p v-else class="text-[9px] text-muted-foreground italic leading-relaxed relative z-10">Enable VIP access to allow citizens to purchase special privileges with their diamonds.</p>
                                </div>

                                <div class="p-8 bg-white/5 border border-white/5 rounded-[2.5rem] space-y-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-emerald-500/10 grid place-items-center">
                                                <Eye v-if="eventForm.is_active" class="h-5 w-5 text-emerald-400" />
                                                <EyeOff v-else class="h-5 w-5 text-red-400" />
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-black text-foreground uppercase tracking-widest">Publicity</p>
                                                <p class="text-[8px] text-muted-foreground uppercase font-bold">{{ eventForm.is_active ? 'Live Broadcast' : 'Hidden / Draft' }}</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" v-model="eventForm.is_active" class="sr-only peer" id="is-active">
                                        <label for="is-active" class="relative w-12 h-6 bg-white/10 rounded-full peer peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6 cursor-pointer"></label>
                                    </div>
                                    <p class="text-[8px] text-muted-foreground italic leading-tight border-t border-white/5 pt-4">Encryption: AES-256 Enabled. Data will be broadcasted to all citizens in real-time upon deployment.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-8 border-t border-white/5">
                            <div class="flex items-center gap-4">
                                <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-neon"></div>
                                <span class="text-[9px] text-foreground/40 font-black uppercase tracking-[0.3em]">System Ready for Deployment</span>
                            </div>
                            <div class="flex gap-6 items-center">
                                <button type="button" @click="showEventModal = false" class="text-[10px] font-black uppercase text-foreground/40 hover:text-foreground transition-all tracking-widest">Abord Mission</button>
                                <NeonButton type="submit" variant="primary" :disabled="eventForm.processing" class="shadow-neon px-12 h-14 text-[10px] tracking-[0.2em]">
                                    {{ eventForm.id ? 'UPDATE PROTOCOL' : 'DEPLOY EVENT' }}
                                </NeonButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
.site-layout {
    position: relative;
    z-index: 10;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
