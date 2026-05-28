
<script setup>
import Modal from './Modal.vue';
import NeonButton from './NeonButton.vue';
import { AlertTriangle, HelpCircle, Info, ShieldAlert } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    title: String,
    message: String,
    variant: { type: String, default: 'purple' }, // 'purple', 'danger', 'electric', 'primary'
    confirmText: { type: String, default: 'Confirmer' }
});

defineEmits(['close', 'confirm']);

const config = computed(() => {
    switch (props.variant) {
        case 'danger':
            return {
                icon: ShieldAlert,
                colorClass: 'text-red-500',
                bgClass: 'bg-red-500/10',
                borderClass: 'border-red-500/20',
                glowClass: 'shadow-[0_0_50px_rgba(239,68,68,0.15)]'
            };
        case 'electric':
            return {
                icon: Info,
                colorClass: 'text-electric',
                bgClass: 'bg-electric/10',
                borderClass: 'border-electric/20',
                glowClass: 'shadow-neon'
            };
        case 'primary':
            return {
                icon: Info,
                colorClass: 'text-primary',
                bgClass: 'bg-primary/10',
                borderClass: 'border-primary/20',
                glowClass: 'shadow-neon'
            };
        default:
            return {
                icon: HelpCircle,
                colorClass: 'text-purple-neon',
                bgClass: 'bg-purple-neon/10',
                borderClass: 'border-purple-neon/20',
                glowClass: 'shadow-purple'
            };
    }
});
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="relative p-1 bg-gaming-darker rounded-[2.5rem] overflow-hidden">
            <!-- Top Gradient Border -->
            <div 
                class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r"
                :class="{
                    'from-red-500/0 via-red-500 to-red-500/0': variant === 'danger',
                    'from-electric/0 via-electric to-electric/0': variant === 'electric',
                    'from-purple-neon/0 via-purple-neon to-purple-neon/0': variant === 'purple' || !variant,
                }"
            ></div>

            <div class="p-8 md:p-10 relative overflow-hidden text-center">
                <!-- Background effects -->
                <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none" />
                <div 
                    class="absolute -top-24 left-1/2 -translate-x-1/2 w-48 h-48 rounded-full blur-[80px] pointer-events-none"
                    :class="config.bgClass"
                ></div>

                <div class="relative z-10">
                    <!-- Icon Container -->
                    <div 
                        class="mx-auto w-20 h-20 rounded-3xl flex items-center justify-center mb-8 border rotate-3 transition-transform hover:rotate-0 duration-500"
                        :class="[config.bgClass, config.borderClass, config.glowClass]"
                    >
                        <component 
                            :is="config.icon" 
                            class="h-10 w-10"
                            :class="config.colorClass"
                        />
                    </div>

                    <h2 class="font-display text-3xl text-white mb-4 uppercase italic tracking-tight">
                        {{ title }}
                    </h2>
                    
                    <p class="text-muted-foreground text-lg leading-relaxed max-w-md mx-auto mb-10 font-medium">
                        {{ message }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <button 
                            @click="$emit('close')" 
                            class="order-2 sm:order-1 w-full sm:w-auto px-8 py-3 text-sm font-black uppercase tracking-[0.2em] text-muted-foreground hover:text-white transition-all hover:bg-white/5 rounded-xl"
                        >
                            Annuler
                        </button>
                        <NeonButton 
                            class="order-1 sm:order-2 w-full sm:w-auto min-w-[160px] !h-14"
                            :variant="variant" 
                            @click="$emit('confirm')"
                        >
                            {{ confirmText }}
                        </NeonButton>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.shadow-neon-danger {
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
}
</style>