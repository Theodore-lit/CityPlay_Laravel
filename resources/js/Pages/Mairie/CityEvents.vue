<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import AppImage from '@/Components/AppImage.vue';
import { firstStorageUrl } from '@/lib/storageUrl';
import GlowInput from '@/Components/GlowInput.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Plus, ChevronLeft, Trash2, Image as ImageIcon,
    Save, Clock, MapPin, X, Edit2, Ticket, Gem, Gift, Eye, EyeOff
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    city: Object,
});

const showEventModal = ref(false);
const imagePreviews = ref([]);

const rewardTypes = [
    { value: 'ticket', label: 'Ticket Entrée' },
    { value: 'meal', label: 'Repas / Snack' },
    { value: 'discount', label: 'Réduction Boutique' },
];

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

const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    eventForm.images = files;
    imagePreviews.value = files.map(file => URL.createObjectURL(file));
};

const removeExistingImage = (index) => {
    eventForm.existing_images.splice(index, 1);
};

const submitEvent = () => {
    eventForm.transform((data) => ({
        ...data,
        _method: eventForm.id ? 'PUT' : 'POST',
    })).post(route('mairie.events.store', props.city.id), {
        onSuccess: () => {
            showEventModal.value = false;
            eventForm.reset();
        },
    });
};

const deleteEvent = (event) => {
    if (confirm('Supprimer cet événement ?')) {
        eventForm.delete(route('mairie.events.delete', event.id));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Événements - ${city.name} — CityPlay HQ`" />

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
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-primary text-[10px] font-black uppercase tracking-widest">
                                <Clock class="h-3.5 w-3.5" /> {{ formatDate(event.event_date) }}
                            </div>
                            <div v-if="event.diamond_price > 0" class="flex items-center gap-1 text-sky-400 font-black text-xs">
                                <Gem class="h-3.5 w-3.5" /> {{ event.diamond_price }}
                            </div>
                        </div>

                        <Link :href="route('mairie.events.show', { city: city.id, event: event.id })" class="group/title">
                            <h3 class="font-display text-xl text-foreground uppercase italic font-black mb-3 line-clamp-1 tracking-tight group-hover/title:text-primary transition-colors italic">
                                {{ event.title }}
                            </h3>
                        </Link>

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
                                        <span>Intel</span>
                                        <Eye class="h-3.5 w-3.5" />
                                    </div>
                                </button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-24 glass-strong rounded-[3rem] border border-white/5">
                <ImageIcon class="h-16 w-16 text-foreground/5 mx-auto mb-6" />
                <h3 class="font-display text-2xl text-foreground uppercase italic font-black opacity-40">No Missions Logged</h3>
                <NeonButton @click="openEventModal()" variant="primary" class="mt-8 shadow-neon">INITIATE EVENT</NeonButton>
            </div>
        </div>

        <Modal :show="showEventModal" @close="showEventModal = false" maxWidth="4xl">
            <div class="relative overflow-hidden glass-strong border border-primary/20 rounded-[3rem] shadow-2xl backdrop-blur-3xl">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

                <div class="p-8 relative z-10">
                    <div class="flex justify-between items-center mb-10 px-4">
                        <div>
                            <h2 class="font-display text-3xl text-foreground uppercase italic font-black tracking-tighter">
                                <span class="text-primary mr-2">//</span>Event Editor
                            </h2>
                        </div>
                        <button @click="showEventModal = false" class="group h-10 w-10 rounded-xl glass flex items-center justify-center transition-all hover:border-primary/50">
                            <X class="h-5 w-5 text-foreground/40 group-hover:text-primary transition-colors" />
                        </button>
                    </div>

                    <form @submit.prevent="submitEvent" class="space-y-6">
                        <div class="grid lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2 space-y-6">
                                <div class="glass border border-white/5 p-6 rounded-[2rem] hover:border-primary/20 transition-colors">
                                    <label class="text-[10px] text-primary font-black uppercase tracking-[0.2em] mb-4 block">Primary Information</label>
                                    <div class="space-y-4">
                                        <GlowInput v-model="eventForm.title" placeholder="Mission Title..." required />
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-foreground/40 uppercase font-bold ml-2">Timestamp</span>
                                                <input type="datetime-local" v-model="eventForm.event_date" class="w-full h-11 glass border border-white/10 rounded-xl px-4 text-xs text-foreground focus:border-primary outline-none transition-all" />
                                            </div>
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-foreground/40 uppercase font-bold ml-2">Vector / Location</span>
                                                <GlowInput v-model="eventForm.location_name" placeholder="Coordinates..." />
                                            </div>
                                        </div>
                                        <textarea v-model="eventForm.description" rows="4" placeholder="Briefing details..." class="w-full glass border border-white/10 rounded-2xl p-4 text-foreground text-xs focus:border-primary outline-none resize-none leading-relaxed transition-all"></textarea>
                                    </div>
                                </div>

                                <div class="glass border border-white/5 p-6 rounded-[2rem]">
                                    <label class="text-[10px] text-primary font-black uppercase tracking-[0.2em] mb-4 block">Visual Assets</label>
                                    <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                                        <div v-for="(img, idx) in eventForm.existing_images" :key="idx" class="relative min-w-[100px] h-[100px] rounded-xl overflow-hidden border border-white/10 group">
                                            <AppImage :src="img" class="w-full h-full object-cover" />
                                            <button @click.prevent="removeExistingImage(idx)" class="absolute inset-0 bg-red-600/80 text-white opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <label class="min-w-[100px] h-[100px] rounded-xl border-2 border-dashed border-white/10 hover:border-primary/40 flex flex-col items-center justify-center gap-1 cursor-pointer transition-all glass">
                                            <Plus class="h-5 w-5 text-foreground/20" />
                                            <input type="file" multiple @change="handleImageUpload" class="hidden" accept="image/*" />
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="p-6 glass border border-primary/10 rounded-[2rem] bg-primary/5">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-lg bg-primary/20 grid place-items-center">
                                                <Ticket class="h-4 w-4 text-primary shadow-neon-sm" />
                                            </div>
                                            <span class="text-[11px] font-black text-foreground uppercase">VIP Tier</span>
                                        </div>
                                        <input type="checkbox" v-model="eventForm.has_vip_pass" class="sr-only peer" id="has-vip">
                                        <label for="has-vip" class="relative w-10 h-5 glass rounded-full peer peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 cursor-pointer"></label>
                                    </div>

                                    <div v-if="eventForm.has_vip_pass" class="space-y-4 animate-in slide-in-from-right-4 duration-300">
                                        <div class="space-y-2">
                                            <span class="text-[9px] text-sky-400 font-bold uppercase ml-1">Price (💎)</span>
                                            <input type="number" v-model="eventForm.diamond_price" class="w-full h-10 glass border border-sky-500/20 rounded-xl px-4 text-foreground outline-none focus:border-sky-400 font-bold" />
                                        </div>
                                        <div class="space-y-2">
                                            <span class="text-[9px] text-primary font-bold uppercase ml-1">Unlock Reward</span>
                                            <select v-model="eventForm.reward_type" class="w-full h-10 glass border border-white/10 rounded-xl px-4 text-[10px] text-foreground outline-none focus:border-primary">
                                                <option value="" class="bg-gaming-dark">None</option>
                                                <option v-for="type in rewardTypes" :key="type.value" :value="type.value" class="bg-gaming-dark">{{ type.label }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-6 glass border border-white/5 rounded-[2rem] space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-foreground/40 uppercase tracking-widest">Public Status</span>
                                        <input type="checkbox" v-model="eventForm.is_active" class="sr-only peer" id="is-active">
                                        <label for="is-active" class="relative w-10 h-5 glass rounded-full peer peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5 cursor-pointer"></label>
                                    </div>
                                    <p class="text-[8px] text-muted-foreground italic leading-tight">Broadcast intel to all citizens in real-time.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 px-4 border-t border-white/5">
                            <div class="hidden md:block">
                                <span class="text-[8px] text-foreground/20 font-mono tracking-tighter uppercase italic">Encryption: AES-256 Enabled</span>
                            </div>
                            <div class="flex gap-4">
                                <button type="button" @click="showEventModal = false" class="text-[10px] font-black uppercase text-foreground/40 hover:text-foreground transition-all">Cancel</button>
                                <NeonButton type="submit" variant="primary" :disabled="eventForm.processing" size="sm" class="shadow-neon px-8">
                                    {{ eventForm.id ? 'Update System' : 'Deploy Event' }}
                                </NeonButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
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
