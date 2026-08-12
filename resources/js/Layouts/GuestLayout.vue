<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import SiteFooter from '@/Components/SiteFooter.vue'
import { Link } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'

const { isLight } = useTheme()
</script>

<template>
  <div class="flex min-h-screen flex-col bg-gray-100">
    <div class="flex flex-1 flex-col items-center pt-6 sm:justify-center sm:pt-0">
      <div>
        <Link href="/">
          <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
        </Link>
      </div>

      <div
        class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
      >
        <slot />
      </div>
    </div>
    <SiteFooter :is-light="isLight" :reveal="true" :home-links="true" />
  </div>
</template>
