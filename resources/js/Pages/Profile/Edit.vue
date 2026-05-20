<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
import HUDHeader from '@/Components/HUDHeader.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profil — CityPlay" />

    <SiteLayout isHUD>
        <HUDHeader />
        <div class="py-12 pb-28 md:pb-12 relative z-10">
            <div class="mx-auto max-w-5xl space-y-12 px-6">
                <div class="text-center mb-12">
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <div class="h-px w-12 bg-primary/30" />
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse" />
                        <div class="h-px w-12 bg-primary/30" />
                    </div>
                    <h1 class="font-display text-4xl md:text-6xl font-black uppercase italic tracking-tighter text-white leading-none">
                        CENTRE DE <span class="text-primary drop-shadow-[0_0_15px_#06b6d4]">COMMANDE</span>
                    </h1>
                    <div class="text-[10px] font-black tracking-[0.5em] text-white/40 uppercase mt-4">USER_PROFILE_CONFIGURATION // SECURE_ACCESS</div>
                </div>

                <div class="neon-border-box p-8 md:p-12 rounded-[2.5rem] relative">
                    <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-2xl mx-auto"
                    />
                </div>

                <div class="neon-border-box p-8 md:p-12 rounded-[2.5rem] relative">
                    <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent" />
                    <UpdatePasswordForm class="max-w-2xl mx-auto" />
                </div>

                <div class="hud-glass-card border-red-500/20 p-8 md:p-12 rounded-[2.5rem] relative">
                    <DeleteUserForm class="max-w-2xl mx-auto" />
                </div>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>
