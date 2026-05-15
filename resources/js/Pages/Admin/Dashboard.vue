<script setup>
import GamingLayout from '@/Layouts/GamingLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    mairies: Array,
    stats: Object,
});

const showingAddMairieModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const formCity = useForm({
    
})

const openModal = () => {
    showingAddMairieModal.value = true;
};

const closeModal = () => {
    showingAddMairieModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(route('admin.mairie.store'), {
        onSuccess: () => closeModal(),
    });
};
</script>

<template>
    <Head title="Administration Mairie" />

    <GamingLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl text-gaming-green-light leading-tight uppercase tracking-widest">
                    Tableau de Administrateur
                </h2>
                <button 
                    @click="openModal"
                    class="bg-gaming-green hover:bg-gaming-green-dark text-white font-bold py-2 px-6 rounded-lg transition-all uppercase text-sm tracking-wider"
                >
                    + Ajouter une ville
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm">
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1">Totales des Mairies</p>
                        <p class="text-3xl font-black text-white">{{ mairies.length }}</p>
                    </div>
                    <div class="bg-gaming-surface border border-gaming-green/10 p-6 rounded-2xl shadow-sm">
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1">Joueurs Actifs</p>
                        <p class="text-3xl font-black text-gaming-green-light">{{ stats.active_players }}</p>
                    </div>
                    <div class="bg-gaming-surface border border-gaming-blue/10 p-6 rounded-2xl shadow-sm">
                        <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1">Taux de réussite</p>
                        <p class="text-3xl font-black text-gaming-blue-light">78%</p>
                    </div>
                </div>

                <!-- Cities Management -->
                <div class="bg-gaming-surface border border-gaming-blue/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-gaming-blue/10">
                        <h3 class="text-lg font-bold text-white uppercase tracking-wider">Toutes les Villes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gaming-dark/30">
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Ville</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Statut</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Lieux</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gaming-blue/5">
                                <tr v-for="mairie in mairies" :key="mairie.id" class="hover:bg-gaming-blue/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-white">{{ mairie.name }}</div>
                                        <div class="text-xs text-gray-500 truncate w-48">{{ mairie.description }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2 py-1 rounded text-[10px] font-bold uppercase', mairie.is_active ? 'bg-gaming-green/20 text-gaming-green-light' : 'bg-gray-500/20 text-gray-500']">
                                            {{ mairie.is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-400">
                                        {{ mairie.locations_count || 0 }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-3">
                                            <button class="text-gaming-blue-light hover:text-white transition-colors text-sm font-bold">Modifier</button>
                                            <button class="text-red-500 hover:text-white transition-colors text-sm font-bold">Désactiver</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GamingLayout>

    <!-- Modal Ajout Mairie -->
    <Modal :show="showingAddMairieModal" @close="closeModal">
        <div class="p-6 bg-gaming-surface border border-gaming-blue/20">
            <h2 class="text-xl font-bold text-white uppercase tracking-widest mb-6 border-b border-gaming-blue/10 pb-4">
                Ajouter une nouvelle Mairie
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <InputLabel for="name" value="Nom de la Mairie" class="text-gray-400" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                        v-model="form.name"
                        required
                        autofocus
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" class="text-gray-400" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                        v-model="form.email"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="password" value="Mot de passe" class="text-gray-400" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                            v-model="form.password"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmer" class="text-gray-400" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                            v-model="form.password_confirmation"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                    <div>
                        <InputLabel for="city" value="Confirmer" class="text-gray-400" />
                        <TextInput
                            id="city"
                            type="password"
                            class="mt-1 block w-full bg-gaming-dark border-gaming-blue/20 text-white focus:border-gaming-green focus:ring-gaming-green"
                            v-model="form.city"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.city" />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8 space-x-4">
                    <SecondaryButton @click="closeModal" class="bg-transparent border-gray-600 text-gray-400 hover:bg-gray-800 transition-all">
                        Annuler
                    </SecondaryButton>

                    <PrimaryButton 
                        class="bg-gaming-green hover:bg-gaming-green-dark shadow-gaming-green/20" 
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing"
                    >
                        Créer le compte
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
