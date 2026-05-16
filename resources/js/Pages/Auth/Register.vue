<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import GlowInput from '@/Components/GlowInput.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserPlus, ArrowRight } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout 
        title="Nouvelle Légende" 
        subtitle="Créez votre compte pour commencer votre quête au Bénin."
    >
        <Head title="Inscription — CityPlay" />

        <form @submit.prevent="submit" class="space-y-4">
            <GlowInput
                label="Nom complet"
                type="text"
                v-model="form.name"
                placeholder="Ex: Kossi Mensah"
                required
                autofocus
                autocomplete="name"
                :error="form.errors.name"
            />

            <GlowInput
                label="Email"
                type="email"
                v-model="form.email"
                placeholder="votre@aventure.bj"
                required
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
                    Rejoindre l'Aventure <UserPlus class="h-4 w-4 ml-2" />
                </NeonButton>
            </div>
        </form>

        <template #footer>
            Déjà inscrit ? 
            <Link :href="route('login')" class="text-electric font-bold hover:underline">
                Se connecter
            </Link>
        </template>
    </AuthLayout>
</template>
