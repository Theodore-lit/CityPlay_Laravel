<script setup>
import { ref } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = page.props.auth.user;

const showingLogoutModal = ref(false);
const logoutForm = useForm({
    deactivate: false
});

const handleLogoutClick = () => {
    if (user.role === 'joueur') {
        showingLogoutModal.value = true;
    } else {
        performLogout();
    }
};

const performLogout = (deactivate = false) => {
    logoutForm.deactivate = deactivate;
    logoutForm.post(route('logout'), {
        onFinish: () => {
            showingLogoutModal.value = false;
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <Link :href="route('dashboard')" class="flex items-center space-x-3">
                            <ApplicationLogo class="h-12 w-auto" />
                            <span class="text-2xl font-black tracking-tighter text-gaming-orange uppercase italic">CityPlay</span>
                        </Link>
                    </div>

                    <div v-if="user" class="hidden sm:flex sm:items-center sm:ms-6 space-x-6">
                        <!-- Stats for Players -->
                        <div v-if="user.role === 'joueur'" class="flex items-center space-x-4 bg-gray-100 px-6 py-2 rounded-2xl border border-gray-200">
                            <div class="flex items-center text-gaming-orange">
                                <span class="text-xl mr-2">🪙</span>
                                <span class="font-black text-lg">{{ user.coins }}</span>
                            </div>
                            <div class="flex items-center text-red-500">
                                <span class="text-xl mr-2">❤️</span>
                                <span class="font-black text-lg">{{ user.hearts }}</span>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-4 py-2.5 border border-gray-200 text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-all">
                                            {{ user.name }}
                                            <svg class="ms-2 -me-0.5 h-4 w-4 text-gaming-orange" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Mon Profil </DropdownLink>
                                    <button 
                                        @click="handleLogoutClick"
                                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                    >
                                        Se déconnecter
                                    </button>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Logout/Deactivation Modal -->
        <Modal :show="showingLogoutModal" @close="showingLogoutModal = false">
            <div class="p-6">
                <h2 class="text-lg font-black text-gray-900 uppercase tracking-widest mb-4">
                    Déconnexion
                </h2>
                <p class="text-sm text-gray-600 mb-6">
                    Voulez-vous désactiver votre compte avant de vous déconnecter ? 
                    <br><br>
                    <span class="italic text-xs">Note : Si vous ne désactivez pas votre compte, il restera actif pendant 2 mois d'inactivité avant d'être archivé automatiquement.</span>
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="performLogout(false)">
                        Non, juste déconnexion
                    </SecondaryButton>
                    <DangerButton @click="performLogout(true)">
                        Oui, désactiver mon compte
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
</style>
