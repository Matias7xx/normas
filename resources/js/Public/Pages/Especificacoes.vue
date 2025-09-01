<template>
  <PublicLayout :stats="pageStats">
    <Head title="Especificações Técnicas" />
    
    <!-- Header da página -->
    <section class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center">
          <h1 class="text-3xl md:text-4xl font-bold mb-4">
            Especificações Técnicas
          </h1>
          <p class="text-xl text-blue-100 max-w-2xl mx-auto">
            Documentos técnicos para equipamentos
          </p>
        </div>
      </div>
    </section>

    <!-- Lista de especificações -->
    <section class="max-w-7xl mx-auto px-4 py-8 min-h-[60vh]">
      <!-- Loading state -->
      <div v-if="carregando" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Carregando especificações...</p>
      </div>

      <!-- Lista de especificações -->
      <div v-else-if="especificacoes && especificacoes.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div
          v-for="(especificacao, index) in especificacoes"
          :key="especificacao?.id || `spec-${index}`"
          class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-4 sm:px-6 py-5 border-b-2 border-gray-100 hover:bg-gray-50 transition-colors duration-200 group last:border-b-0"
        >
          <!-- Nome da especificação -->
          <div class="flex-1 mb-4 lg:mb-0 lg:mr-6">
            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-700 transition-colors mb-2">
              {{ especificacao?.nome || 'Especificação técnica' }}
            </h3>
            <div class="flex items-center text-sm text-gray-500">
              <i class="fas fa-calendar-alt mr-2"></i>
              {{ formatarData(especificacao?.created_at) }}
            </div>
          </div>

          <!-- Botões de ação -->
          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full lg:w-auto">
            <!-- Botão Visualizar -->
            <button
              v-if="especificacao?.arquivo"
              @click="visualizarPDF(especificacao?.id)"
              class="bg-gradient-to-r from-[#c1a85a] to-[#b39c4f] hover:from-[#a8914a] hover:to-[#9b853f] disabled:from-gray-400 disabled:to-gray-500 text-white px-4 sm:px-6 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-md disabled:cursor-not-allowed w-full sm:w-auto min-w-[120px]"
            >
              <i class="fas fa-eye text-sm"></i>
              <span>Visualizar</span>
            </button>

            <!-- Botão Baixar -->
            <button
              v-if="especificacao?.arquivo"
              @click="baixarEspecificacao(especificacao?.id, especificacao?.nome)"
              :disabled="baixandoId === especificacao?.id"
              class="bg-gradient-to-r from-[#c1a85a] to-[#b39c4f] hover:from-[#a8914a] hover:to-[#9b853f] disabled:from-gray-400 disabled:to-gray-500 text-white px-4 sm:px-6 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-md disabled:cursor-not-allowed w-full sm:w-auto min-w-[120px] relative"
            >
              <i 
                :class="baixandoId === especificacao?.id ? 'fas fa-spinner fa-spin' : 'fas fa-download'"
                class="text-sm"
              ></i>
              <span>{{ baixandoId === especificacao?.id ? 'Baixando...' : 'Baixar' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Estado vazio -->
      <div v-else class="flex items-center justify-center min-h-[400px]">
        <div class="text-center">
          <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8 sm:p-12 max-w-md mx-auto border border-gray-200">
            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-folder-open text-3xl sm:text-4xl text-gray-600"></i>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">
              Nenhuma especificação disponível
            </h3>
            <p class="text-gray-600 mb-8 leading-relaxed text-sm sm:text-base">
              No momento não há especificações técnicas publicadas. Volte em breve para verificar novas atualizações.
            </p>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '../Layouts/PublicLayout.vue'

const props = defineProps({
  especificacoes: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

const carregando = ref(false)
const baixandoId = ref(null)

const pageStats = computed(() => ({
  usuarios_ativos: props.stats?.usuarios_ativos || 0,
  normas_cadastradas: props.stats?.normas_cadastradas || 0,
  especificacoes_count: props.especificacoes?.length || 0
}))

const formatarData = (data) => {
  if (!data) return 'Data não disponível'
  
  try {
    const dataObj = new Date(data)
    
    if (isNaN(dataObj.getTime())) {
      return data
    }
    
    return dataObj.toLocaleDateString('pt-BR', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      timeZone: 'America/Sao_Paulo'
    })
  } catch (error) {
    console.error('Erro ao formatar data:', error)
    return data
  }
}

const baixarEspecificacao = async (id, nome) => {
  if (!id) {
    console.error('ID da especificação não informado')
    return
  }
  
  baixandoId.value = id
  
  try {
    // Criar um link temporário para forçar o download
    const link = document.createElement('a')
    link.href = `/especificacao/download/${id}`
    link.download = `${nome || 'especificacao'}.pdf`
    link.target = '_blank'
    
    // Adicionar o link ao DOM
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    // Pequeno delay para melhorar a UX
    await new Promise(resolve => setTimeout(resolve, 1000))
    
  } catch (error) {
    console.error('Erro ao baixar especificação:', error)
    alert('Erro ao baixar o arquivo. Tente novamente.')
  } finally {
    baixandoId.value = null
  }
}

const visualizarPDF = (id) => {
  if (!id) {
    console.error('ID da especificação não informado')
    return
  }
  
  // Abrir PDF em nova aba
  window.open(`/especificacao/view/${id}`, '_blank')
}

const recarregarPagina = () => {
  window.location.reload()
}

onMounted(() => {
  console.log('Especificações carregadas:', props.especificacoes)
})
</script>

<style scoped>
/* Acessibilidade */
button:focus {
  outline: 2px solid #1f1f1f;
  outline-offset: 2px;
}

/* Estados interativos */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: translateY(0);
}

/* Animação de loading nos botões */
button:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

/* Estados visuais */
.group:hover {
  background-color: #f9fafb;
}

/* Bordas */
.border-gray-100 {
  border-color: #f3f4f6;
}

/* Lista */
.bg-white > div:nth-child(even) {
  background-color: #fafafa;
}

.bg-white > div:nth-child(even):hover {
  background-color: #f5f5f5;
}

/* Transições */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

@media (max-width: 640px) {
  /* Botões ocupam toda a largura em telas muito pequenas */
  .flex.flex-col.sm\\:flex-row button {
    min-height: 44px;
  }
  
  /* Padding reduzido em telas pequenas */
  .px-4.sm\\:px-6 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  /* Texto ligeiramente menor em mobile */
  .text-lg {
    font-size: 1.1rem;
  }
  
  /* Ícones menores */
  .text-3xl.sm\\:text-4xl {
    font-size: 2rem;
  }
}

/* Hover effects */
@media (min-width: 1024px) {
  button:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }
  
  .group:hover {
    transform: translateY(-1px);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }
}

/* Animação para o ícone de loading */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.fa-spin {
  animation: spin 1s linear infinite;
}
</style>