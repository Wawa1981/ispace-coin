<template>
  <div class="flex justify-center gap-8 mb-6">
    <button v-for="tab in tabs" :key="tab.value"
      @click="$emit('update:tab', tab.value)"
      :class="['px-5 py-2.5 rounded-md font-medium transition-colors shadow-sm',
        currentTab === tab.value ? 'bg-blue-600 text-white' : 'bg-gray-800 hover:bg-gray-700']">
      {{ tab.label }}
    </button>
  </div>
</template>

<script setup>

const props = defineProps({
  tabs: {
    type: Array,
    required: true
  },
  currentTab: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['update:tab']);
</script>
