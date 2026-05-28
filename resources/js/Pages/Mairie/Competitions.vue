<script setup>
// kamal
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SiteLayout from '@/Layouts/SiteLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import {
    ChevronLeft, Plus, Edit2, Trash2, Calendar, Target, Award, Info, X, Clock, Settings, Trophy, Zap, Heart, Gem
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    event: Object,
    competitions: Array,
});

const showModal = ref(false);
const showDeleteConfirm = ref(false);
const compToDelete = ref(null);

/**
 * Formulaire réactif pour la gestion des compétitions.
 * Définit les paramètres du défi : type (classement ou fixe), objectif, dates et récompenses.
 */
const competitionForm = useForm({
    id: null,
    city_event_id: props.event.id,
    title: '',
    description: '',
    type: 'fixed', // 'ranking' pour un podium, 'fixed' pour un seuil à atteindre
    objective_type: 'xp', // Ressource à accumuler : 'xp', 'hearts', 'diamonds'
    goal_amount: null,
    ranking_limit: 3,
    start_date: '',
    end_date: '',
    reward_description: '',
    is_active: true,
});

/**
 * Ouvre le modal d'édition ou de création.
 * Pré-remplit les champs si une compétition existante est fournie.
 */
const openModal = (comp = null) => {
    if (comp) {
        competitionForm.id = comp.id;
        competitionForm.title = comp.title;
        competitionForm.description = comp.description;
        competitionForm.type = comp.type;
        competitionForm.objective_type = comp.objective_type;
        competitionForm.goal_amount = comp.goal_amount;
        competitionForm.ranking_limit = comp.ranking_limit;
        competitionForm.start_date = comp.start_date.split('.')[0];
        competitionForm.end_date = comp.end_date.split('.')[0];
        competitionForm.reward_description = comp.reward_description;
        competitionForm.is_active = !!comp.is_active;
    } else {
        competitionForm.reset();
        competitionForm.id = null;
        competitionForm.city_event_id = props.event.id;
    }
    showModal.value = true;
};

/**
 * Soumet le protocole de compétition au serveur.
 */
const submit = () => {
    competitionForm.post(route('mairie.competitions.store'), {
        onSuccess: () => {
            showModal.value = false;
            competitionForm.reset();
        },
    });
};

/**
 * Supprime le protocole de compétition après confirmation manuelle.
 */
const deleteComp = (id) => {
    compToDelete.value = id;
    showDeleteConfirm.value = true;
};

const executeDelete = () => {
    if (compToDelete.value) {
        router.delete(route('mairie.competitions.destroy', compToDelete.value), {
            onSuccess: () => {
                showDeleteConfirm.value = false;
                compToDelete.value = null;
            },
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// Style de carte "eau de roche"
const cardClass = "group relative overflow-hidden rounded-[2.5rem] bg-white/10 backdrop-blur-2xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] hover:border-primary/40 transition-all duration-500";
</script>

<template>
    <Head :title="`Compétitions - ${event.title}`" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div class="flex items-center gap-6">
                    <Link :href="route('mairie.cities.events', event.city_id)" class="h-12 w-12 rounded-2xl bg-white/5 backdrop-blur-xl grid place-items-center text-primary hover:scale-110 transition-all border border-primary/20">
                        <ChevronLeft class="h-6 w-6" />
                    </Link>
                    <div>
                        <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">Event Specifics</div>
                        <h1 class="font-display text-4xl md:text-5xl text-foreground uppercase italic font-black">Competitions</h1>
                        <p class="text-xs text-muted-foreground mt-2 font-bold uppercase tracking-widest">Linked to: <span class="text-primary italic">{{ event.title }}</span></p>
                    </div>
                </div>
                <NeonButton @click="openModal()" variant="primary" size="sm" class="hover-game shadow-neon">
                    <Plus class="h-4 w-4 mr-2" />New Competition
                </NeonButton>
            </div>

            <div v-if="competitions.length > 0" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="comp in competitions" :key="comp.id"
                     :class="cn(cardClass, { 'opacity-50 grayscale': !comp.is_active })">

                    <div class="p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-primary text-[10px] font-black uppercase tracking-widest">
                                <Trophy v-if="comp.type === 'ranking'" class="h-3.5 w-3.5" />
                                <Target v-else class="h-3.5 w-3.5" />
                                {{ comp.type === 'ranking' ? `TOP ${comp.ranking_limit}` : `Objectif: ${comp.goal_amount}` }}
                            </div>
                            <div class="flex gap-2">
                                <button @click="openModal(comp)" class="h-8 w-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-foreground hover:text-primary transition-all">
                                    <Edit2 class="h-3.5 w-3.5" />
                                </button>
                                <button @click="deleteComp(comp.id)" class="h-8 w-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <h3 class="font-display text-xl text-foreground uppercase italic font-black mb-3 line-clamp-1 tracking-tight">
                            {{ comp.title }}
                        </h3>

                        <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed mb-6">{{ comp.description }}</p>

                        <div class="space-y-3 pt-4 border-t border-white/5">
                            <div class="flex items-center gap-2 text-[10px] font-black text-foreground/60 uppercase">
                                <Calendar class="h-3.5 w-3.5 text-primary" />
                                {{ formatDate(comp.start_date) }} - {{ formatDate(comp.end_date) }}
                            </div>
                            <div class="flex items-center gap-2 text-[10px] font-black text-sky-400 uppercase">
                                <Zap v-if="comp.objective_type === 'xp'" class="h-3.5 w-3.5" />
                                <Heart v-else-if="comp.objective_type === 'hearts'" class="h-3.5 w-3.5 text-red-500" />
                                <Gem v-else class="h-3.5 w-3.5 text-cyan-400" />
                                Type: {{ comp.objective_type.toUpperCase() }}
                            </div>
                            <div v-if="comp.reward_description" class="flex items-center gap-2 text-[10px] font-black text-emerald-400 uppercase">
                                <Award class="h-3.5 w-3.5" /> {{ comp.reward_description }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 bg-white/5 backdrop-blur-xl rounded-[3rem] border border-dashed border-white/10 shadow-inner">
                <Info class="h-12 w-12 mx-auto text-muted-foreground/20 mb-4" />
                <p class="text-muted-foreground uppercase font-black tracking-widest">No competitions registered for this protocol.</p>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <ConfirmModal 
            :show="showDeleteConfirm"
            title="Supprimer la compétition ?"
            message="Êtes-vous sûr de vouloir supprimer cette compétition ? Cette action est irréversible."
            variant="danger"
            confirmText="Supprimer"
            @close="showDeleteConfirm = false"
            @confirm="executeDelete"
        />

        <!-- MODAL EDITOR -->
        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gaming-dark/60 backdrop-blur-md" @click="showModal = false"></div>

            <div class="relative w-full max-w-4xl bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[3rem] shadow-[0_8px_32px_0_rgba(0,0,0,0.5)] overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="p-8 relative z-10">
                    <div class="flex justify-between items-center mb-10 px-4">
                        <div>
                            <h2 class="font-display text-3xl text-foreground uppercase italic font-black tracking-tighter">
                                <span class="text-primary mr-2">//</span>Competition Editor
                            </h2>
                        </div>
                        <button @click="showModal = false" class="group h-10 w-10 rounded-xl bg-white/5 flex items-center justify-center transition-all hover:border-primary/50">
                            <X class="h-5 w-5 text-foreground/40 group-hover:text-primary transition-colors" />
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-6">
                                <div class="bg-white/5 border border-white/5 p-6 rounded-[2rem] hover:border-primary/20 transition-colors">
                                    <label class="text-[10px] text-primary font-black uppercase tracking-[0.2em] mb-4 block">Primary Information</label>
                                    <div class="space-y-4">
                                        <GlowInput v-model="competitionForm.title" placeholder="Competition Title..." required />
                                        <textarea v-model="competitionForm.description" placeholder="Description of the challenge..." class="w-full h-32 bg-white/5 border border-white/10 rounded-2xl p-4 text-sm text-foreground outline-none focus:border-primary/50 transition-all resize-none" required></textarea>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase ml-1">Type de Défi</span>
                                                <select v-model="competitionForm.type" class="w-full h-12 bg-white/5 border border-white/10 rounded-xl px-4 text-xs text-foreground outline-none focus:border-primary">
                                                    <option value="ranking">Classement (Top X)</option>
                                                    <option value="fixed">Objectif Fixe</option>
                                                </select>
                                            </div>
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase ml-1">Ressource</span>
                                                <select v-model="competitionForm.objective_type" class="w-full h-12 bg-white/5 border border-white/10 rounded-xl px-4 text-xs text-foreground outline-none focus:border-primary">
                                                    <option value="xp">XP (Expérience)</option>
                                                    <option value="hearts">Cœurs</option>
                                                    <option value="diamonds">Diamants</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div v-if="competitionForm.type === 'fixed'" class="animate-in slide-in-from-top-2">
                                            <GlowInput type="number" v-model="competitionForm.goal_amount" placeholder="Montant à atteindre (ex: 40)" required />
                                        </div>
                                        <div v-if="competitionForm.type === 'ranking'" class="animate-in slide-in-from-top-2">
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase ml-1">Limite du Top</span>
                                                <select v-model="competitionForm.ranking_limit" class="w-full h-12 bg-white/5 border border-white/10 rounded-xl px-4 text-xs text-foreground outline-none focus:border-primary">
                                                    <option :value="1">Top 1 (Uniquement le premier)</option>
                                                    <option :value="3">Top 3 (Les trois premiers)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-white/5 border border-white/5 p-6 rounded-[2rem] hover:border-primary/20 transition-colors">
                                    <label class="text-[10px] text-primary font-black uppercase tracking-[0.2em] mb-4 block">Timing & Rewards</label>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase ml-1">Start Date</span>
                                                <input type="datetime-local" v-model="competitionForm.start_date" class="w-full h-12 bg-white/5 border border-white/10 rounded-xl px-4 text-xs text-foreground outline-none focus:border-primary" required />
                                            </div>
                                            <div class="space-y-2">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase ml-1">End Date</span>
                                                <input type="datetime-local" v-model="competitionForm.end_date" class="w-full h-12 bg-white/5 border border-white/10 rounded-xl px-4 text-xs text-foreground outline-none focus:border-primary" required />
                                            </div>
                                        </div>
                                        <GlowInput v-model="competitionForm.reward_description" placeholder="Reward Description (e.g. 500 Diamonds)" />
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-6 bg-white/5 rounded-[2rem] border border-white/5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-primary/10 grid place-items-center text-primary">
                                            <Settings class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-foreground uppercase tracking-widest">Active Status</p>
                                            <p class="text-[9px] text-muted-foreground uppercase">Visible to all players</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" v-model="competitionForm.is_active" class="sr-only peer" id="comp-active">
                                        <label for="comp-active" class="relative w-12 h-6 bg-white/10 rounded-full peer peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6 cursor-pointer"></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8">
                            <button type="button" @click="showModal = false" class="px-8 py-4 rounded-2xl bg-white/5 text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-all">Cancel</button>
                            <NeonButton variant="primary" size="lg" :disabled="competitionForm.processing" class="px-12">
                                {{ competitionForm.id ? 'Update Protocol' : 'Deploy Competition' }}
                            </NeonButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SiteLayout>
</template>
