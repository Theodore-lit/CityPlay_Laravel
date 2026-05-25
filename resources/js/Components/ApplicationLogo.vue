<template>
    <div class="flex flex-col items-center">
        <!-- Logo Image -->
        <div class="relative w-24 h-12 overflow-hidden">
            <img 
                src="https://raw.githubusercontent.com/TheodoreLima/cityplay-assets/main/logo.png" 
                alt="CityPlay Logo" 
                class="w-full h-full object-contain filter drop-shadow-sm"
                @error="(e) => e.target.src = 'https://placehold.co/200x100?text=CityPlay&bg=f97316&clr=ffffff'"
            />
        </div>
    </div>
</template>
