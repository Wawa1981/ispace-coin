<template>
  <a :href="route(routeName)"
     class="flex flex-col items-center justify-center bg-gray-800 hover:bg-gray-700 text-white p-4 rounded-xl shadow transition transform hover:scale-105 group">
    <div class="text-3xl">{{ icon }}</div>
    <div class="mt-2 text-sm opacity-0 group-hover:opacity-100 transition">{{ label }}</div>
  </a>
</template>

<script setup>
defineProps({
  label: String,
  icon: String,
  routeName: String
});

const route = (name) => window.route(name);
</script>
