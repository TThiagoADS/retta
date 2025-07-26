<template>
  <div class="dashboard-container">
    <div class="dashboard-card mb-6">
      <div class="dashboard-header">
        <h1 class="display-2 font-weight-bold">
         <b></b> 
        </h1>
        <p class="subtitle-1 mt-2">Análise completa dos dados parlamentares</p>
      </div>
    </div>

    <div class="stats-row">
      <div class="stats-col">
        <div class="stats-card deputy-card">
          <div class="stats-content">
            <div class="stats-icon">👥</div>
            <h2 class="stats-number">{{ totalDeputadosApi }}</h2>
            <p class="stats-label">Total de Deputados</p>
          </div>
        </div>
      </div>
      
      <div class="stats-col">
        <div class="stats-card expense-card">
          <div class="stats-content">
            <div class="stats-icon">💰</div>
            <h2 class="stats-number">{{ GastosTotaisApi }}</h2>
            <p class="stats-label">Gastos Totais</p>
          </div>
        </div>
      </div>
      
      <div class="stats-col">
        <div class="stats-card warning-card">
          <div class="stats-content">
            <div class="stats-icon">📊</div>
            <h2 class="stats-number">{{ formatCurrency(gastoMedio) }}</h2>
            <p class="stats-label">Gasto Médio Por Deputado</p>
          </div>
        </div>
      </div>
      
      <div class="stats-col">
        <div class="stats-card info-card">
          <div class="stats-content">
            <div class="stats-icon">🏛️</div>
            <h2 class="stats-number">{{ totalEstados }}</h2>
            <p class="stats-label">Estados</p>
          </div>
        </div>
      </div>
    </div>

    <div class="charts-section">
      <div class="chart-col">
        <div class="dashboard-card">
          <div class="card-header">
            <h3>🥧 Deputados por Partido</h3>
          </div>
          <div class="chart-container">
            <canvas :id="partidosChartId" width="400" height="300"></canvas>
          </div>
        </div>
      </div>

      <div class="chart-col">
        <div class="dashboard-card">
          <div class="card-header">
            <h3>💳 Top 10 - Gastos por Tipo</h3>
          </div>
          <div class="expenses-list">
            <div 
              v-for="(gasto, index) in topGastosPorTipo" 
              :key="index"
              class="expense-item"
              :class="{ 'expense-highlight': index < 3 }"
            >
              <div class="expense-rank">
                <span class="rank-number">{{ index + 1 }}</span>
                <div class="rank-icon">
                  <span v-if="index === 0">🥇</span>
                  <span v-else-if="index === 1">🥈</span>
                  <span v-else-if="index === 2">🥉</span>
                  <span v-else>📊</span>
                </div>
              </div>
              <div class="expense-info">
                <h4 class="expense-type">{{ gasto.expense_type }}</h4>
                <div class="expense-value">{{ formatCurrency(gasto.valor_numerico) }}</div>
                <div class="expense-bar">
                  <div 
                    class="expense-progress" 
                    :style="{ width: (gasto.valor_numerico / topGastosPorTipo[0].valor_numerico * 100) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="main-table-section">
      <div class="dashboard-card">
        <div class="card-header">
          <h3>👨‍💼 Lista Completa de Deputados com Gastos</h3>
          <div class="search-controls">
            <input 
              v-model="searchDeputados" 
              type="text" 
              placeholder="Buscar por nome, partido, estado ou valor..."
              class="search-input"
            />
            <select v-model="sortField" class="sort-select" @change="applySorting">
              <option value="name">Ordenar por Nome</option>
              <option value="party_abbr">Ordenar por Partido</option>
              <option value="state_abbr">Ordenar por Estado</option>
              <option value="total_expenses_numeric">Ordenar por Gastos</option>
            </select>
            <button @click="toggleSortOrder" class="sort-button">
              {{ sortOrder === 'asc' ? '⬆️' : '⬇️' }}
            </button>
          </div>
        </div>
        
        <div class="loading" v-if="isLoadingDeputados">
          <div class="spinner"></div>
          <p>Carregando deputados...</p>
        </div>
        
        <div class="deputies-grid" v-else>
          <div 
            v-for="deputado in paginatedDeputados" 
            :key="deputado.id"
            class="deputy-card-item"
          >
            <div class="deputy-photo">
              <img 
                :src="deputado.photo_url" 
                :alt="deputado.name"
                @error="handleImageError"
                loading="lazy"
              />
            </div>
            <div class="deputy-info">
              <h4 class="deputy-name">{{ deputado.name }}</h4>
              <div class="deputy-details">
                <div class="deputy-tags">
                  <span :class="['party-chip', getPartyColor(deputado.party_abbr)]">
                    {{ deputado.party_abbr }}
                  </span>
                  <span class="state-chip">
                    {{ deputado.state_abbr }}
                  </span>
                </div>
                <span :class="['expense-amount', getExpenseColor(deputado.total_expenses_numeric)]">
                  {{ formatCurrency(deputado.total_expenses_numeric) }}
                </span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="pagination" v-if="totalPages > 1">
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage <= 1"
            class="pagination-btn"
          >
            ◀️ Anterior
          </button>
          
          <span class="pagination-info">
            Página {{ currentPage }} de {{ totalPages }} 
            ({{ filteredDeputados.length }} deputados)
          </span>
          
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage >= totalPages"
            class="pagination-btn"
          >
            Próxima ▶️
          </button>
        </div>
      </div>
    </div>

    <button class="floating-action" @click="refreshData" title="Atualizar dados">
      🔄
    </button>
  </div>
</template>

<script>
export default {
  name: 'DashboardDeputados',
  data() {
    return {
      totalDeputadosApi: 0,
      GastosTotaisApi: 0,
      totalEstadosApi: 0,
      deputadosPorPartido: [],
      deputadosComGastos: [],
      searchDeputados: '',
      gastosPorTipo: [],
      sortField: 'total_expenses_numeric',
      sortOrder: 'desc',
      currentPage: 1,
      itemsPerPage: 12,
      partidosChartId: `partidos-chart-${Math.random().toString(36).substr(2, 9)}`,
      partidosChart: null,
      isDataLoaded: false,
      isLoadingDeputados: false
    };
  },
  computed: {
    totalDeputados() {
      return this.totalDeputadosApi;
    },
    gastoMedio() {
      const gastos = typeof this.GastosTotaisApi === 'string'
        ? parseFloat(this.GastosTotaisApi.replace(/\./g, '').replace(',', '.'))
        : this.GastosTotaisApi;

      const deputados = parseInt(this.totalDeputadosApi);

      if (!gastos || !deputados) return 0;

      return (gastos / deputados).toFixed(2);
    },
    totalEstados() {
      return this.totalEstadosApi;
    },
    topGastosPorTipo() {
      return [...this.gastosPorTipo]
        .sort((a, b) => b.valor_numerico - a.valor_numerico)
        .slice(0, 10);
    },
    filteredDeputados() {
      let filtered = this.deputadosComGastos.filter(dep => {
        const searchTerm = this.searchDeputados.toLowerCase();
        return dep.name.toLowerCase().includes(searchTerm) ||
               dep.party_abbr.toLowerCase().includes(searchTerm) ||
               dep.state_abbr.toLowerCase().includes(searchTerm) ||
               dep.total_expenses.toLowerCase().includes(searchTerm);
      });

      return this.sortDeputados(filtered);
    },
    paginatedDeputados() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredDeputados.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredDeputados.length / this.itemsPerPage);
    }
  },
  watch: {
    searchDeputados() {
      this.currentPage = 1;
    }
  },
  async mounted() {
    await this.loadChartJS();
    await this.loadAllData();
    
    this.$nextTick(() => {
      this.createCharts();
    });
  },
  beforeUnmount() {
    this.destroyCharts();
  },
  methods: {
    async loadChartJS() {
      if (window.Chart) return;
      
      return new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js';
        script.onload = resolve;
        document.head.appendChild(script);
      });
    },

    async loadAllData() {
      try {
        await Promise.all([
          this.fetchTotalDeputados(),
          this.fetchTotalGastos(),
          this.fetchEstados(),
          this.fetchDeputadosPorPartido(),
          this.fetchGastosPorTipo(),
          this.fetchDeputadosComGastos()
        ]);
        
        this.isDataLoaded = true;
        console.log('Todos os dados carregados');
      } catch (error) {
        console.error('Erro ao carregar dados:', error);
      }
    },

    async fetchGastosPorTipo() {
      try {
        const response = await fetch('http://localhost:8000/api/sumExpenseType');
        const data = await response.json();
        
        console.log('Resposta da API gastos por tipo:', data);
        
        if (Array.isArray(data)) {
          this.gastosPorTipo = data.map(item => ({
            ...item,
            valor_numerico: this.parseValueToNumber(item.total)
          }));
          console.log('Gastos por tipo carregados:', this.gastosPorTipo);
        } else {
          console.error('Formato de dados inesperado:', data);
          this.gastosPorTipo = [];
        }
      } catch (error) {
        console.error('Erro ao buscar gastos por tipo:', error);
        this.gastosPorTipo = [];
      }
    },

    async fetchDeputadosComGastos() {
      this.isLoadingDeputados = true;
      try {
        const response = await fetch('http://localhost:8000/api/getWithExpenses');
        const data = await response.json();
        
        console.log('Deputados com gastos carregados:', data);
        
        this.deputadosComGastos = data.map(deputado => ({
          ...deputado,
          total_expenses_numeric: this.parseValueToNumber(deputado.total_expenses)
        }));
        
      } catch (error) {
        console.error('Erro ao buscar deputados com gastos:', error);
        this.deputadosComGastos = [];
      } finally {
        this.isLoadingDeputados = false;
      }
    },

    async fetchDeputadosPorPartido() {
      try {
        const response = await fetch('http://localhost:8000/api/countDeputiesByParty');
        const data = await response.json();
        
        if (data && data.original && Array.isArray(data.original)) {
          this.deputadosPorPartido = data.original;
        } else {
          console.error('Formato de dados inesperado:', data);
          this.deputadosPorPartido = [];
        }
      } catch (error) {
        console.error('Erro ao buscar deputados por partido:', error);
        this.deputadosPorPartido = [];
      }
    },

    async fetchEstados() {
      try {
        const response = await fetch('http://localhost:8000/api/sumStateAbbr');
        const data = await response.json();
        this.totalEstadosApi = data;
      } catch (error) {
        console.error('Erro ao buscar estados:', error);
      }
    },

    async fetchTotalDeputados() {
      try {
        const response = await fetch('http://localhost:8000/api/totalDeputies');
        const data = await response.json();
        this.totalDeputadosApi = Array.isArray(data) ? data[0] : data;
      } catch (error) {
        console.error('Erro ao buscar total de deputados:', error);
      }
    },

    async fetchTotalGastos() {
      try {
        const response = await fetch('http://localhost:8000/api/sumNetValueTotal');
        const data = await response.json();
        this.GastosTotaisApi = data;
      } catch (error) {
        console.error('Erro ao buscar total de gastos:', error);
      }
    },

    parseValueToNumber(valueString) {
      if (typeof valueString === 'string') {
        return parseFloat(valueString.replace(/\./g, '').replace(',', '.'));
      }
      return parseFloat(valueString) || 0;
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value);
    },

    getPartyColor(partido) {
      const colors = {
        'PT': 'red-chip',
        'PSDB': 'blue-chip',
        'MDB': 'green-chip',
        'PSL': 'orange-chip',
        'PP': 'purple-chip',
        'PDT': 'teal-chip',
        'PSOL': 'pink-chip',
        'DEM': 'indigo-chip',
        'PODE': 'cyan-chip',
        'REPUBLICANOS': 'brown-chip'
      };
      return colors[partido] || 'grey-chip';
    },

    getExpenseColor(valor) {
      if (valor > 200000) return 'expense-high';
      if (valor > 100000) return 'expense-medium';
      return 'expense-low';
    },

    handleImageError(event) {
      event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiNlMGUwZTAiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMmM0LjQyIDAgOC00IDgtOHMtMy41OC04LTgtOC04IDMuNTgtOCA4IDMuNTggOCA4IDh6bTAgMmMtNS4zMyAwLTE2IDIuNjctMTYgOHYyaDE2VjE0eiIgZmlsbD0iIzk5OTk5OSIvPgo8L3N2Zz4KPC9zdmc+';
    },

    sortDeputados(deputados) {
      return [...deputados].sort((a, b) => {
        let aVal, bVal;
        
        switch (this.sortField) {
          case 'name':
            aVal = a.name.toLowerCase();
            bVal = b.name.toLowerCase();
            break;
          case 'party_abbr':
            aVal = a.party_abbr.toLowerCase();
            bVal = b.party_abbr.toLowerCase();
            break;
          case 'state_abbr':
            aVal = a.state_abbr.toLowerCase();
            bVal = b.state_abbr.toLowerCase();
            break;
          case 'total_expenses_numeric':
            aVal = a.total_expenses_numeric;
            bVal = b.total_expenses_numeric;
            break;
          default:
            return 0;
        }
        
        if (this.sortOrder === 'asc') {
          return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
        } else {
          return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
        }
      });
    },

    applySorting() {
      this.currentPage = 1;
    },

    toggleSortOrder() {
      this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      this.currentPage = 1;
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    async refreshData() {
      this.destroyCharts();
      await this.loadAllData();
      this.$nextTick(() => {
        this.createCharts();
      });
    },

    destroyCharts() {
      if (this.partidosChart) {
        this.partidosChart.destroy();
        this.partidosChart = null;
      }
    },

    createCharts() {
  if (!window.Chart) {
    console.error('Chart.js não carregado');
    return;
  }

  const partidosCanvas = document.getElementById(this.partidosChartId);
  if (partidosCanvas && this.deputadosPorPartido && this.deputadosPorPartido.length > 0) {
    const labels = this.deputadosPorPartido.map(item => item.party_abbr);
    const data = this.deputadosPorPartido.map(item => item.total);
    
    const backgroundColors = [
      '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
      '#9966FF', '#FF9F40', '#FF6B6B', '#4ECDC4',
      '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD',
      '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
      '#F8C471', '#82E0AA', '#F1948A', '#AED6F1'
    ];

    // eslint-disable-next-line no-undef
    this.partidosChart = new Chart(partidosCanvas, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Número de Deputados',
          data: data,
          backgroundColor: backgroundColors.slice(0, data.length),
          borderColor: backgroundColors.slice(0, data.length).map(color => color + '80'),
          borderWidth: 2,
          borderRadius: 8,
          borderSkipped: false,
        }]
      },
      options: {
        indexAxis: 'y', // Barras horizontais
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0,0,0,0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            callbacks: {
              label: function(context) {
                return `${context.parsed.x} deputados`;
              }
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0,0,0,0.1)'
            },
            ticks: {
              font: {
                size: 12
              }
            }
          },
          y: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 12,
                weight: 'bold'
              }
            }
          }
        },
        animation: {
          duration: 1500,
          easing: 'easeInOutQuart'
        }
      }
    });
  }
}
  }
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.dashboard-container {
  background: linear-gradient(135deg, #000000 0%, #333333 100%);
  min-height: 100vh;
  padding: 20px;
  font-family: 'Roboto', sans-serif;
}

.dashboard-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 20px;
}

.dashboard-header {
  background: linear-gradient(135deg, #000000, #444444);
  color: white;
  text-align: center;
  padding: 2rem;
  border-radius: 20px 20px 0 0;
}

.display-2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
}

.subtitle-1 {
  font-size: 1.1rem;
  margin-top: 10px;
  opacity: 0.9;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stats-card {
  color: white;
  border-radius: 15px;
  padding: 25px;
  text-align: center;
}

.deputy-card {
  background: linear-gradient(135deg, #43a047, #2e7d32);
}

.expense-card {
  background: linear-gradient(135deg, #e53935, #c62828);
}

.warning-card {
  background: linear-gradient(135deg, #ff9800, #f57c00);
}

.info-card {
  background: linear-gradient(135deg, #9c27b0, #7b1fa2);
}

.stats-icon {
  font-size: 3rem;
  margin-bottom: 10px;
}

.stats-number {
  font-size: 2rem;
  font-weight: bold;
  margin: 10px 0;
}

.stats-label {
  font-size: 1rem;
  opacity: 0.9;
  margin: 0;
}

.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.chart-container {
  padding: 20px;
  height: 350px;
  position: relative;
}

.expenses-list {
  padding: 20px;
  max-height: 400px;
  overflow-y: auto;
}

.expense-item {
  display: flex;
  align-items: center;
  padding: 15px;
  margin-bottom: 10px;
  background: #f8f9fa;
  border-radius: 12px;
  border-left: 4px solid #e0e0e0;
}

.expense-highlight {
  background: linear-gradient(135deg, #fff3e0, #ffe0b2);
  border-left-color: #ff9800;
}

.expense-highlight:first-child {
  border-left-color: #ffd700;
  background: linear-gradient(135deg, #fffde7, #fff9c4);
}

.expense-highlight:nth-child(2) {
  border-left-color: #c0c0c0;
  background: linear-gradient(135deg, #f5f5f5, #eeeeee);
}

.expense-highlight:nth-child(3) {
  border-left-color: #cd7f32;
  background: linear-gradient(135deg, #fff8e1, #ffecb3);
}

.expense-rank {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-right: 15px;
  min-width: 50px;
}

.rank-number {
  font-size: 0.9rem;
  font-weight: bold;
  color: #666;
}

.rank-icon {
  font-size: 1.5rem;
  margin-top: 5px;
}

.expense-info {
  flex: 1;
  min-width: 0;
}

.expense-type {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #333;
  line-height: 1.3;
}

.expense-value {
  font-size: 1.2rem;
  font-weight: bold;
  color: #1976d2;
  margin-bottom: 8px;
}

.expense-bar {
  width: 100%;
  height: 6px;
  background: #e0e0e0;
  border-radius: 3px;
  overflow: hidden;
}

.expense-progress {
  height: 100%;
  background: linear-gradient(90deg, #1976d2, #42a5f5);
  border-radius: 3px;
}

.card-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 1.3rem;
  color: #333;
}

.main-table-section {
  margin-bottom: 30px;
}

.search-controls {
  display: flex;
  gap: 10px;
  align-items: center;
}

.search-input {
  padding: 10px 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  flex: 1;
  max-width: 300px;
}

.search-input:focus {
  border-color: #1976d2;
}

.sort-select {
  padding: 10px;
  border: 2px solid #ddd;
  border-radius: 8px;
  background: white;
  cursor: pointer;
}

.sort-button {
  padding: 10px 15px;
  border: 2px solid #1976d2;
  background: #1976d2;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
}

.sort-button:hover {
  background: #1565c0;
}

.loading {
  text-align: center;
  padding: 40px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #1976d2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.deputies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  padding: 20px;
}

.deputy-card-item {
  background: white;
  border-radius: 15px;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 15px;
}

.deputy-photo {
  flex-shrink: 0;
}

.deputy-photo img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #e0e0e0;
}

.deputy-info {
  flex: 1;
  min-width: 0;
}

.deputy-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.deputy-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.deputy-tags {
  display: flex;
  gap: 8px;
  align-items: center;
}

.party-chip, .state-chip {
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
  color: white;
  display: inline-block;
}

.state-chip {
  background: #607d8b;
  color: white;
}

.red-chip { background: #f44336; }
.blue-chip { background: #2196f3; }
.green-chip { background: #4caf50; }
.orange-chip { background: #ff9800; }
.purple-chip { background: #9c27b0; }
.teal-chip { background: #009688; }
.pink-chip { background: #e91e63; }
.indigo-chip { background: #3f51b5; }
.brown-chip { background: #795548; }
.grey-chip { background: #9e9e9e; }
.cyan-chip { background: #00bcd4; }

.expense-amount {
  font-weight: bold;
  font-size: 0.95rem;
}

.expense-high {
  color: #d32f2f;
}

.expense-medium {
  color: #f57c00;
}

.expense-low {
  color: #388e3c;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  padding: 20px;
  border-top: 1px solid #eee;
}

.pagination-btn {
  padding: 10px 20px;
  border: 2px solid #1976d2;
  background: white;
  color: #1976d2;
  border-radius: 8px;
  cursor: pointer;
}

.pagination-btn:hover:not(:disabled) {
  background: #1976d2;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  font-weight: 500;
  color: #666;
}

.floating-action {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1976d2, #1565c0);
  color: white;
  border: none;
  font-size: 24px;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  z-index: 1000;
}

.floating-action:hover {
  background: linear-gradient(135deg, #1565c0, #0d47a1);
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 10px;
  }
  
  .stats-row {
    grid-template-columns: 1fr;
  }
  
  .charts-section {
    grid-template-columns: 1fr;
  }
  
  .deputies-grid {
    grid-template-columns: 1fr;
    padding: 15px;
  }
  
  .display-2 {
    font-size: 1.8rem;
  }
  
  .card-header {
    flex-direction: column;
    gap: 15px;
  }
  
  .search-controls {
    flex-direction: column;
    width: 100%;
  }
  
  .search-input {
    max-width: 100%;
  }
  
  .deputy-details {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .deputy-tags {
    flex-wrap: wrap;
  }
  
  .expense-item {
    flex-direction: column;
    text-align: center;
  }
  
  .expense-rank {
    margin-right: 0;
    margin-bottom: 10px;
  }
}
</style>