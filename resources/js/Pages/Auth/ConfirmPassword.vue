<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import GlowInput from '@/Components/GlowInput.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout 
        title="Zone Sécurisée" 
        subtitle="Veuillez confirmer votre mot de passe pour continuer."
    >
        <Head title="Confirmation — CityPlay" />

        <form @submit.prevent="submit" class="space-y-6">
            <GlowInput
                label="Mot de passe"
                type="password"
                v-model="form.password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
                autofocus
                :error="form.errors.password"
            />

            <NeonButton
                class="w-full"
                :disabled="form.processing"
            >
                Confirmer l'Identité <ShieldCheck class="h-4 w-4 ml-2" />
            </NeonButton>
        </form>
    </AuthLayout>
</template>
