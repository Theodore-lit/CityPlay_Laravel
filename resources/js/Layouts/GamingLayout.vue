<script setup>
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
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
        <nav class="bg-gaming-surface border-b-2 border-gaming-blue/40 sticky top-0 z-50 shadow-gaming py-2">
            <!-- max-w-7xl remplacé par max-w-full avec de généreux paddings pour étaler la navbar -->
            <div class="max-w-full mx-auto px-6 sm:px-10 lg:px-16">
                <div class="flex justify-between h-20 items-center">
                    
                    <!-- LOGO & TITRE -->
                    <div class="flex items-center">
                        <Link :href="route('dashboard')" class="flex items-center space-x-4 group">
                            <ApplicationLogo class="h-12 w-auto fill-current text-gaming-blue group-hover:scale-105 transition-transform duration-300" />
                            <span class="text-2xl md:text-3xl font-black tracking-tight text-gaming-blue-light uppercase filter drop-shadow-[0_0_8px_rgba(37,99,235,0.5)]">
                                CityPlay
                            </span>
                        </Link>
                    </div>

                    <!-- BLOC DE DROITE (PLUS ESPACÉ) -->
                    <div class="hidden sm:flex sm:items-center space-x-10">
                        
                        <!-- STATS DES JOUEURS -->
                        <div v-if="user.role === 'joueur'" class="flex items-center space-x-8 bg-gaming-dark/70 px-8 py-3 rounded-2xl border-2 border-gaming-blue/30 shadow-inner">
                            <div class="flex items-center text-yellow-400 font-mono">
                                <span class="text-2xl mr-3 filter drop-shadow-[0_0_5px_rgba(234,179,8,0.5)]">⚡</span>
                                <span class="text-xl font-black uppercase tracking-wider">{{ user.xp || 0 }} <span class="text-xs text-yellow-500 font-sans font-bold">XP</span></span>
                            </div>
                            
                            <div class="h-6 w-[2px] bg-gaming-blue/20" />

                            <button 
                                @click="router.post(route('player.buy.heart'))"
                                class="flex items-center text-red-500 hover:scale-110 transition-transform group relative font-mono"
                            >
                                <span class="text-2xl mr-3 filter drop-shadow-[0_0_5px_rgba(239,68,68,0.5)] animate-pulse">❤️</span>
                                <span class="text-xl font-black">{{ user.hearts || 0 }}</span>
                                
                                <span class="absolute -top-12 left-1/2 -translate-x-1/2 bg-gaming-surface border-2 border-red-500/50 text-xs text-white px-3 py-1.5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none shadow-2xl z-50">
                                    Acheter 1 ❤️ <span class="text-yellow-400 font-bold">(500 XP)</span>
                                </span>
                            </button>
                        </div>

                        <!-- DROPDOWN DU PROFIL (LARGEMENT AGRANDI : width="64") -->
                        <div class="relative">
                            <Dropdown align="right" width="64">
                                <template #trigger>
                                    <span class="inline-flex rounded-xl">
                                        <!-- Bouton plus large (px-6) pour éviter que le nom soit serré -->
                                        <button type="button" class="inline-flex items-center min-w-[180px] justify-between px-6 py-3 border-2 border-white/10 text-base font-bold rounded-xl text-gray-300 bg-gaming-dark hover:text-white hover:border-gaming-blue/50 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                                            <span class="truncate mr-2">{{ user.name }}</span>
                                            <svg class="h-5 w-5 text-gaming-blue-light shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <div class="py-2 text-base">
                                        <DropdownLink :href="route('profile.edit')" class="py-3 px-6 font-semibold block hover:bg-white/5"> Profil </DropdownLink>
                                        <div class="border-t border-white/5 my-1" />
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="py-3 px-6 text-red-400 hover:text-red-300 font-semibold w-full text-left hover:bg-white/5"> Déconnexion </DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-gaming-surface/50 border-b border-white/5 shadow-lg">
            <div class="max-w-full mx-auto py-6 px-6 sm:px-10 lg:px-16">
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
:deep(main) {
    height: calc(100vh - 5rem);
}
</style>
