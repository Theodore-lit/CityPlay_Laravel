<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import GlowInput from '@/Components/GlowInput.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserPlus, ArrowRight, ShieldCheck, RefreshCw } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    status: String,
    email: String,
});

const isOtpStep = computed(() => props.status === 'otp_sent');

const form = useForm({
    name: '',
    email: '',
    password: '',
    accepts_terms: false,
    password_confirmation: '',
});

const otpForm = useForm({
    email: '',
    otp_code: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const submitOtp = () => {
    otpForm.email = props.email;
    otpForm.post(route('otp.verify'));
};

const resendOtp = () => {
    const resendForm = useForm({ email: props.email });
    resendForm.post(route('otp.resend'));
};
</script>

<template>
    <AuthLayout 
        :title="isOtpStep ? 'Vérification' : 'Nouvelle Légende'" 
        :subtitle="isOtpStep ? 'Entrez le code OTP envoyé à votre adresse email.' : 'Créez votre compte pour commencer votre quête au Bénin.'"
    >
        <Head :title="isOtpStep ? 'Vérification OTP — CityPlay' : 'Inscription — CityPlay'" />

        <!-- Formulaire d'inscription -->
        <form v-if="!isOtpStep" @submit.prevent="submit" class="space-y-4">
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

        <!-- Formulaire OTP -->
        <form v-else @submit.prevent="submitOtp" class="space-y-6">
            <div class="text-center mb-6">
                <p class="text-gray-400 text-sm">
                    Un code à 6 chiffres a été envoyé à <br>
                    <span class="text-electric font-bold">{{ props.email }}</span>
                </p>
            </div>

            <GlowInput
                label="Code de vérification (OTP)"
                type="text"
                v-model="otpForm.otp_code"
                placeholder="000000"
                maxlength="6"
                required
                autofocus
                :error="otpForm.errors.otp_code"
                class="text-center tracking-widest text-2xl"
            />

            <div class="space-y-3">
                <NeonButton
                    class="w-full"
                    :disabled="otpForm.processing"
                >
                    Valider le code <ShieldCheck class="h-4 w-4 ml-2" />
                </NeonButton>

                <button 
                    type="button"
                    @click="resendOtp"
                    class="w-full flex items-center justify-center text-sm text-gray-400 hover:text-white transition-colors py-2"
                >
                    <RefreshCw class="h-3 w-3 mr-2" /> Renvoyer un nouveau code
                </button>
            </div>
        </form>

        <template #footer v-if="!isOtpStep">
            Déjà inscrit ? 
            <Link :href="route('login')" class="text-electric font-bold hover:underline">
                Se connecter
            </Link>
        </template>
    </AuthLayout>
</template>
