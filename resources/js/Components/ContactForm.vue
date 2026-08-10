<template>
  <div class="flex-grow w-full bg-gray-800 rounded-xl shadow-xl p-6">
    <h2 class="text-xl font-semibold mb-4">Contact</h2>
    <p class="text-sm leading-relaxed opacity-90">
      Pour toute question, contactez-nous à support@traderapp.com
      <br><br>
      Ou via notre formulaire en ligne.
    </p>
    <form class="mt-6 space-y-4">
      <input type="text" placeholder="Votre nom" class="w-full px-4 py-2 bg-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      <input type="email" placeholder="Votre email" class="w-full px-4 py-2 bg-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      <textarea placeholder="Votre message" class="w-full px-4 py-2 bg-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 h-32"></textarea>
      <button type="submit" class="px-6 py-2 bg-blue-600 rounded-md hover:bg-blue-500 font-medium transition-colors">Envoyer</button>
    </form>
  </div>
</template>

<script setup>
// Pas de logique ici, le contenu est statique
</script>
