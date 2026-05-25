<script setup>
import SiteLayout from '@/Layouts/SiteLayout.vue';
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

    <SiteLayout>
        <div class="py-12 pb-28 md:pb-12">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div class="px-4 sm:px-0">
                    <h1 class="font-display text-3xl md:text-5xl text-white">Paramètres du <span class="text-electric neon-text">Profil</span></h1>
                    <p class="mt-2 text-muted-foreground">Gérez vos informations tactiques et votre sécurité.</p>
                </div>

                <div class="glass-strong border border-white/5 p-6 shadow-2xl sm:rounded-3xl sm:p-10">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="glass-strong border border-white/5 p-6 shadow-2xl sm:rounded-3xl sm:p-10">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="glass-strong border border-destructive/10 p-6 shadow-2xl sm:rounded-3xl sm:p-10">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
        <MobileTabBar />
    </SiteLayout>
</template>
