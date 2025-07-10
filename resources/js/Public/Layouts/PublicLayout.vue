<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <PublicNavbar @show-help="mostrarAjuda" />

    <!-- Breadcrumb -->
    <div v-if="showBreadcrumb" class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 py-3">
        <nav class="text-sm text-gray-600">
          <Link href="/" class="hover:text-yellow-600 transition-colors duration-300">
            <i class="fas fa-home mr-1"></i>
            Início
          </Link>
          <span class="mx-2">/</span>
          <span class="text-gray-900 font-medium">{{ pageTitle }}</span>
        </nav>
      </div>
    </div>

    <!-- Progress Bar -->
    <div 
      v-show="$page.props.loading || isNavigating" 
      class="fixed top-0 left-0 right-0 z-50 h-1 bg-yellow-600 animate-pulse"
    ></div>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <PublicFooter :stats="stats" />

    <!-- Modal de Ajuda -->
    <div 
      v-if="showAjudaModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4"
      @click="showAjudaModal = false"
    >
      <div 
        class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 animate-scale-in"
        @click.stop
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-question-circle mr-2 text-blue-600"></i>
            Ajuda - Sistema de Normas
          </h3>
          <button 
            @click="showAjudaModal = false"
            class="text-gray-400 hover:text-gray-600 transition-colors duration-300"
          >
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <div class="space-y-4 text-gray-700">
          <p>
            <strong>Navegação:</strong> Use os links no topo para navegar entre as páginas do sistema.
          </p>
          <p>
            <strong>Consulta:</strong> Na página de consulta, você pode buscar normas por descrição, tipo, órgão ou data.
          </p>
          <p>
            <strong>Filtros:</strong> Use os filtros avançados para refinar sua busca.
          </p>
          <p>
            <strong>Especificações Técnicas:</strong> Documentos que definem os requisitos mínimos para equipamentos utilizados nas atividades da Polícia Civil da Paraíba.
          </p>
          <p>
            <strong>Área Administrativa:</strong> Acesso restrito para gestão do sistema.
          </p>
        </div>
        
        <div class="mt-6 flex justify-end">
          <button 
            @click="showAjudaModal = false"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-300"
          >
            Entendi
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import PublicNavbar from '../Layouts/PublicNavbar.vue'
import PublicFooter from '../Layouts/PublicFooter.vue'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  showBreadcrumb: {
    type: Boolean,
    default: false
  },
  pageTitle: {
    type: String,
    default: ''
  }
})

// State
const showAjudaModal = ref(false)
const isNavigating = ref(false)

// Computed
const page = usePage()

const isCurrentPage = (pageName) => {
  return page.props.page === pageName
}

// Methods
const mostrarAjuda = () => {
  showAjudaModal.value = true
}
</script>

<style scoped>
.animate-scale-in {
  animation: scaleIn 0.2s ease-out;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}
</style>