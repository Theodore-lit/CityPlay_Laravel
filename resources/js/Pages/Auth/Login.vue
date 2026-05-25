<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import GlowInput from '@/Components/GlowInput.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Lock, Mail } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout 
        title="Heureux de vous revoir" 
        subtitle="Connectez-vous pour continuer votre aventure."
    >
        <Head title="Connexion — CityPlay" />

        <div v-if="status" class="mb-4 text-sm font-medium text-success text-center bg-success/10 py-2 rounded-lg">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
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

            <div class="space-y-2">
                <GlowInput
                    label="Mot de passe"
                    type="password"
                    v-model="form.password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                    :error="form.errors.password"
                />
                
                <div v-if="canResetPassword" class="flex justify-end">
                    <Link
                        :href="route('password.request')"
                        class="text-xs text-electric hover:underline transition-colors"
                    >
                        Mot de passe oublié ?
                    </Link>
                </div>
            </div>

            <div class="flex items-center">
                <label class="flex items-center cursor-pointer group">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        v-model="form.remember"
                        class="h-4 w-4 rounded border-electric/30 bg-gaming-darker text-electric focus:ring-electric focus:ring-offset-gaming-darker"
                    />
                    <span class="ms-2 text-xs text-muted-foreground group-hover:text-foreground transition-colors">Se souvenir de moi</span>
                </label>
            </div>

            <NeonButton
                class="w-full mt-2"
                :disabled="form.processing"
            >
                Entrer dans le jeu <ArrowRight class="h-4 w-4 ml-2" />
            </NeonButton>
        </form>

        <template #footer>
            Pas encore de compte ? 
            <Link :href="route('register')" class="text-electric font-bold hover:underline">
                Créer un compte
            </Link>
        </template>
    </AuthLayout>
</template>
