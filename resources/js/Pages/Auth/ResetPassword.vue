<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import GlowInput from '@/Components/GlowInput.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Lock, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout 
        title="Nouveau Mot de Passe" 
        subtitle="Sécurisez votre compte pour reprendre l'aventure."
    >
        <Head title="Réinitialisation — CityPlay" />

        <form @submit.prevent="submit" class="space-y-4">
            <GlowInput
                label="Email"
                type="email"
                v-model="form.email"
                placeholder="votre@aventure.bj"
                required
                autofocus
                autocomplete="username"
                :error="form.errors.email"
            />

            <GlowInput
                label="Mot de passe"
                type="password"
                v-model="form.password"
                placeholder="••••••••"
                required
                autocomplete="new-password"
                :error="form.errors.password"
            />

            <GlowInput
                label="Confirmer le mot de passe"
                type="password"
                v-model="form.password_confirmation"
                placeholder="••••••••"
                required
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
            />

            <div class="pt-2">
                <NeonButton
                    class="w-full"
                    :disabled="form.processing"
                >
                    Réinitialiser <Lock class="h-4 w-4 ml-2" />
                </NeonButton>
            </div>
        </form>
    </AuthLayout>
</template>
