<script setup>
import HUDButton from '@/Components/HUDButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { KeyRound, Lock, ShieldCheck } from 'lucide-vue-next';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="mb-10">
            <h2 class="font-display text-2xl font-black text-white uppercase italic tracking-tight flex items-center gap-3">
                <KeyRound class="h-6 w-6 text-primary" />
                CRYPTO_KEY_UPDATE
            </h2>
            <p class="mt-2 text-[10px] font-black tracking-widest text-white/40 uppercase">ROTATION DES CLÉS DE SÉCURITÉ</p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-10">
            <div class="grid gap-8">
                <GlowInput
                    id="current_password"
                    type="password"
                    label="CLE_ACTUELLE"
                    v-model="form.current_password"
                    :error="form.errors.current_password"
                    autocomplete="current-password"
                />

                <GlowInput
                    id="password"
                    type="password"
                    label="NOUVELLE_CLE"
                    v-model="form.password"
                    :error="form.errors.password"
                    autocomplete="new-password"
                />

                <GlowInput
                    id="password_confirmation"
                    type="password"
                    label="CONFIRMATION_CLE"
                    v-model="form.password_confirmation"
                    :error="form.errors.password_confirmation"
                    autocomplete="new-password"
                />
            </div>

            <div class="flex items-center gap-6 pt-4">
                <HUDButton :disabled="form.processing" variant="primary" class="h-12 px-8">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="h-4 w-4" />
                        <span>METTRE_À_JOUR_SÉCURITÉ</span>
                    </div>
                </HUDButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-[10px] font-black text-green-500 uppercase tracking-[0.3em] animate-pulse"
                    >
                        SÉCURITÉ_RENFORCÉE
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
