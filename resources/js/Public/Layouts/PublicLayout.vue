<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <PublicNavbar @show-help="mostrarAjuda" />

    <!-- Flash Messages -->
    <div
      v-if="showFlash && flashMessage"
      class="fixed top-20 right-4 z-50 animate-slide-in-right"
    >
      <div
        :class="alertClasses"
        class="px-6 py-4 rounded-lg shadow-xl min-w-80 max-w-md border-l-4"
        role="alert"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i :class="iconClass" class="text-xl"></i>
          </div>
          <div class="ml-3 flex-1">
            <div class="text-sm font-medium">
              {{ flashMessage }}
            </div>
          </div>
          <div class="ml-3 pl-3">
            <button
              @click="closeFlashMessage"
              class="inline-flex rounded-md p-1.5 hover:bg-black hover:bg-opacity-10 focus:outline-none transition-colors duration-200"
            >
              <i class="fas fa-times text-sm"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Breadcrumb -->
    <div v-if="showBreadcrumb" class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 py-3">
        <nav class="text-sm text-gray-600">
          <Link
            href="/"
            class="hover:text-yellow-600 transition-colors duration-300"
          >
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
            <strong>Navegação:</strong> Use os links no topo para navegar entre
            as páginas do sistema.
          </p>
          <p>
            <strong>Normas:</strong> Na página de consulta, você pode buscar
            normas por descrição, tipo, órgão ou data.
          </p>
          <p>
            <strong>Filtros:</strong> Use os filtros avançados para refinar sua
            busca.
          </p>
          <p>
            <strong>Especificações:</strong> Documentos que definem os
            requisitos mínimos para equipamentos utilizados nas atividades da
            Polícia Civil da Paraíba.
          </p>
          <p>
            <strong>Boletim:</strong> Pesquise boletins pela data de publicação,
            pelo número do boletim ou liste os boletins que contenham o seu
            nome.
          </p>
          <p>
            <strong>Administração:</strong> Acesso restrito para gestão do
            sistema.
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
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import PublicNavbar from '../Layouts/PublicNavbar.vue';
import PublicFooter from '../Layouts/PublicFooter.vue';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
  showBreadcrumb: {
    type: Boolean,
    default: false,
  },
  pageTitle: {
    type: String,
    default: '',
  },
});

// State
const showAjudaModal = ref(false);
const isNavigating = ref(false);
const showFlash = ref(false);

// Computed
const page = usePage();

// Sistema de Flash Messages
const flashMessage = computed(() => {
  // Acessar page.props diretamente
  return (
    page.props.flash?.error ||
    page.props.flash?.success ||
    page.props.flash?.message ||
    page.props.error ||
    null
  );
});

const alertType = computed(() => {
  // Acessar page.props diretamente
  if (page.props.flash?.error || page.props.error) return 'error';
  if (page.props.flash?.success) return 'success';
  if (page.props.flash?.warning) return 'warning';
  if (page.props.flash?.message) return 'info';
  return page.props.flash?.alert_type || 'info';
});

const alertClasses = computed(() => {
  const base = 'bg-white shadow-lg';
  switch (alertType.value) {
    case 'error':
      return base + ' border-red-500 text-red-800';
    case 'success':
      return base + ' border-green-500 text-green-800';
    case 'warning':
      return base + ' border-yellow-500 text-yellow-800';
    default:
      return base + ' border-blue-500 text-blue-800';
  }
});

const iconClass = computed(() => {
  switch (alertType.value) {
    case 'error':
      return 'fas fa-exclamation-triangle text-red-600';
    case 'success':
      return 'fas fa-check-circle text-green-600';
    case 'warning':
      return 'fas fa-exclamation-circle text-yellow-600';
    default:
      return 'fas fa-info-circle text-blue-600';
  }
});

// Methods
const mostrarAjuda = () => {
  showAjudaModal.value = true;
};

const closeFlashMessage = () => {
  showFlash.value = false;
};

// Mostrar flash message automaticamente
watch(
  flashMessage,
  newMessage => {
    if (newMessage) {
      showFlash.value = true;
      // Auto-dismiss após 5 segundos
      setTimeout(() => {
        showFlash.value = false;
      }, 5000);
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.animate-scale-in {
  animation: scaleIn 0.2s ease-out;
}

.animate-slide-in-right {
  animation: slideInRight 0.3s ease-out;
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
