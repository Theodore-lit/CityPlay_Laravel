<script setup>
/**
 * Interface d'administration d'une ville (Mairie).
 * Permet la gestion des lieux, énigmes et récompenses.
 *
 * Kamal: Ajout des champs de récompenses XP par lieu.
 */
import SiteLayout from "@/Layouts/SiteLayout.vue";
import MobileTabBar from "@/Components/MobileTabBar.vue";
import NeonButton from "@/Components/NeonButton.vue";
import AppImage from "@/Components/AppImage.vue";
import GlowInput from "@/Components/GlowInput.vue";
import GpsSearchInput from "@/Components/GpsSearchInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    MapPin,
    Plus,
    Target,
    ChevronLeft,
    Map,
    Sparkles,
    HelpCircle,
    Save,
    Trash2,
    Image as ImageIcon,
    List,
    Settings,
    Info,
    Navigation,
    Brain,
    Edit2,
} from "lucide-vue-next";
import { ref } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps({
    city: Object,
    auth: Object,
});

const showLocationModal = ref(false);
const showImageModal = ref(false);
const selectedLocation = ref(null);
const showEnigmaModal = ref(false);
const showCityModal = ref(false);

/**
 * Formulaire de modification de la ville kamal
 */
const cityForm = useForm({
    name: props.city.name,
    description: props.city.description,
    latitude: props.city.latitude,
    longitude: props.city.longitude,
    radius_meters: props.city.radius_meters,
    image: null,
});

/**
 * Mise à jour des coordonnées lors de la sélection GPS kamal
 */
const onCitySelect = (data) => {
    cityForm.name = data.name;
    cityForm.latitude = data.lat;
    cityForm.longitude = data.lon;
};

/**
 * Soumission des modifications de la ville kamal
 */
const submitCity = () => {
    cityForm.post(route("mairie.cities.update", props.city.id), {
        forceFormData: true,
        onSuccess: () => {
            showCityModal.value = false;
        },
    });
};

/**
 * Formulaire de gestion d'un lieu kamal
 */
const locationForm = useForm({
    id: null,
    name: '',
    latitude: '',
    longitude: '',
    radius_meters: 5000,
    reward_xp_arrival: 0, // Kamal
    reward_xp_enigma: 0,  // Kamal
    description: '',
    history: '',
    category: 'culture',
    image: null,
});

/**
 * Soumission d'une énigme kamal
 */
const submitEnigma = () => {
    enigmaForm.post(route("mairie.enigmas.store", selectedLocation.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEnigmaModal.value = false;
            enigmaForm.reset();
        },
    });
};

/**
 * Sélection d'un lieu via la recherche GPS kamal
 */
const onLocationSelect = (data) => {
    locationForm.name = data.name;
    locationForm.latitude = data.lat;
    locationForm.longitude = data.lon;
};

/**
 * Gestion des images des lieux kamal
 */
const imageForm = useForm({
    image: null,
});

const openImageModal = (location) => {
    selectedLocation.value = location;
    imageForm.image = null;
    showImageModal.value = true;
};

const submitImage = () => {
    imageForm.post(route("mairie.locations.image", selectedLocation.value.id), {
        onSuccess: () => {
            showImageModal.value = false;
            imageForm.reset();
        },
    });
};

/**
 * Formulaire de gestion d'une énigme kamal
 */
const enigmaForm = useForm({
    id: null,
    title: "",
    content: "",
    difficulty: "easy",
    answer: "",
    indices: ["", ""],
    reward_coins: 10,
    reward_hearts: 0,
    type: "text",
    image: null,
    is_site_specific: false,
    questions: [], // Questions à choix multiples kamal
});

/**
 * Gestion dynamique des questions de l'énigme kamal
 */
const addQuestion = () => {
    enigmaForm.questions.push({
        question_text: "",
        options: [
            { option_text: "", is_correct: true },
            { option_text: "", is_correct: false },
        ],
    });
};

const removeQuestion = (index) => {
    enigmaForm.questions.splice(index, 1);
};

const addOption = (qIndex) => {
    enigmaForm.questions[qIndex].options.push({
        option_text: "",
        is_correct: false,
    });
};

const removeOption = (qIndex, oIndex) => {
    enigmaForm.questions[qIndex].options.splice(oIndex, 1);
};

const setCorrectOption = (qIndex, oIndex) => {
    enigmaForm.questions[qIndex].options.forEach((opt, idx) => {
        opt.is_correct = idx === oIndex;
    });
};

/**
 * Soumission du formulaire de lieu kamal
 */
const submitLocation = () => {
    const url = locationForm.id
        ? route("mairie.locations.update", locationForm.id)
        : route("mairie.locations.store", props.city.id);

    locationForm.post(url, {
        onSuccess: () => {
            showLocationModal.value = false;
            locationForm.reset();
        },
    });
};

/**
 * Ouvre le modal de création/édition de lieu kamal
 */
const openLocationModal = (location = null) => {
    if (location) {
        locationForm.id = location.id;
        locationForm.name = location.name;
        locationForm.description = location.description;
        locationForm.history = location.history || '';
        locationForm.category = location.category;
        locationForm.latitude = location.latitude;
        locationForm.longitude = location.longitude;
        locationForm.radius_meters = location.radius_meters;
        locationForm.reward_xp_arrival = location.reward_xp_arrival || 0;
        locationForm.reward_xp_enigma = location.reward_xp_enigma || 0;
    } else {
        locationForm.reset();
        locationForm.id = null;
    }
    showLocationModal.value = true;
};

/**
 * Ouvre le modal de création/édition d'énigme kamal
 */
const openEnigmaModal = (location, enigma = null) => {
    selectedLocation.value = location;
    if (enigma) {
        enigmaForm.id = enigma.id;
        enigmaForm.title = enigma.title;
        enigmaForm.content = enigma.content;
        enigmaForm.difficulty = enigma.difficulty || "easy";
        enigmaForm.answer = enigma.answer;
        enigmaForm.indices =
            enigma.indices && enigma.indices.length > 0
                ? [...enigma.indices]
                : ["", ""];
        enigmaForm.reward_coins = enigma.reward_coins;
        enigmaForm.reward_hearts = enigma.reward_hearts;
        enigmaForm.type = enigma.type;
        enigmaForm.is_site_specific = enigma.is_site_specific;
        enigmaForm.image = null;

        // Chargement des questions kamal
        if (enigma.questions && enigma.questions.length > 0) {
            enigmaForm.questions = enigma.questions.map((q) => ({
                question_text: q.question_text,
                options: q.options.map((o) => ({
                    option_text: o.option_text,
                    is_correct: !!o.is_correct,
                })),
            }));
        } else {
            enigmaForm.questions = [];
        }
    } else {
        enigmaForm.reset();
        enigmaForm.id = null;
        enigmaForm.indices = ["", ""];
        enigmaForm.questions = [];
    }
    showEnigmaModal.value = true;
};

const addIndex = () => {
    enigmaForm.indices.push("");
};

const removeIndex = (index) => {
    enigmaForm.indices.splice(index, 1);
};

console.log(props.city)
</script>
