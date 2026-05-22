<script setup>
// kamal
import SiteLayout from '@/Layouts/SiteLayout.vue';
import MobileTabBar from '@/Components/MobileTabBar.vue';
import NeonButton from '@/Components/NeonButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    Gift, Package, CheckCircle2, Download, QrCode, X, Trophy, Sparkles, Star, ArrowLeft, Archive, Award
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { ref, nextTick } from 'vue';
import gsap from 'gsap';
import confetti from 'canvas-confetti';
import { jsPDF } from 'jspdf';
import QRCode from 'qrcode';

const props = defineProps({
    prizes: {
        type: Array,
        default: () => [],
    },
    stats: Object,
});

// Logique d'ouverture de lot (Prizes System)
// Cette section gère la transition visuelle entre la boîte fermée et le contenu révélé
const openingPrize = ref(null);
const showOpeningModal = ref(false);
const isOpening = ref(false);
const prizeOpened = ref(false);

const openPrizeModal = (prize) => {
    openingPrize.value = prize;
    showOpeningModal.value = true;
    prizeOpened.value = prize.is_opened;
};

/**
 * Déclenche la séquence d'animation GSAP pour l'ouverture du lot.
 * On utilise une timeline pour synchroniser les secousses, l'agrandissement et l'explosion.
 */
const triggerOpeningAnimation = () => {
    if (isOpening.value) return;
    isOpening.value = true;
    
    // Séquence GSAP Premium : Secousses suivies d'une disparition en flou
    const tl = gsap.timeline();
    
    tl.to(".prize-box-container", {
        scale: 1.1,
        duration: 0.4,
        ease: "back.out(1.7)"
    })
    .to(".prize-box", {
        x: -10,
        rotate: -5,
        duration: 0.05,
        repeat: 10,
        yoyo: true
    })
    .to(".prize-box", {
        y: -50,
        scale: 1.3,
        opacity: 0,
        filter: "blur(10px)",
        duration: 0.6,
        ease: "power4.in",
        onComplete: () => {
            // Appel API uniquement APRES la fin de l'animation visuelle
            sendOpenRequest();
        }
    });
};

/**
 * Envoie la requête de validation d'ouverture au serveur.
 * Désactive le loader global d'Inertia pour garder le contrôle sur l'UI gaming.
 */
const sendOpenRequest = () => {
    useForm({}).post(route('player.prizes.open', openingPrize.value.id), {
        onSuccess: () => {
            // On attend que le serveur confirme avant de changer d'état
            isOpening.value = false;
            prizeOpened.value = true;
            
            // Effets de victoire : Confettis et animation d'apparition du lot
            confetti({
                particleCount: 200,
                spread: 90,
                origin: { y: 0.5 },
                colors: ['#0070ff', '#00f2ff', '#ffffff', '#ffd700']
            });

            // Animation d'apparition du contenu débloqué (élastique)
            nextTick(() => {
                gsap.from(".revealed-content", {
                    y: 40,
                    opacity: 0,
                    scale: 0.8,
                    duration: 0.8,
                    ease: "elastic.out(1, 0.5)"
                });
            });
        },
        // IMPORTANT: Désactivation du loader global pour éviter l'interruption visuelle
        showProgress: false,
        preserveScroll: true
    });
};

// Génération PDF + QR Code
const generateAndDownloadPDF = async (prize) => {
    const doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
    });
    
    const qrData = prize.qr_code_data || `CITYPLAY-REWARD-${prize.id}`;
    const qrDataUrl = await QRCode.toDataURL(qrData, {
        margin: 1,
        color: {
            dark: '#000000',
            light: '#ffffff'
        }
    });

    // Fond Stylisé
    doc.setFillColor(15, 15, 25);
    doc.rect(0, 0, 210, 297, 'F');
    
    // Header
    doc.setTextColor(0, 112, 255);
    doc.setFontSize(28);
    doc.setFont('helvetica', 'bold');
    doc.text("CITYPLAY", 105, 40, { align: "center" });
    
    doc.setDrawColor(0, 112, 255);
    doc.setLineWidth(1);
    doc.line(40, 45, 170, 45);

    // Titre du Lot
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.text("BON DE RÉCOMPENSE", 105, 65, { align: "center" });
    
    doc.setFontSize(16);
    doc.text(prize.title.toUpperCase(), 105, 80, { align: "center" });

    // Description
    doc.setFontSize(11);
    doc.setTextColor(160, 160, 160);
    const splitDesc = doc.splitTextToSize(prize.description || "Félicitations pour votre victoire ! Présentez ce code pour retirer votre lot.", 140);
    doc.text(splitDesc, 105, 95, { align: "center" });

    // QR Code
    doc.setFillColor(255, 255, 255);
    doc.roundedRect(50, 110, 110, 110, 5, 5, 'F');
    doc.addImage(qrDataUrl, 'PNG', 55, 115, 100, 100);

    // Footer
    doc.setFontSize(10);
    doc.setTextColor(0, 112, 255);
    doc.text(`CODE DE SÉCURITÉ : ${qrData}`, 105, 235, { align: "center" });
    
    doc.setTextColor(100, 100, 100);
    doc.setFontSize(9);
    doc.text("Ce document est personnel et unique. Ne le partagez pas.", 105, 250, { align: "center" });
    doc.text("Généré le " + new Date().toLocaleString('fr-FR'), 105, 258, { align: "center" });

    doc.save(`CityPlay_Reward_${prize.id}.pdf`);
};

// Style "eau de roche" purifié
const glassClass = "bg-white/5 backdrop-blur-2xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)]";
</script>

<template>
    <Head title="Mon Coffre — CityPlay" />

    <SiteLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 pb-28 md:pb-12 relative z-10">
            <!-- Header Tactique -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16 animate-in fade-in slide-in-from-left-4 duration-700">
                <div class="flex items-center gap-6">
                    <Link :href="route('player.hub')" :class="cn('h-12 w-12 rounded-2xl grid place-items-center text-primary hover:scale-110 transition-all', glassClass)">
                        <ArrowLeft class="h-6 w-6" />
                    </Link>
                    <div>
                        <div class="text-[10px] text-primary uppercase tracking-[0.4em] font-black mb-1 neon-text">Inventory System</div>
                        <h1 class="font-display text-4xl md:text-5xl text-foreground uppercase italic font-black">Mon <span class="text-white/40">Coffre</span></h1>
                    </div>
                </div>

                <div :class="cn('px-8 py-4 rounded-[2rem] flex items-center gap-6', glassClass)">
                    <div class="text-center">
                        <p class="text-[8px] font-black text-muted-foreground uppercase tracking-widest mb-1">Total Obtenu</p>
                        <p class="text-xl font-display font-black text-foreground">{{ prizes?.length || 0 }}</p>
                    </div>
                    <div class="h-8 w-px bg-white/10" />
                    <div class="text-center">
                        <p class="text-[8px] font-black text-muted-foreground uppercase tracking-widest mb-1">À Ouvrir</p>
                        <p class="text-xl font-display font-black text-amber-400">{{ prizes?.filter(p => !p.is_opened).length || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Grille des Lots -->
            <div v-if="prizes?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
                <div v-for="prize in prizes" :key="prize.id" 
                     :class="cn('group relative p-8 rounded-[3rem] overflow-hidden transition-all duration-500 hover:border-primary/40', glassClass)">
                    
                    <!-- Badge Statut -->
                    <div class="absolute top-6 right-8">
                        <div v-if="!prize.is_opened" class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/30 text-[8px] font-black text-amber-500 uppercase tracking-widest animate-pulse">
                            <Sparkles class="h-2.5 w-2.5" /> Nouveau
                        </div>
                        <div v-else class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[8px] font-black text-emerald-400 uppercase tracking-widest">
                            <CheckCircle2 class="h-2.5 w-2.5" /> Archivé
                        </div>
                    </div>

                    <!-- Icone Centrale -->
                    <div :class="cn('h-24 w-24 mx-auto rounded-[2.5rem] grid place-items-center mb-8 transition-all duration-700 group-hover:scale-110 group-hover:rotate-3 relative', 
                        prize.is_opened ? 'bg-white/5 text-white/20' : 'bg-primary/10 text-primary shadow-neon shadow-primary/20'
                    )">
                        <Gift v-if="!prize.is_opened" class="h-10 w-10" />
                        <Archive v-else class="h-10 w-10" />
                        
                        <!-- Glow effect pour les non-ouverts -->
                        <div v-if="!prize.is_opened" class="absolute inset-0 rounded-[2.5rem] bg-primary/20 blur-xl animate-pulse" />
                    </div>

                    <!-- Infos Lot -->
                    <div class="text-center space-y-3 mb-10">
                        <h3 class="font-display text-xl text-foreground uppercase italic font-black tracking-tight">{{ prize.title }}</h3>
                        <p class="text-[10px] text-muted-foreground leading-relaxed italic px-4 line-clamp-2">{{ prize.description }}</p>
                    </div>

                    <!-- Bouton Action -->
                    <div class="relative z-10">
                        <NeonButton v-if="!prize.is_opened" @click="openPrizeModal(prize)" variant="primary" class="w-full h-14 text-[10px] tracking-[0.2em] shadow-neon">
                            OUVRIR LE LOT
                        </NeonButton>
                        <div v-else class="flex gap-3">
                            <button @click="openPrizeModal(prize)" :class="cn('flex-1 h-14 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-white/10 transition-all', glassClass)">
                                INFOS
                            </button>
                            <button @click="generateAndDownloadPDF(prize)" class="h-14 w-14 rounded-2xl bg-primary/10 border border-primary/20 grid place-items-center text-primary hover:bg-primary hover:text-white transition-all shadow-neon-sm">
                                <Download class="h-6 w-6" />
                            </button>
                        </div>
                    </div>

                    <!-- Background Decor -->
                    <div :class="cn('absolute -bottom-10 -right-10 h-32 w-32 rounded-full blur-3xl opacity-10 transition-colors', 
                        prize.is_opened ? 'bg-white' : 'bg-primary'
                    )" />
                </div>
            </div>

            <!-- État Vide -->
            <div v-else class="max-w-2xl mx-auto text-center py-24 px-8 rounded-[4rem] border-2 border-dashed border-white/5 animate-in fade-in zoom-in duration-1000">
                <div class="h-24 w-24 mx-auto bg-white/5 rounded-full grid place-items-center mb-8">
                    <Package class="h-10 w-10 text-white/10" />
                </div>
                <h3 class="font-display text-2xl text-white/40 uppercase italic font-black mb-4 tracking-widest">Votre coffre est vide</h3>
                <p class="text-muted-foreground text-sm leading-relaxed mb-10 italic">
                    Participez à des compétitions et atteignez vos objectifs pour débloquer des lots exclusifs. L'aventure vous attend !
                </p>
                <Link :href="route('player.competitions')">
                    <NeonButton variant="primary" class="px-12 h-14 shadow-neon">EXPLORER LES DÉFIS</NeonButton>
                </Link>
            </div>
        </div>

        <!-- MODAL OUVERTURE PREMIUM -->
        <div v-if="showOpeningModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gaming-dark/90 backdrop-blur-2xl" @click="!isOpening && (showOpeningModal = false)"></div>
            
            <div :class="cn('relative w-full max-w-xl p-10 md:p-16 rounded-[4rem] text-center overflow-hidden animate-in zoom-in-95 duration-500', glassClass)">
                <!-- Bouton Fermer -->
                <button v-if="!isOpening" @click="showOpeningModal = false" class="absolute top-8 right-8 h-10 w-10 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition-colors">
                    <X class="h-5 w-5" />
                </button>

                <div class="relative z-10">
                    <!-- Phase 1: Prêt à l'ouverture -->
                    <div v-if="!prizeOpened" class="prize-box-container">
                        <div class="prize-box h-48 w-48 mx-auto bg-gradient-to-br from-primary/20 to-primary/40 rounded-[3rem] border-2 border-primary/30 grid place-items-center mb-12 shadow-[0_0_50px_rgba(0,112,255,0.3)] relative">
                            <Gift class="h-20 w-20 text-primary animate-bounce-slow" />
                            <div class="absolute inset-0 rounded-[3rem] bg-primary/20 blur-2xl animate-pulse" />
                        </div>
                        
                        <div class="space-y-4 mb-12">
                            <h2 class="font-display text-3xl md:text-4xl text-white uppercase italic font-black tracking-tighter">Lot Non Ouvert</h2>
                            <p class="text-muted-foreground text-sm italic px-6">"{{ openingPrize.title }}" — Prêt pour le déploiement du contenu.</p>
                        </div>
                        
                        <NeonButton @click="triggerOpeningAnimation" :disabled="isOpening" variant="primary" class="w-full h-20 shadow-neon text-xl tracking-[0.3em]">
                            {{ isOpening ? 'DÉCRYPTAGE...' : 'OUVRIR LE LOT' }}
                        </NeonButton>
                    </div>

                    <!-- Phase 2: Contenu Révélé -->
                    <div v-else class="revealed-content animate-in fade-in zoom-in duration-1000">
                        <div class="h-48 w-48 mx-auto bg-emerald-500/20 rounded-[3rem] border-2 border-emerald-500/30 grid place-items-center mb-10 shadow-[0_0_50px_rgba(16,185,129,0.3)] relative">
                            <QrCode class="h-20 w-20 text-emerald-400" />
                            <div class="absolute -top-4 -right-4 h-12 w-12 bg-amber-400 rounded-2xl grid place-items-center text-gaming-darker shadow-neon-amber rotate-12">
                                <Award class="h-6 w-6" />
                            </div>
                        </div>

                        <div class="space-y-3 mb-10">
                            <h2 class="font-display text-4xl text-white uppercase italic font-black tracking-tighter">FÉLICITATIONS !</h2>
                            <p class="text-emerald-400 text-xs font-black uppercase tracking-[0.4em]">Protocole de Victoire Validé</p>
                        </div>
                        
                        <div class="p-8 rounded-[2.5rem] bg-white/5 border border-white/10 mb-12 relative overflow-hidden group">
                            <div class="relative z-10">
                                <p class="text-white text-lg font-display font-black uppercase mb-3 tracking-tight">{{ openingPrize.title }}</p>
                                <p class="text-muted-foreground text-xs leading-relaxed italic px-4">{{ openingPrize.description }}</p>
                            </div>
                            <div class="absolute -bottom-10 -left-10 h-32 w-32 bg-emerald-500/5 rounded-full blur-3xl" />
                        </div>

                        <NeonButton @click="generateAndDownloadPDF(openingPrize)" variant="primary" class="w-full h-20 shadow-neon text-xl tracking-[0.3em]">
                            <Download class="h-7 w-7 mr-4" /> RETIRER (PDF)
                        </NeonButton>
                        
                        <p class="text-[9px] text-muted-foreground mt-6 uppercase font-bold tracking-widest opacity-50">
                            Code Unique : {{ openingPrize.qr_code_data || 'CP-REWARD-VALID' }}
                        </p>
                    </div>
                </div>

                <!-- Glow Background Effects -->
                <div class="absolute -top-40 -left-40 h-96 w-96 bg-primary/10 rounded-full blur-[120px] pointer-events-none" />
                <div class="absolute -bottom-40 -right-40 h-96 w-96 bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none" />
            </div>
        </div>

        <MobileTabBar />
    </SiteLayout>
</template>

<style scoped>
.animate-bounce-slow {
    animation: bounce-slow 3s infinite;
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

.shadow-neon-amber {
    box-shadow: 0 0 30px rgba(245, 158, 11, 0.4);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 112, 255, 0.2);
    border-radius: 10px;
}
</style>
