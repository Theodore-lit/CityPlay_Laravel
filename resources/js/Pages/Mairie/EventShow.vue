<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ChevronLeft, Calendar, MapPin, Ticket,
    Gem, Gift, Clock, Share2, Info
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppImage from '@/Components/AppImage.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { firstStorageUrl } from '@/lib/storageUrl';

const props = defineProps({
    event: Object,
    city: Object
});

// Gestion de la galerie
const activeImageIndex = ref(0);
const allImages = computed(() => {
    // Fusionne les images si elles viennent de sources différentes (legacy vs new)
    return props.event.image_urls || props.event.images || [];
});

const nextImage = () => {
    activeImageIndex.value = (activeImageIndex.value + 1) % allImages.value.length;
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`${event.title} - CityPlay`" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 pb-24 relative z-10">

            <div class="flex items-center gap-4 mb-8">
                <Link :href="route('mairie.cities.events', city.id)" class="h-10 w-10 rounded-xl glass border border-white/10 grid place-items-center text-foreground/60 hover:text-primary transition-all">
                    <ChevronLeft class="h-5 w-5" />
                </Link>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-primary/60">Retour</span>
            </div>

            <div class="grid lg:grid-cols-12 gap-8">

                <div class="lg:col-span-8 space-y-6">
                    <div class="relative aspect-[16/9] rounded-[2.5rem] overflow-hidden border border-white/5 shadow-2xl group">
                        <transition name="fade" mode="out-in">
                            <AppImage
                                :key="activeImageIndex"
                                :src="allImages[activeImageIndex]"
                                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                            />
                        </transition>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none" />

                        <div class="absolute top-6 left-6 flex flex-col gap-3">
                            <div v-if="event.has_vip_pass" class="px-4 py-1.5 rounded-full bg-primary text-primary-foreground text-[10px] font-black uppercase tracking-widest flex items-center gap-2 shadow-neon">
                                <Ticket class="h-4 w-4" /> VIP PASS REQUIRED
                            </div>
                        </div>

                        <div v-if="allImages.length > 1" class="absolute bottom-6 right-6 flex gap-2">
                            <button v-for="(img, idx) in allImages" :key="idx"
                                    @click="activeImageIndex = idx"
                                    class="h-2 transition-all duration-300 rounded-full border border-white/20"
                                    :class="activeImageIndex === idx ? 'w-8 bg-primary shadow-neon border-primary' : 'w-2 bg-white/20 hover:bg-white/40'">
                            </button>
                        </div>
                    </div>

                    <div v-if="allImages.length > 1" class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                        <button v-for="(img, idx) in allImages" :key="idx"
                                @click="activeImageIndex = idx"
                                class="relative min-w-[120px] h-20 rounded-2xl overflow-hidden border-2 transition-all"
                                :class="activeImageIndex === idx ? 'border-primary shadow-neon-sm scale-95' : 'border-transparent opacity-50 hover:opacity-100'">
                            <AppImage :src="img" class="w-full h-full object-cover" />
                        </button>
                    </div>

                    <div class="glass-strong border border-white/5 p-8 rounded-[2.5rem]">
                        <h2 class="text-[10px] text-primary font-black uppercase tracking-[0.4em] mb-6 flex items-center gap-2">
                            <Info class="h-4 w-4" /> Intel Briefing
                        </h2>
                        <p class="text-foreground/80 leading-relaxed font-light text-lg italic">
                            "{{ event.description }}"
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">

                    <div class="glass-strong border border-primary/20 p-8 rounded-[2.5rem] relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 h-32 w-32 bg-primary/10 blur-3xl rounded-full group-hover:bg-primary/20 transition-all" />

                        <h1 class="font-display text-4xl text-foreground uppercase italic font-black leading-tight mb-8">
                            {{ event.title }}
                        </h1>

                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="h-10 w-10 rounded-xl glass border border-white/10 grid place-items-center text-primary shrink-0">
                                    <Clock class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] text-foreground/40 uppercase font-black tracking-widest">Scheduled</p>
                                    <p class="text-sm font-bold text-foreground capitalize">{{ formatDate(event.event_date) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="h-10 w-10 rounded-xl glass border border-white/10 grid place-items-center text-primary shrink-0">
                                    <MapPin class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] text-foreground/40 uppercase font-black tracking-widest">Location</p>
                                    <p class="text-sm font-bold text-foreground">{{ event.location_name || 'Classified' }}</p>
                                </div>
                            </div>

                            <div v-if="event.diamond_price > 0" class="flex items-start gap-4 p-4 rounded-2xl bg-sky-500/5 border border-sky-500/20">
                                <div class="h-10 w-10 rounded-xl bg-sky-500/20 grid place-items-center text-sky-400 shrink-0 shadow-neon-sm">
                                    <Gem class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] text-sky-400 font-black uppercase tracking-widest">Access Fee</p>
                                    <p class="text-xl font-display font-black text-foreground">{{ event.diamond_price }} <span class="text-xs italic opacity-50">Diamonds</span></p>
                                </div>
                            </div>

                            <div v-if="event.reward_type" class="flex items-start gap-4 p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/20">
                                <div class="h-10 w-10 rounded-xl bg-emerald-500/20 grid place-items-center text-emerald-400 shrink-0">
                                    <Gift class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] text-emerald-400 font-black uppercase tracking-widest">Completion Reward</p>
                                    <p class="text-sm font-bold text-foreground uppercase tracking-tighter">{{ event.reward_type }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 space-y-3">
                            <NeonButton variant="primary" class="w-full shadow-neon h-14 text-sm tracking-[0.2em]">
                                PARTICIPATE NOW
                            </NeonButton>
                            <button class="w-full py-4 text-[10px] font-black uppercase text-foreground/40 hover:text-primary transition-all flex items-center justify-center gap-2">
                                <Share2 class="h-3 w-3" /> Broadcast to Citizens
                            </button>
                        </div>
                    </div>

                    <div class="glass border border-white/5 p-6 rounded-[2rem] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full animate-pulse shadow-neon" :class="event.is_active ? 'bg-emerald-500 shadow-emerald-500' : 'bg-red-500 shadow-red-500'"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ event.is_active ? 'System Live' : 'Maintenance' }}</span>
                        </div>
                        <span class="text-[8px] font-mono text-foreground/20">V.1.0.4-LATEST</span>
                    </div>
                </div>

            </div>
        </div>
    </SiteLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
