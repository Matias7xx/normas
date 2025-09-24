<template>
  <PublicLayout :stats="pageStats">
    <Head title="Consulta de Normas" />

    <!-- Header da página -->
    <section class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center">
          <h1 class="text-3xl md:text-4xl font-bold mb-4">
            Consulta de Normas
          </h1>
          <p class="text-xl text-blue-100 max-w-2xl mx-auto">
            Pesquise por descrição, resumo ou palavra-chave da norma
          </p>
        </div>
      </div>
    </section>

    <!-- Formulário de Busca -->
    <section class="bg-white shadow-lg -mt-6 relative z-10">
      <div class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
        <form @submit.prevent="buscarNormas" class="space-y-6">
          <!-- Busca por termo -->
          <div>
            <label for="search_term" class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-search mr-2 text-blue-600"></i>
              Termo de busca (descrição, resumo ou palavra-chave)
            </label>
            <input
              id="search_term"
              v-model="form.search_term"
              type="text"
              placeholder="Ex: portaria, regulamento, procedimento..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 text-sm sm:text-base"
            />
          </div>

          <!-- Filtros avançados -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tipo -->
            <div>
              <label for="tipo_id" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-layer-group mr-2 text-blue-600"></i>
                Tipo de Norma
              </label>
              <select
                id="tipo_id"
                v-model="form.tipo_id"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base"
              >
                <option value="">Todos os tipos</option>
                <option
                  v-for="tipo in (tipos || [])"
                  :key="tipo?.id || 'tipo-' + Math.random()"
                  :value="tipo?.id"
                >
                  {{ tipo?.tipo || 'Tipo não informado' }}
                </option>
              </select>
            </div>

            <!-- Órgão -->
            <div>
              <label for="orgao_id" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-building mr-2 text-blue-600"></i>
                Órgão
              </label>
              <select
                id="orgao_id"
                v-model="form.orgao_id"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base"
              >
                <option value="">Todos os órgãos</option>
                <option
                  v-for="orgao in (orgaos || [])"
                  :key="orgao?.id || 'orgao-' + Math.random()"
                  :value="orgao?.id"
                >
                  {{ orgao?.orgao || 'Órgão não informado' }}
                </option>
              </select>
            </div>

            <!-- Vigência -->
            <!-- <div>
              <label for="vigente" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>
                Vigência
              </label>
              <select
                id="vigente"
                v-model="form.vigente"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base"
              >
                <option value="">Todas</option>
                <option value="VIGENTE">Vigente</option>
                <option value="EM ANÁLISE">Em Análise</option>
                <option value="NÃO VIGENTE">Não Vigente</option>
              </select>
            </div> -->

            <!-- Botão Filtros de Data -->
            <div class="flex items-end">
              <button
                type="button"
                @click="mostrarFiltrosData = !mostrarFiltrosData"
                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 flex items-center justify-center text-sm sm:text-base"
              >
                <i class="fas fa-calendar mr-2"></i>
                <span class="hidden sm:inline">Filtrar por Data</span>
                <span class="sm:hidden">Data</span>
              </button>
            </div>
          </div>

          <!-- Filtros de data -->
          <div v-if="mostrarFiltrosData" class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg border">
            <div>
              <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                Data Início
              </label>
              <input
                id="data_inicio"
                v-model="form.data_inicio"
                type="date"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base"
              />
            </div>
            <div>
              <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                Data Fim
              </label>
              <input
                id="data_fim"
                v-model="form.data_fim"
                type="date"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base"
              />
            </div>
          </div>

          <!-- Botões de ação -->
          <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <button
              type="submit"
              :disabled="carregando"
              class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 sm:px-8 py-3 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md flex items-center justify-center text-sm sm:text-base"
            >
              <i v-if="carregando" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-search mr-2"></i>
              {{ carregando ? 'Consultando...' : 'Consultar' }}
            </button>

            <button
              type="button"
              @click="limparFiltros"
              class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 sm:px-8 py-3 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md flex items-center justify-center text-sm sm:text-base"
            >
              <i class="fas fa-times mr-2"></i>
              Limpar Filtros
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- Resultados -->
    <section class="max-w-7xl mx-auto px-4 py-8">
      <!-- Estatísticas da busca -->
      <div v-if="normas?.data && Array.isArray(normas.data)" class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center justify-between flex-wrap">
            <div class="text-blue-800">
              <i class="fas fa-info-circle mr-2"></i>
              <strong>{{ normas?.total || 0 }}</strong> norma(s) encontrada(s)
              <span v-if="temFiltrosAtivos" class="ml-2 text-sm">
                (com filtros aplicados)
              </span>
            </div>
            <div class="text-sm text-blue-600">
              Página {{ normas?.current_page || 1 }} de {{ normas?.last_page || 1 }}
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de normas -->
      <div v-if="normas?.data && Array.isArray(normas.data) && normas.data.length > 0" class="space-y-4 mb-8">
        <div
            v-for="norma in normas.data"
            :key="norma?.id || 'norma-' + Math.random()"
            class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-gray-200"
        >
            <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    {{ norma?.descricao || 'Descrição não informada' }}
                </h3>
                <p v-if="norma?.resumo" class="text-gray-700 mb-3 leading-relaxed">
                    {{ norma.resumo }}
                </p>
                </div>

                <!-- Status de vigência -->
                <!-- <div class="flex-shrink-0 ml-4">
                <span
                    :class="getVigenciaClass(norma?.vigente)"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap"
                >
                    <i :class="getVigenciaIcon(norma?.vigente)" class="mr-1"></i>
                    {{ norma?.vigente || 'N/A' }}
                </span>
                </div> -->
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">

                <!-- Órgão -->
                <div v-if="norma?.orgao?.orgao">
                <i class="fas fa-building mr-1 text-blue-600"></i>
                {{ norma.orgao.orgao }}
                </div>

                <!-- Data -->
                <div v-if="norma?.data">
                <i class="fas fa-calendar mr-1 text-blue-600"></i>
                {{ formatarData(norma.data) }}
                </div>

                <!-- Palavras-chave -->
                <!-- <div
                v-if="norma?.palavras_chave && Array.isArray(norma.palavras_chave) && norma.palavras_chave.length > 0"
                >
                <div class="flex items-start">
                    <i class="fas fa-tags mr-1 text-blue-600 flex-shrink-0 mt-0.5"></i>
                    <div class="flex flex-wrap gap-1 min-w-0">
                    <span
                        v-for="palavra in norma.palavras_chave"
                        :key="palavra?.id || 'palavra-' + Math.random()"
                        class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs whitespace-nowrap"
                    >
                        {{ palavra?.palavra_chave || 'Palavra-chave' }}
                    </span>
                    <span
                        v-if="(norma?.palavras_chave_restantes || 0) > 0"
                        class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs whitespace-nowrap"
                    >
                        +{{ norma.palavras_chave_restantes }}
                    </span>
                    </div>
                </div>
                </div> -->
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex space-x-3">
                <button
                    @click="visualizarNorma(norma?.id)"
                    :disabled="!norma?.id"
                    class="bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 hover:shadow-md active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-eye"></i>
                    Ver Detalhes
                </button>

                <button
                    v-if="norma?.anexo_url || norma?.anexo"
                    @click="baixarArquivo(norma?.id)"
                    class="bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
                >
                    <i class="fas fa-download"></i>
                    Baixar
                </button>
                </div>
            </div>
            </div>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="normas?.data && normas.data.length > 0 && normas.last_page > 1" class="flex flex-col items-center space-y-4">
        <!-- Navegação de páginas -->
        <nav aria-label="Navegação de páginas" class="w-full">
          <ul class="flex justify-center items-center space-x-1 flex-wrap gap-y-2">
            <!-- Primeira página -->
            <li v-if="normas.current_page > 1" class="hidden sm:block">
              <button
                @click="irParaPagina(1)"
                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 flex items-center"
                title="Primeira página"
              >
                <i class="fas fa-angle-double-left"></i>
              </button>
            </li>

            <!-- Página anterior -->
            <li v-if="normas.current_page > 1">
              <button
                @click="irParaPagina(normas.current_page - 1)"
                class="px-2 sm:px-3 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 flex items-center space-x-1"
              >
                <i class="fas fa-angle-left"></i>
                <span class="hidden sm:inline">Anterior</span>
              </button>
            </li>

            <!-- Páginas numeradas -->
            <li v-for="page in paginasVisiveisMobile" :key="page">
              <button
                v-if="page !== '...'"
                @click="irParaPagina(page)"
                :class="[
                  'px-2 sm:px-3 py-2 text-xs sm:text-sm rounded-md transition-all duration-200 min-w-[32px] sm:min-w-[36px]',
                  page === normas.current_page
                    ? 'bg-blue-600 text-white border border-blue-600 font-semibold'
                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-blue-50 hover:border-blue-300'
                ]"
              >
                {{ page }}
              </button>
              <span v-else class="px-2 py-2 text-xs sm:text-sm text-gray-500">...</span>
            </li>

            <!-- Próxima página -->
            <li v-if="normas.current_page < normas.last_page">
              <button
                @click="irParaPagina(normas.current_page + 1)"
                class="px-2 sm:px-3 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 flex items-center space-x-1"
              >
                <span class="hidden sm:inline">Próxima</span>
                <i class="fas fa-angle-right"></i>
              </button>
            </li>

            <!-- Última página -->
            <li v-if="normas.current_page < normas.last_page" class="hidden sm:block">
              <button
                @click="irParaPagina(normas.last_page)"
                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 flex items-center"
                title="Última página"
              >
                <i class="fas fa-angle-double-right"></i>
              </button>
            </li>
          </ul>
        </nav>

        <!-- Informações da paginação -->
        <div class="text-sm text-gray-600 text-center">
          Mostrando
          <span class="font-semibold">{{ ((normas.current_page - 1) * normas.per_page) + 1 }}</span>
          a
          <span class="font-semibold">{{ Math.min(normas.current_page * normas.per_page, normas.total) }}</span>
          de
          <span class="font-semibold">{{ normas.total }}</span>
          resultados
        </div>
      </div>

      <!-- Estado vazio -->
      <div v-else-if="normas?.data && Array.isArray(normas.data) && normas.data.length === 0" class="text-center py-8 sm:py-12">
        <div class="bg-gray-50 rounded-lg p-6 sm:p-8 mx-4 sm:mx-0">
          <i class="fas fa-search text-3xl sm:text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">
            Nenhuma norma encontrada
          </h3>
          <p class="text-gray-600 mb-6 text-sm sm:text-base">
            Tente ajustar os filtros ou usar termos diferentes na busca.
          </p>
          <button
            @click="limparFiltros"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 text-sm sm:text-base"
          >
            <i class="fas fa-refresh mr-2"></i>
            Nova Busca
          </button>
        </div>
      </div>

      <!-- Estado inicial -->
      <div v-else class="text-center py-8 sm:py-12">
        <div class="p-6 sm:p-8 mx-4 sm:mx-0">
          <i class="fas fa-search text-3xl sm:text-4xl text-blue-600 mb-4"></i>
          <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">
            Pronto para buscar
          </h3>
          <p class="text-gray-600 mb-4 text-sm sm:text-base">
            Use os filtros acima para encontrar as normas que você precisa.
          </p>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PublicLayout from '../Layouts/PublicLayout.vue'

const props = defineProps({
  tipos: {
    type: Array,
    default: () => []
  },
  orgaos: {
    type: Array,
    default: () => []
  },
  normas: {
    type: Object,
    default: () => null
  },
  filtros: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Estado do componente
const carregando = ref(false)
const mostrarFiltrosData = ref(false)

// Formulário de busca
const form = ref({
  search_term: props.filtros?.search_term || '',
  tipo_id: props.filtros?.tipo_id || '',
  orgao_id: props.filtros?.orgao_id || '',
  vigente: props.filtros?.vigente || '',
  data_inicio: props.filtros?.data_inicio || '',
  data_fim: props.filtros?.data_fim || ''
})

const temFiltrosAtivos = computed(() => {
  return !!(form.value.search_term || form.value.tipo_id || form.value.orgao_id ||
           form.value.vigente || form.value.data_inicio || form.value.data_fim)
})

const pageStats = computed(() => ({
  usuarios_ativos: props.stats?.usuarios_ativos || 0,
  normas_cadastradas: props.stats?.normas_cadastradas || 0,
  total_encontradas: props.normas?.total || 0
}))

const paginasVisiveisMobile = computed(() => {
  if (!props.normas || !props.normas.current_page || !props.normas.last_page) {
    return []
  }

  const current = props.normas.current_page
  const last = props.normas.last_page
  const pages = []

  // mobile
  const isMobile = window.innerWidth < 640

  if (isMobile) {
    // Em mobile, mostrar apenas 3 páginas no máximo
    const startPage = Math.max(1, current - 1)
    const endPage = Math.min(last, current + 1)

    for (let i = startPage; i <= endPage; i++) {
      pages.push(i)
    }
  } else {
    // Desktop
    if (current > 3) {
      pages.push(1)
      if (current > 4) pages.push('...')
    }

    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
      pages.push(i)
    }

    if (current < last - 2) {
      if (current < last - 3) pages.push('...')
      pages.push(last)
    }
  }

  return pages
})

// Métodos
const buscarNormas = () => {
  carregando.value = true

  // Adicionar parâmetro 'busca=1' para indicar que é uma busca ativa
  const params = { ...form.value, busca: 1 }

  router.get('/consulta', params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      carregando.value = false
    },
    onError: (errors) => {
      console.error('Erro na busca:', errors)
      carregando.value = false
    }
  })
}

const buscarTodasNormas = () => {
  carregando.value = true

  // Limpar todos os filtros e fazer busca
  form.value = {
    search_term: '',
    tipo_id: '',
    orgao_id: '',
    vigente: '',
    data_inicio: '',
    data_fim: ''
  }

  // Fazer busca com parâmetro indicando que é busca ativa
  router.get('/consulta', { busca: 1 }, {
    preserveState: true,
    onFinish: () => {
      carregando.value = false
    },
    onError: (errors) => {
      console.error('Erro na busca:', errors)
      carregando.value = false
    }
  })
}

const limparFiltros = () => {
  form.value = {
    search_term: '',
    tipo_id: '',
    orgao_id: '',
    vigente: '',
    data_inicio: '',
    data_fim: ''
  }

  router.get('/consulta', {}, {
    preserveState: true
  })
}

//Formatar data
const formatarData = (data) => {
  if (!data) return ''

  try {
    const dataObj = new Date(data)

    if (isNaN(dataObj.getTime())) {
      return data
    }

    return dataObj.toLocaleDateString('pt-BR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      timeZone: 'America/Sao_Paulo'
    })
  } catch (error) {
    console.error('Erro ao formatar data:', error)
    return data
  }
}

const irParaPagina = (pagina) => {
  if (pagina === '...' || pagina === props.normas?.current_page || !pagina) return

  const params = { ...form.value, page: pagina, busca: 1 }

  router.get('/consulta', params, {
    preserveState: true,
    preserveScroll: false // Permite scroll para o topo após mudança de página
  })
}

const visualizarNorma = (id) => {
  if (!id) {
    console.error('ID da norma não informado')
    return
  }
  router.get(`/norma/${id}`)
}

const baixarArquivo = (normaId) => {
  if (!normaId) {
    console.error('ID da norma não informado')
    return
  }

  // Criar um link temporário para forçar o download
  const link = document.createElement('a')
  link.href = `/norma/${normaId}/download`
  link.target = '_blank'

  // Adicionar o link ao DOM
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// Funções para classes de vigência
const getVigenciaClass = (vigente) => {
  if (!vigente) return 'bg-gray-100 text-gray-800'

  switch (vigente) {
    case 'VIGENTE':
      return 'bg-green-100 text-green-800'
    case 'EM ANÁLISE':
      return 'bg-yellow-100 text-yellow-800'
    case 'NÃO VIGENTE':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getVigenciaIcon = (vigente) => {
  if (!vigente) return 'fas fa-question-circle'

  switch (vigente) {
    case 'VIGENTE':
      return 'fas fa-check-circle'
    case 'EM ANÁLISE':
      return 'fas fa-clock'
    case 'NÃO VIGENTE':
      return 'fas fa-times-circle'
    default:
      return 'fas fa-question-circle'
  }
}

// Watchers
watch(() => props.filtros, (newFiltros) => {
  if (newFiltros && typeof newFiltros === 'object') {
    form.value = {
      search_term: newFiltros.search_term || '',
      tipo_id: newFiltros.tipo_id || '',
      orgao_id: newFiltros.orgao_id || '',
      vigente: newFiltros.vigente || '',
      data_inicio: newFiltros.data_inicio || '',
      data_fim: newFiltros.data_fim || ''
    }
  }
}, { immediate: true })

// Se houver filtros de data, expandir automaticamente
watch(() => [form.value.data_inicio, form.value.data_fim], ([inicio, fim]) => {
  if (inicio || fim) {
    mostrarFiltrosData.value = true
  }
}, { immediate: true })
</script>
