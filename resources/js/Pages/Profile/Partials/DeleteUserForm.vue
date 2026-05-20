<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

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
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-white">
                Supprimer le compte
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Avant de supprimer votre compte, veuillez télécharger toutes les données ou informations que vous souhaitez conserver.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion" class="rounded-xl font-bold uppercase tracking-widest text-xs">Supprimer définitivement</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 bg-gaming-darker border border-destructive/20 rounded-[2.5rem] relative overflow-hidden">
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                
                <h2 class="text-2xl font-display text-white mb-4 relative z-10">
                    Confirmer la suppression ?
                </h2>

                <p class="text-sm text-muted-foreground mb-8 leading-relaxed relative z-10">
                    Cette action est irréversible. Toutes vos données, XP et badges seront perdus. Veuillez entrer votre mot de passe pour confirmer.
                </p>

                <div class="space-y-2 relative z-10">
                    <InputLabel
                        for="password"
                        value="Mot de passe"
                        class="text-white"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full bg-gaming-darker border-white/10 text-white focus:border-destructive focus:ring-destructive"
                        placeholder="Votre mot de passe"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-8 flex justify-end gap-3 relative z-10">
                    <SecondaryButton @click="closeModal" class="rounded-xl border-white/10 text-white hover:bg-white/5">
                        Annuler
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3 rounded-xl font-bold uppercase tracking-widest text-xs"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirmer la suppression
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
