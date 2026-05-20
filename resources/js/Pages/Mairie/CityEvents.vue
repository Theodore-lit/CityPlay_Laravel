<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import AppImage from '@/Components/AppImage.vue';
import { firstStorageUrl } from '@/lib/storageUrl';
import GlowInput from '@/Components/GlowInput.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  Calendar, Plus, ChevronLeft, Trash2, Image as ImageIcon, 
  Save, Clock, MapPin, Info, X, Edit2
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    city: Object,
});

const showEventModal = ref(false);
const selectedEvent = ref(null);
const imagePreviews = ref([]);

const eventForm = useForm({
    id: null,
    title: '',
    description: '',
    event_date: '',
    location_name: '',
    images: [],
    existing_images: [],
});

const openEventModal = (event = null) => {
    if (event) {
        selectedEvent.value = event;
        eventForm.id = event.id;
        eventForm.title = event.title;
        eventForm.description = event.description;
        // Format date for input type="datetime-local"
        const date = new Date(event.event_date);
        const formattedDate = date.toISOString().slice(0, 16);
        eventForm.event_date = formattedDate;
        eventForm.location_name = event.location_name;
        eventForm.existing_images = event.image_urls?.length ? event.image_urls : (event.images || []);
        eventForm.images = [];
    } else {
        selectedEvent.value = null;
        eventForm.reset();
        eventForm.id = null;
        eventForm.images = [];
        eventForm.existing_images = [];
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
    eventForm.post(route('mairie.events.store', props.city.id), {
        onSuccess: () => {
            showEventModal.value = false;
            eventForm.reset();
        }
    });
};

const deleteEvent = (event) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
        eventForm.delete(route('mairie.events.delete', event.id));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
  <Head :title="`Événements - ${city.name} — CityPlay HQ`" />

  <SiteLayout>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div class="flex items-center gap-5">
            <Link :href="route('mairie.city.hub', city.id)" class="h-12 w-12 rounded-2xl glass grid place-items-center text-accent hover:scale-110 transition-all shadow-2xl border border-accent/20">
                <ChevronLeft class="h-6 w-6" />
            </Link>
            <div>
                <div class="text-[10px] text-accent uppercase tracking-[0.3em] font-black mb-1">Gestion Culturelle</div>
                <h1 class="font-display text-3xl md:text-5xl text-white uppercase italic font-black">ÉVÉNEMENTS : {{ city.name }}</h1>
            </div>
        </div>
        <NeonButton @click="openEventModal()" variant="purple" size="sm">
            <Plus class="h-4 w-4 mr-2" />Nouvel Événement
        </NeonButton>
      </div>

      <!-- Events List -->
      <div v-if="city.events.length > 0" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <div 
            v-for="event in city.events" 
            :key="event.id"
            class="group relative overflow-hidden rounded-[2.5rem] glass-strong border-white/5 hover:border-accent/30 transition-all duration-500"
        >
            <div class="aspect-video overflow-hidden relative">
                <AppImage
                    :src="firstStorageUrl(event.image_urls) || firstStorageUrl(event.images)"
                    fallback="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&q=80&w=800"
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-gaming-darker via-transparent to-transparent opacity-60" />
                
                <div class="absolute top-4 right-4 flex gap-2">
                    <button @click="openEventModal(event)" class="h-10 w-10 rounded-xl glass-strong border-white/10 flex items-center justify-center text-white hover:bg-accent hover:text-black transition-all">
                        <Edit2 class="h-5 w-5" />
                    </button>
                    <button @click="deleteEvent(event)" class="h-10 w-10 rounded-xl glass-strong border-white/10 flex items-center justify-center text-destructive hover:bg-destructive hover:text-white transition-all">
                        <Trash2 class="h-5 w-5" />
                    </button>
                </div>
            </div>
            
            <div class="p-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2 text-accent text-[10px] font-black uppercase tracking-widest">
                        <Clock class="h-3.5 w-3.5" /> {{ formatDate(event.event_date) }}
                    </div>
                </div>
                
                <h3 class="font-display text-xl text-white uppercase italic font-black mb-3">{{ event.title }}</h3>
                <p class="text-xs text-white/60 line-clamp-3 leading-relaxed mb-6">{{ event.description }}</p>
                
                <div class="flex items-center gap-3 text-[10px] font-black text-white/40 uppercase tracking-widest">
                    <MapPin class="h-4 w-4 text-accent" /> {{ event.location_name || 'Lieu non spécifié' }}
                </div>
            </div>
        </div>
      </div>
      
      <div v-else class="text-center py-20 glass-strong rounded-[3rem] border border-white/5">
          <Calendar class="h-16 w-16 text-white/10 mx-auto mb-6" />
          <h3 class="font-display text-2xl text-white uppercase italic font-black">Aucun événement planifié</h3>
          <p class="text-white/40 mt-2">Commencez par ajouter le premier événement phare de la ville.</p>
          <NeonButton @click="openEventModal()" variant="purple" class="mt-8">
              CRÉER UN ÉVÉNEMENT
          </NeonButton>
      </div>
    </div>

    <!-- Event Modal -->
    <Modal :show="showEventModal" @close="showEventModal = false">
        <div class="p-8 bg-gaming-darker border border-accent/20 rounded-[2.5rem] overflow-hidden relative max-w-2xl mx-auto shadow-2xl">
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

            <div class="relative z-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <div class="text-[10px] text-accent font-black uppercase tracking-[0.3em] mb-2">
                            {{ eventForm.id ? 'MODIFICATION' : 'NOUVEL ÉVÉNEMENT' }}
                        </div>
                        <h2 class="font-display text-3xl text-white uppercase italic font-black leading-none">DÉTAILS DE L'ÉVÉNEMENT</h2>
                    </div>
                    <button @click="showEventModal = false" class="h-10 w-10 rounded-xl glass flex items-center justify-center text-white/40 hover:text-white transition-all">
                        <X class="h-6 w-6" />
                    </button>
                </div>

                <form @submit.prevent="submitEvent" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] text-accent font-black uppercase tracking-[0.3em] mb-2 block">TITRE DE L'ÉVÉNEMENT</label>
                                <GlowInput v-model="eventForm.title" placeholder="Ex: Festival des Masques" required />
                            </div>
                            
                            <div>
                                <label class="text-[10px] text-accent font-black uppercase tracking-[0.3em] mb-2 block">DATE & HEURE</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="eventForm.event_date"
                                    class="w-full h-14 bg-white/5 border border-white/10 rounded-2xl px-4 text-white focus:border-accent outline-none transition-all"
                                    required
                                />
                            </div>

                            <div>
                                <label class="text-[10px] text-accent font-black uppercase tracking-[0.3em] mb-2 block">LIEU DE L'ÉVÉNEMENT</label>
                                <GlowInput v-model="eventForm.location_name" placeholder="Ex: Place de l'Indépendance" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] text-accent font-black uppercase tracking-[0.3em] mb-2 block">DESCRIPTION</label>
                                <textarea 
                                    v-model="eventForm.description" 
                                    rows="8"
                                    placeholder="Décrivez l'importance de cet événement..."
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-accent outline-none transition-all resize-none text-sm"
                                    required
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Image Management -->
                    <div>
                        <label class="text-[10px] text-accent font-black uppercase tracking-[0.3em] mb-4 block">GALERIE PHOTOS (Plusieurs possibles)</label>
                        
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <!-- Existing Images -->
                            <div v-for="(img, idx) in eventForm.existing_images" :key="idx" class="relative aspect-square rounded-xl overflow-hidden border border-white/10 group">
                                <AppImage :src="img" class="w-full h-full object-cover" />
                                <button @click.prevent="removeExistingImage(idx)" class="absolute top-1 right-1 h-6 w-6 rounded-lg bg-destructive text-white opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                            
                            <!-- New Previews -->
                            <div v-for="(img, idx) in imagePreviews" :key="idx" class="relative aspect-square rounded-xl overflow-hidden border border-accent/30">
                                <img :src="img" class="w-full h-full object-cover opacity-50" />
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[8px] font-black text-accent uppercase">NOUVEAU</span>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <label class="aspect-square rounded-xl border-2 border-dashed border-white/10 hover:border-accent/40 hover:bg-accent/5 transition-all cursor-pointer flex flex-col items-center justify-center gap-2 group">
                                <ImageIcon class="h-6 w-6 text-white/20 group-hover:text-accent transition-colors" />
                                <span class="text-[8px] font-black text-white/20 group-hover:text-accent transition-colors">AJOUTER</span>
                                <input type="file" multiple @change="handleImageUpload" class="hidden" accept="image/*" />
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t border-white/5">
                        <NeonButton type="button" variant="outline" @click="showEventModal = false">ANNULER</NeonButton>
                        <NeonButton type="submit" variant="purple" :disabled="eventForm.processing">
                            <Save class="h-4 w-4 mr-2" /> {{ eventForm.id ? 'METTRE À JOUR' : 'ENREGISTRER L\'ÉVÉNEMENT' }}
                        </NeonButton>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
  </SiteLayout>
</template>

<style scoped>
.grid-bg {
  background-image: linear-gradient(var(--border) 1px, transparent 1px), linear-gradient(90deg, var(--border) 1px, transparent 1px);
  background-size: 30px 30px;
  mask-image: radial-gradient(circle at center, black, transparent 80%);
}
</style>
