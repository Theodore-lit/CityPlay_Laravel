<script setup>
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="min-h-screen bg-gaming-dark text-white font-sans">
        <!-- Navigation -->
        <nav class="bg-gaming-surface border-b border-gaming-blue/30 sticky top-0 z-50 shadow-gaming">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('dashboard')" class="flex items-center space-x-2">
                            <ApplicationLogo class="h-9 w-auto fill-current text-gaming-blue" />
                            <span class="text-xl font-bold tracking-tighter text-gaming-blue-light uppercase">CityPlay</span>
                        </Link>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-6">
                        <!-- Stats for Players -->
                        <div v-if="user.role === 'joueur'" class="flex items-center space-x-4 bg-gaming-dark/50 px-4 py-1.5 rounded-full border border-gaming-blue/20">
                            <div class="flex items-center text-yellow-400">
                                <span class="text-lg mr-1">⚡</span>
                                <span class="font-bold">{{ user.xp || 0 }}</span>
                            </div>
                            <button 
                                @click="router.post(route('player.buy.heart'))"
                                class="flex items-center text-red-500 hover:scale-110 transition-transform group relative"
                            >
                                <span class="text-lg mr-1">❤️</span>
                                <span class="font-bold">{{ user.hearts || 0 }}</span>
                                <span class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gaming-dark text-[10px] text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap border border-white/10 pointer-events-none shadow-xl">
                                    Acheter 1 ❤️ (500 XP)
                                </span>
                            </button>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-400 bg-gaming-surface hover:text-white focus:outline-none transition ease-in-out duration-150">
                                            {{ user.name }}
                                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Profil </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button"> Déconnexion </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-gaming-surface/50 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <slot />
        </main>
    </div>
</template>

<style scoped>
:deep(main) {
    height: calc(100vh - 4rem);
}
</style>
