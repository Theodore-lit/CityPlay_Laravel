<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, LogOut } from 'lucide-vue-next';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout 
        title="Vérification" 
        subtitle="Merci de vous être inscrit ! Avant de commencer, veuillez vérifier votre adresse email."
    >
        <Head title="Vérification Email — CityPlay" />

        <div
            v-if="verificationLinkSent"
            class="mb-6 p-4 rounded-xl bg-success/10 border border-success/20 text-sm font-medium text-success text-center"
        >
            Un nouveau lien de vérification a été envoyé à l'adresse email fournie lors de l'inscription.
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-col gap-4">
                <NeonButton
                    class="w-full"
                    :disabled="form.processing"
                >
                    Renvoyer l'Email <Mail class="h-4 w-4 ml-2" />
                </NeonButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs text-muted-foreground hover:text-white underline text-center flex items-center justify-center gap-2 transition-colors"
                >
                    <LogOut class="h-3 w-3" /> Se déconnecter
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
