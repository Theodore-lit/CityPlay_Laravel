<script setup>
import InputError from '@/Components/InputError.vue';
import HUDButton from '@/Components/HUDButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Save, User, Mail, ShieldAlert } from 'lucide-vue-next';

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
        <header class="mb-10">
            <h2 class="font-display text-2xl font-black text-white uppercase italic tracking-tight flex items-center gap-3">
                <User class="h-6 w-6 text-primary" />
                IDENTITÉ TACTIQUE
            </h2>
            <p class="mt-2 text-[10px] font-black tracking-widest text-white/40 uppercase">MISE À JOUR DES PARAMÈTRES D'IDENTIFICATION</p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-10"
        >
            <div class="grid gap-8">
                <GlowInput
                    id="name"
                    label="NOM D'UTILISATEUR"
                    v-model="form.name"
                    :error="form.errors.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <GlowInput
                    id="email"
                    type="email"
                    label="ADRESSE_LIAISON_DATA"
                    v-model="form.email"
                    :error="form.errors.email"
                    required
                    autocomplete="username"
                />
            </div>

            <div class="space-y-6 border-t border-white/5 pt-10">
                <h3 class="font-display text-sm font-black text-primary uppercase italic tracking-widest flex items-center gap-2">
                    <ShieldAlert class="h-4 w-4" />
                    PROTOCOLES DE SÉCURITÉ
                </h3>
                
                <div class="hud-glass-card p-6 rounded-2xl border border-white/5 group hover:border-primary/20 transition-all">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center h-6">
                            <input
                                id="deactivate_on_logout"
                                type="checkbox"
                                v-model="form.deactivate_on_logout"
                                class="h-5 w-5 rounded bg-zinc-900 border-white/20 text-primary focus:ring-primary focus:ring-offset-zinc-900 transition-all"
                            />
                        </div>
                        <div class="flex-1">
                            <label for="deactivate_on_logout" class="block font-black text-[11px] text-white uppercase tracking-widest mb-1 cursor-pointer">
                                AUTO_DEACTIVATE_MODE
                            </label>
                            <p class="text-white/40 text-[9px] font-bold uppercase leading-relaxed tracking-widest">
                                Si activé, votre compte sera invisible lors de la déconnexion. Protection furtive active.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-500 text-[9px] font-black uppercase tracking-widest">
                    ALERTE : ADRESSE EMAIL NON VÉRIFIÉE.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="ml-2 underline hover:text-amber-400"
                    >
                        RENVOYER_LE_LIEN
                    </Link>
                </div>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-4 text-xs font-black text-green-500 uppercase tracking-widest"
                >
                    LIEN_DE_VÉRIFICATION_EXPÉDIÉ.
                </div>
            </div>

            <div class="flex items-center gap-6 pt-4">
                <HUDButton :disabled="form.processing" variant="primary" class="h-12 px-8">
                    <div class="flex items-center gap-2">
                        <Save class="h-4 w-4" />
                        <span>SAUVEGARDER_DATA</span>
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
                        TRANSFERT_RÉUSSI
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
