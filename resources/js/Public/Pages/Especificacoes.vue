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
    <section class="max-w-7xl mx-auto px-4 py-8">
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
          class="flex items-center justify-between px-6 py-5 border-b-2 border-gray-100 hover:bg-gray-50 transition-colors duration-200 group"
          :class="{ 'border-b-0': index === especificacoes.length - 1 }"
        >
          <!-- Nome da especificação -->
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-700 transition-colors">
              {{ especificacao?.nome || 'Especificação técnica' }}
            </h3>
            <div class="flex items-center text-sm text-gray-500 mt-1">
              <i class="fas fa-calendar-alt mr-2"></i>
              {{ formatarData(especificacao?.created_at) }}
            </div>
          </div>

          <div class="flex items-center space-x-3 ml-6">
            <!-- Botão Visualizar/Baixar -->
             <button
              v-if="especificacao?.arquivo"
              @click="visualizarPDF(especificacao?.id)"
              class="bg-gradient-to-r from-[#c1a85a] to-[#b39c4f] hover:from-[#a8914a] hover:to-[#9b853f] disabled:from-gray-400 disabled:to-gray-500 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:shadow-md disabled:cursor-not-allowed"
            >
              <i class="fas fa-eye"></i>
              Visualizar
            </button>

            <button
              v-if="especificacao?.arquivo"
              @click="baixarEspecificacao(especificacao?.id)"
              class="bg-gradient-to-r from-[#c1a85a] to-[#b39c4f] hover:from-[#a8914a] hover:to-[#9b853f] disabled:from-gray-400 disabled:to-gray-500 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:shadow-md disabled:cursor-not-allowed"
            >
              <i class="fas fa-download"></i>
              Baixar
            </button>
          </div>
        </div>
      </div>

      <!-- Estado vazio -->
      <div v-else class="text-center py-16">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-12 max-w-md mx-auto border border-gray-200">
          <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-folder-open text-4xl text-gray-600"></i>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 mb-4">
            Nenhuma especificação disponível
          </h3>
          <p class="text-gray-600 mb-8 leading-relaxed">
            No momento não há especificações técnicas publicadas. Volte em breve para verificar novas atualizações.
          </p>
          <button
            @click="recarregarPagina"
            class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white px-8 py-3 rounded-lg font-medium transition-all duration-300 flex items-center mx-auto hover:shadow-lg hover:scale-105"
          >
            <i class="fas fa-refresh mr-2"></i>
            Atualizar Página
          </button>
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
/* acessibilidade */
button:focus {
  outline: 2px solid #1f1f1f;
  outline-offset: 2px;
}

/* Responsividade */
@media (max-width: 768px) {
  .flex.items-center.justify-between {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .flex.items-center.space-x-3 {
    width: 100%;
    justify-content: flex-end;
  }
  
  .ml-6 {
    margin-left: 0;
  }
  
  .px-6 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

@media (max-width: 640px) {
  .flex.items-center.space-x-3 {
    flex-direction: column;
    space-x: 0;
    gap: 0.5rem;
  }
  
  .flex.items-center.space-x-3 button {
    width: 100%;
    justify-content: center;
  }
}

/* Estados interativos */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: translateY(0);
}

/* Bordas */
.border-gray-100 {
  border-color: #f3f4f6;
}

/* Lista zebrada */
.bg-white > div:nth-child(even) {
  background-color: #fafafa;
}

.bg-white > div:nth-child(even):hover {
  background-color: #f5f5f5;
}
</style>