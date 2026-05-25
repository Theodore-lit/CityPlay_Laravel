<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    deactivate_on_logout: user.deactivate_on_logout,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-white">
                Informations du Profil
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Mettez à jour les informations de votre compte et votre adresse email.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Nom" class="text-white" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full bg-gaming-darker border-white/10 text-white focus:border-electric"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-white" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-gaming-darker border-white/10 text-white focus:border-electric"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-4 border-t border-white/5 pt-6">
                <h3 class="text-sm font-bold text-electric uppercase tracking-widest">Paramètres de Sécurité Tactique</h3>
                <div class="flex items-start gap-3">
                    <div class="flex items-center h-5">
                        <input
                            id="deactivate_on_logout"
                            type="checkbox"
                            v-model="form.deactivate_on_logout"
                            class="rounded bg-gaming-darker border-electric/30 text-electric shadow-sm focus:ring-electric"
                        />
                    </div>
                    <div class="text-sm">
                        <label for="deactivate_on_logout" class="font-medium text-white">Désactiver mon compte lors de la déconnexion</label>
                        <p class="text-muted-foreground text-xs">Si activé, votre compte sera invisible jusqu'à votre prochaine connexion. Sinon, la règle de désactivation après 12 mois d'inactivité s'appliquera.</p>
                    </div>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-200">
                    Votre adresse email n'est pas vérifiée.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-electric underline hover:text-electric/80 focus:outline-none focus:ring-2 focus:ring-electric"
                    >
                        Cliquez ici pour renvoyer l'email de vérification.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-success"
                >
                    Un nouveau lien de vérification a été envoyé à votre adresse email.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing" class="bg-electric hover:bg-electric/80 text-black font-bold">Enregistrer</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-muted-foreground"
                    >
                        Enregistré.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
