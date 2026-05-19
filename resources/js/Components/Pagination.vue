<script setup>
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-center gap-2 p-6 bg-white/5 border-t border-white/5">
        <template v-for="(link, k) in links" :key="k">
            <div
                v-if="link.url === null"
                class="h-10 px-4 rounded-xl glass border-white/5 flex items-center justify-center text-muted-foreground/40 text-xs font-bold pointer-events-none"
            >
                <ChevronLeft v-if="link.label.includes('Previous')" class="h-4 w-4" />
                <span v-else-if="!link.label.includes('Next')" v-html="link.label"></span>
                <ChevronRight v-if="link.label.includes('Next')" class="h-4 w-4" />
            </div>
            <Link
                v-else
                :href="link.url"
                :class="cn(
                    'h-10 px-4 rounded-xl transition-all flex items-center justify-center text-xs font-bold border',
                    link.active
                        ? 'bg-electric border-electric text-black shadow-neon shadow-electric/20'
                        : 'glass border-white/10 text-muted-foreground hover:border-electric hover:text-electric'
                )"
            >
                <ChevronLeft v-if="link.label.includes('Previous')" class="h-4 w-4" />
                <span v-else-if="!link.label.includes('Next')" v-html="link.label"></span>
                <ChevronRight v-if="link.label.includes('Next')" class="h-4 w-4" />
            </Link>
        </template>
    </div>
</template>
