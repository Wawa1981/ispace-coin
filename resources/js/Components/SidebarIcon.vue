<template>
    <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        :class="isLight ? 'text-black/80' : 'text-white/90'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <template v-if="icon === 'home'"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></template>
        <template v-else-if="icon === 'hex'"><path d="M12 3l7 4v10l-7 4-7-4V7z"/></template>
        <template v-else-if="icon === 'orbit'"><circle cx="12" cy="12" r="4"/><path d="M3 12c3-6 15-6 18 0M3 12c3 6 15 6 18 0"/></template>
        <template v-else-if="icon === 'spark'"><path d="M4 16l6-8 4 5 6-9"/><circle cx="5" cy="17" r="1"/><circle cx="18" cy="5" r="1"/></template>
        <template v-else-if="icon === 'stack'"><path d="M4 10l8-4 8 4-8 4-8-4z"/><path d="M4 14l8 4 8-4"/></template>
        <template v-else-if="icon === 'flow'"><path d="M4 8c6 0 6 8 12 8h4"/><path d="M20 16l-2-2 2-2"/></template>
        <template v-else-if="icon === 'id'"><rect x="6" y="6" width="12" height="12" rx="2"/><circle cx="12" cy="11" r="2"/><path d="M9 15h6"/></template>
        <template v-else-if="icon === 'cards'"><rect x="7" y="7" width="10" height="7" rx="1.6"/><rect x="5" y="10" width="10" height="7" rx="1.6"/></template>
        <template v-else-if="icon === 'pulse'"><path d="M3 12h4l2-3 3 6 2-3h7"/><circle cx="6" cy="12" r="1"/></template>
    </svg>
</template>

<script setup>

defineProps({
    icon: {
        type: String,
        required: true,
    },
    isLight: {
        type: Boolean,
        default: false,
    }
});
</script>
