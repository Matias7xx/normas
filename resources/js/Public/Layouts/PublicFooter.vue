<script setup>
import { computed } from 'vue'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
})

const currentYear = computed(() => new Date().getFullYear())
const lastUpdate = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('pt-BR')
})
</script>

<template>
  <footer class="bg-gradient-to-r from-gray-50 to-gray-100 border-t-4 border-[#c1a85a] text-gray-700">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between py-6 text-center lg:text-left">
        <!-- Informações da versão -->
        <div class="mb-4 lg:mb-0">
          <div class="font-bold text-gray-800 mb-1">
            <a 
              href="https://policiacivil.pb.gov.br" 
              class="text-[#c1a85a] hover:text-[#a8914a] transition-colors duration-300 no-underline" 
              target="_blank" 
              rel="noopener"
            >
              Polícia Civil da Paraíba
            </a>
          </div>
          <div class="text-gray-600 text-sm">
            Biblioteca de Normas
          </div>
        </div>
        
        <!-- Informações técnicas e direitos -->
        <div class="space-y-2">
          <!-- Tags técnicas -->
          <div class="flex flex-wrap justify-center lg:justify-end gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white border border-[#c1a85a] text-[#c1a85a]">
              <i class="fas fa-code mr-1"></i> 
              Versão 3.1.4
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white border border-[#c1a85a] text-[#c1a85a]">
              <i class="fas fa-server mr-1"></i> 
              DITI/DG-PCPB
            </span>
          </div>
          <!-- Copyright -->
          <div class="text-gray-600 text-sm">
            © {{ currentYear }} - Todos os Direitos Reservados
          </div>
        </div>
      </div>
      
      <!-- Estatísticas -->
      <div class="border-t border-gray-200 py-4">
        <div class="flex justify-center items-center text-sm text-gray-600">
          <div class="flex items-center hover:text-[#c1a85a] transition-colors duration-300">
            <i class="fas fa-clock mr-2 text-[#c1a85a]"></i>
            Última Atualização: {{ lastUpdate }}
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>