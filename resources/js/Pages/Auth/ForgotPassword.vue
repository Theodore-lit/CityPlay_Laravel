<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import GlowInput from '@/Components/GlowInput.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, ArrowRight } from 'lucide-vue-next';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout 
        title="Récupération" 
        subtitle="Entrez votre email pour recevoir un lien de réinitialisation."
    >
        <Head title="Mot de passe oublié — CityPlay" />

        <div
            v-if="status"
            class="mb-6 p-4 rounded-xl bg-success/10 border border-success/20 text-sm font-medium text-success text-center"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
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

            <NeonButton
                class="w-full"
                :disabled="form.processing"
            >
                Envoyer le lien <Mail class="h-4 w-4 ml-2" />
            </NeonButton>
        </form>
    </AuthLayout>
</template>
