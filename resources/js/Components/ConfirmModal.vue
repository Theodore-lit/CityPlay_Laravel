
<script setup>
import Modal from './Modal.vue';
import NeonButton from './NeonButton.vue';

defineProps({
    show: Boolean,
    title: String,
    message: String,
    variant: { type: String, default: 'purple' }, // 'purple', 'danger', 'electric'
    confirmText: { type: String, default: 'Confirmer' }
});

defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-8 bg-gaming-darker border border-white/10 rounded-[2.5rem] relative overflow-hidden text-center">
            <!-- Background effect -->
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />

            <div class="relative z-10">
                <h2 class="font-display text-2xl text-white mb-4">{{ title }}</h2>
                <p class="text-muted-foreground mb-8">{{ message }}</p>

                <div class="flex gap-4 justify-center">
                    <button @click="$emit('close')" class="px-6 py-2 text-sm text-muted-foreground hover:text-white transition-colors">
                        Annuler
                    </button>
                    <NeonButton :variant="variant" @click="$emit('confirm')">
                        {{ confirmText }}
                    </NeonButton>
                </div>
            </div>
        </div>
    </Modal>
</template>