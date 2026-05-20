<script setup>
import HUDButton from '@/Components/HUDButton.vue';
import GlowInput from '@/Components/GlowInput.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { AlertTriangle, Trash2, XCircle } from 'lucide-vue-next';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-10">
        <header class="mb-10">
            <h2 class="font-display text-2xl font-black text-red-500 uppercase italic tracking-tight flex items-center gap-3">
                <AlertTriangle class="h-6 w-6" />
                TERMINAISON_DE_COMPTE
            </h2>
            <p class="mt-2 text-[10px] font-black tracking-widest text-white/40 uppercase leading-relaxed">
                ATTENTION : TOUTES LES DONNÉES ET PROGRESSIONS SERONT DÉFINITIVEMENT EFFACÉES DU SECTEUR.
            </p>
        </header>

        <HUDButton @click="confirmUserDeletion" variant="magenta" class="h-12 border-red-500/40 text-red-500 hover:bg-red-500/10">
            <div class="flex items-center gap-2">
                <Trash2 class="h-4 w-4" />
                <span>SUPPRIMER_DÉFINITIVEMENT</span>
            </div>
        </HUDButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 md:p-12 bg-zinc-950 border-2 border-red-500/20 rounded-[2.5rem] relative overflow-hidden">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-red-500/40 to-transparent" />
                
                <h2 class="text-3xl font-display font-black text-white uppercase italic tracking-tighter mb-4 relative z-10">
                    CONFIRMER <span class="text-red-500">L'EFFACEMENT</span> ?
                </h2>

                <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-10 leading-relaxed relative z-10">
                    CETTE ACTION EST IRRÉVERSIBLE. VEUILLEZ SAISIR VOTRE CLÉ D'ACCÈS POUR VALIDER LA SUPPRESSION TOTALE DU PROFIL.
                </p>

                <div class="relative z-10">
                    <GlowInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        label="CLE_D_ACCES_SÉCURITÉ"
                        placeholder="MOT DE PASSE"
                        :error="form.errors.password"
                        @keyup.enter="deleteUser"
                    />
                </div>

                <div class="mt-12 flex flex-col sm:flex-row justify-end gap-4 relative z-10">
                    <button @click="closeModal" class="h-12 px-8 rounded-xl text-[10px] font-black text-white/40 uppercase tracking-widest hover:text-white transition-colors">
                        ANNULER_PROCÉDURE
                    </button>

                    <HUDButton
                        variant="magenta"
                        class="h-12 px-8"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        <div class="flex items-center gap-2">
                            <XCircle class="h-4 w-4" />
                            <span>CONFIRMER_EFFACEMENT_TOTAL</span>
                        </div>
                    </HUDButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
