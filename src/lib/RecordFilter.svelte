<script lang="ts">
  let { 
    records = [],
    onSelectRecord 
  }: { 
    records: any[]; 
    onSelectRecord: (record: any[]) => void; 
  } = $props();
  
  // Import the escudo image
  import escudo from "../assets/escudo.png";
  
  // Filter states
  let searchTerm = $state("");
  let selectedArea = $state("");
  let selectedGrade = $state("");
  let selectedTeacher = $state("");
  let selectedPeriod = $state("");
  let sortBy = $state("recent"); // recent, area, teacher, period
  
  // Extract unique values for filters
  let uniqueAreas = $derived(() => [...new Set(records.map((r: any) => r[0]).filter(Boolean))].sort());
  let uniqueGrades = $derived(() => [...new Set(records.map((r: any) => r[2]).filter(Boolean))].sort());
  let uniqueTeachers = $derived(() => [...new Set(records.map((r: any) => r[1]).filter(Boolean))].sort());
  let uniquePeriods = $derived(() => [...new Set(records.map((r: any) => r[4]).filter(Boolean))].sort());
  
  // Check if showing all periods for a specific area and grade
  let shouldShowPDFButton = $derived(() => {
    if (!selectedArea || !selectedGrade || selectedPeriod) {
      return false; // Need area and grade selected, no period filter
    }
    
    const allPossiblePeriods = ['I', 'II', 'III', 'IV'];
    const currentGroupPeriods = new Set(
      records
        .filter((r: any) => r[0] === selectedArea && r[2] === selectedGrade)
        .map((r: any) => r[4])
        .filter(Boolean)
    );
    
    // Check if we have all standard periods for this area-grade combination
    return allPossiblePeriods.every(period => currentGroupPeriods.has(period)) && 
           currentGroupPeriods.size === allPossiblePeriods.length;
  });
  
  // Filtered and sorted records
  let filteredRecords = $derived(() => {
    let filtered = records.filter((record: any) => {
      const matchesSearch = !searchTerm || 
        record.some((field: any) => 
          field && field.toString().toLowerCase().includes(searchTerm.toLowerCase())
        );
      
      const matchesArea = !selectedArea || record[0] === selectedArea;
      const matchesGrade = !selectedGrade || record[2] === selectedGrade;
      const matchesTeacher = !selectedTeacher || record[1] === selectedTeacher;
      const matchesPeriod = !selectedPeriod || record[4] === selectedPeriod;
      
      return matchesSearch && matchesArea && matchesGrade && matchesTeacher && matchesPeriod;
    });
    
    // Sorting
    return filtered.sort((a: any, b: any) => {
      switch (sortBy) {
        case "area":
          return (a[0] || "").localeCompare(b[0] || "");
        case "teacher":
          return (a[1] || "").localeCompare(b[1] || "");
        case "period":
          return (a[4] || "").localeCompare(b[4] || "");
        case "recent":
        default:
          return 0; // Keep original order (most recent first)
      }
    });
  });
  
  // Grouped records for display
  let groupedRecords = $derived(() => {
    const groups: Record<string, any[]> = {};
    const records = filteredRecords();
    records.forEach((row: any) => {
      const key = `${row[0]} - ${row[2]}`;
      if (!groups[key]) {
        groups[key] = [];
      }
      groups[key].push(row);
    });
    return groups;
  });
  
  function clearFilters() {
    searchTerm = "";
    selectedArea = "";
    selectedGrade = "";
    selectedTeacher = "";
    selectedPeriod = "";
    sortBy = "recent";
  }
  
  function getRecordCount() {
    return filteredRecords.length;
  }
  
  function generatePDF() {
    if (!selectedArea || !selectedGrade) return;
    
    // Get all records for the selected area and grade (all periods)
    const allPeriodRecords = records.filter((r: any) => 
      r[0] === selectedArea && r[2] === selectedGrade
    );
    
    // Sort by period (I, II, III, IV)
    const sortedRecords = allPeriodRecords.sort((a: any, b: any) => {
      const periodOrder = ['I', 'II', 'III', 'IV'];
      const aPeriodIndex = periodOrder.indexOf(a[4]);
      const bPeriodIndex = periodOrder.indexOf(b[4]);
      return aPeriodIndex - bPeriodIndex;
    });
    
    // Create header HTML (same as PlanAulaForm)
    const headerHTML = `
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: white;">
        <tbody>
          <tr>
            <td rowspan="2" style="width: 100px; padding: 10px; border: 1px solid black;">
              <img src="${escudo}" alt="Escudo" style="max-width: 80px; height: auto; filter: grayscale(1);" />
            </td>
            <td style="border: 1px solid black; padding: 8px;">
              <h1 style="font-size: 1.2rem; margin: 0; font-weight: bold;">INSTITUCION EDUCATIVA INSTITUTO GUATICA</h1>
              <p style="margin: 4px 0 0; font-size: 0.9rem;">DANE</p>
            </td>
            <td rowspan="2" style="border: 1px solid black; padding: 8px; vertical-align: middle;">
              <h2 style="font-size: 1rem; margin: 0; font-weight: bold;">GESTIÓN ACADÉMICA</h2>
            </td>
            <td style="width: 150px; font-size: 0.8rem; font-weight: bold; height: 20px; border: 1px solid black;">CÓDIGO</td>
          </tr>
          <tr>
            <td style="border: 1px solid black; padding: 8px; height: 40px;"></td>
            <td style="border: 1px solid black; padding: 8px; background-color: #eee; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
              <div style="font-weight: bold; font-size: 0.9rem;">PLAN DE AULA</div>
              <div style="font-size: 0.8rem;">2026</div>
            </td>
          </tr>
        </tbody>
      </table>
    `;
    
    // Create HTML content for all periods
    let allPeriodsHTML = `
      <div style="font-family: Arial, sans-serif; padding: 20px;">
        ${headerHTML}
        <h2 style="text-align: center; margin: 20px 0 30px 0;">
          PLAN DE AULA COMPLETO - ${selectedArea} - ${selectedGrade}
        </h2>
    `;
    
    sortedRecords.forEach((record: any, index: number) => {
      const isLastRecord = index === sortedRecords.length - 1;
      allPeriodsHTML += `
        <div style="${isLastRecord ? '' : 'page-break-after: always;'} margin-bottom: 40px;">
          ${index === 0 ? '' : headerHTML}
          <h3 style="color: #333; margin-bottom: 15px; text-align: center; font-size: 1.1rem;">PERIODO ${record[4]}</h3>
          
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
              <td style="width: 50%; padding: 8px; border: 1px solid #333; font-weight: bold;">ÁREA:</td>
              <td style="width: 50%; padding: 8px; border: 1px solid #333;">${record[0]}</td>
            </tr>
            <tr>
              <td style="padding: 8px; border: 1px solid #333; font-weight: bold;">DOCENTE:</td>
              <td style="padding: 8px; border: 1px solid #333;">${record[1]}</td>
            </tr>
            <tr>
              <td style="padding: 8px; border: 1px solid #333; font-weight: bold;">GRADO:</td>
              <td style="padding: 8px; border: 1px solid #333;">${record[2]}</td>
            </tr>
            <tr>
              <td style="padding: 8px; border: 1px solid #333; font-weight: bold;">INTENSIDAD HORARIA:</td>
              <td style="padding: 8px; border: 1px solid #333;">${record[3]}</td>
            </tr>
          </table>
          
          <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead>
              <tr>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 20%; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  Estándares básicos de competencia<br/>CONTENIDOS
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 22%; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  INDICADORES DE DESEMPEÑO
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 20%; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  DERECHOS BÁSICOS DE APRENDIZAJE<br/>D B A
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 18%; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  CRITERIOS DE EVALUACIÓN
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 20%; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  ACTIVIDADES Y RECURSOS
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="border: 1px solid #333; padding: 8px 6px; vertical-align: top; height: 180px; font-size: 0.85rem; line-height: 1.3; word-wrap: break-word; white-space: pre-wrap;">${record[5] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px 6px; vertical-align: top; height: 180px; font-size: 0.85rem; line-height: 1.3; word-wrap: break-word; white-space: pre-wrap;">${record[6] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px 6px; vertical-align: top; height: 180px; font-size: 0.85rem; line-height: 1.3; word-wrap: break-word; white-space: pre-wrap;">${record[7] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px 6px; vertical-align: top; height: 180px; font-size: 0.85rem; line-height: 1.3; word-wrap: break-word; white-space: pre-wrap;">${record[8] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px 6px; vertical-align: top; height: 180px; font-size: 0.85rem; line-height: 1.3; word-wrap: break-word; white-space: pre-wrap;">${record[9] || ''}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `;
    });
    
    allPeriodsHTML += '</div>';
    
    // Create print window
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Plan de Aula Completo - ${selectedArea} - ${selectedGrade}</title>
          <style>
            @media print {
              body { margin: 0; font-family: Arial, sans-serif; }
              div { page-break-after: always; }
              div:last-child { page-break-after: auto; }
              @page { margin: 1.5cm; }
              table { page-break-inside: avoid; }
            }
            body { 
              font-family: Arial, sans-serif; 
              margin: 0; 
              font-size: 12px;
              line-height: 1.2;
            }
            table {
              border-collapse: collapse;
              width: 100%;
            }
            td, th {
              border: 1px solid #000;
            }
          </style>
        </head>
        <body>
          ${allPeriodsHTML}
        </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  }
</script>

<div class="filter-container">
  <!-- Search Box -->
  <div class="search-section">
    <div class="search-box">
      <input
        type="text"
        placeholder="Buscar en todos los campos..."
        bind:value={searchTerm}
        class="search-input"
      />
      <span class="search-icon">🔍</span>
    </div>
  </div>
  
  <!-- Filters Row -->
  <div class="filters-row">
    <select bind:value={selectedArea} class="filter-select">
      <option value="">Todas las áreas</option>
      {#each uniqueAreas() as area}
        <option value={area}>{area}</option>
      {/each}
    </select>
    
    <select bind:value={selectedGrade} class="filter-select">
      <option value="">Todos los grados</option>
      {#each uniqueGrades() as grade}
        <option value={grade}>{grade}</option>
      {/each}
    </select>
    
    <select bind:value={selectedTeacher} class="filter-select">
      <option value="">Todos los docentes</option>
      {#each uniqueTeachers() as teacher}
        <option value={teacher}>{teacher}</option>
      {/each}
    </select>
    
    <select bind:value={selectedPeriod} class="filter-select">
      <option value="">Todos los períodos</option>
      {#each uniquePeriods() as period}
        <option value={period}>{period}</option>
      {/each}
    </select>
  </div>
  
  <!-- Sort and Clear Controls -->
  <div class="controls-row">
    <div class="sort-control">
      <label for="sort-select">Ordenar por:</label>
      <select bind:value={sortBy} id="sort-select" class="sort-select">
        <option value="recent">Más reciente</option>
        <option value="area">Área</option>
        <option value="teacher">Docente</option>
        <option value="period">Período</option>
      </select>
    </div>
    
    <button 
      onclick={clearFilters}
      class="clear-btn"
      disabled={!searchTerm && !selectedArea && !selectedGrade && !selectedTeacher && !selectedPeriod}
    >
      Limpiar filtros
    </button>
    
    {#if shouldShowPDFButton()}
      <button 
        onclick={generatePDF}
        class="pdf-btn"
        title="Generar PDF con todos los períodos"
      >
        📄 Generar PDF
      </button>
    {/if}
    
    <span class="record-count">
      {getRecordCount()} registro{getRecordCount() !== 1 ? 's' : ''}
    </span>
  </div>
  
  <!-- Records List -->
  <div class="records-list">
    {#if Object.keys(groupedRecords()).length === 0}
      <div class="no-results">
        <p>No se encontraron registros</p>
        <small>Intenta ajustar los filtros o el término de búsqueda</small>
      </div>
    {:else}
      {#each Object.entries(groupedRecords()) as [key, rows]}
        <div class="area-group">
          <div class="area-title">{key}</div>
          <div class="area-items">
            {#each rows as row}
              <button class="record-item" onclick={() => onSelectRecord(row)}>
                <div class="record-header">
                  <span class="record-period">Pér: {row[4]}</span>
                  <span class="record-intensity">{row[3]}</span>
                </div>
                <div class="record-teacher">{row[1]}</div>
                {#if searchTerm && row.some((field: any) => field && field.toString().toLowerCase().includes(searchTerm.toLowerCase()))}
                  <div class="record-highlight">✓ Coincide con búsqueda</div>
                {/if}
              </button>
            {/each}
          </div>
        </div>
      {/each}
    {/if}
  </div>
</div>

<style>
  .filter-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    height: 100%;
  }
  
  .search-section {
    padding: 0 10px;
  }
  
  .search-box {
    position: relative;
  }
  
  .search-input {
    width: 100%;
    padding: 10px 40px 10px 12px;
    border: 1px solid #444;
    border-radius: 8px;
    background-color: #2a2a2a;
    color: white;
    font-size: 0.9rem;
    box-sizing: border-box;
    transition: border-color 0.2s;
  }
  
  .search-input:focus {
    outline: none;
    border-color: #4a90e2;
    background-color: #333;
  }
  
  .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
    font-size: 0.9rem;
  }
  
  .filters-row {
    display: flex;
    gap: 8px;
    padding: 0 10px;
    flex-wrap: wrap;
  }
  
  .filter-select {
    flex: 1;
    min-width: 120px;
    padding: 8px;
    border: 1px solid #444;
    border-radius: 6px;
    background-color: #2a2a2a;
    color: white;
    font-size: 0.8rem;
    box-sizing: border-box;
    cursor: pointer;
    transition: border-color 0.2s;
  }
  
  .filter-select:focus {
    outline: none;
    border-color: #4a90e2;
  }
  
  .controls-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 10px;
    flex-wrap: wrap;
    border-top: 1px solid #333;
    padding-top: 10px;
  }
  
  .sort-control {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    min-width: 150px;
  }
  
  .sort-control label {
    font-size: 0.8rem;
    color: #888;
    white-space: nowrap;
  }
  
  .sort-select {
    padding: 6px;
    border: 1px solid #444;
    border-radius: 4px;
    background-color: #2a2a2a;
    color: white;
    font-size: 0.8rem;
    cursor: pointer;
  }
  
  .clear-btn {
    padding: 6px 12px;
    border: 1px solid #666;
    border-radius: 4px;
    background-color: #444;
    color: white;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
  }
  
  .clear-btn:hover:not(:disabled) {
    background-color: #555;
    border-color: #777;
  }
  
  .clear-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .pdf-btn {
    padding: 6px 12px;
    border: 1px solid #28a745;
    border-radius: 4px;
    background-color: #28a745;
    color: white;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    font-weight: bold;
  }
  
  .pdf-btn:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: scale(1.05);
  }
  
  .record-count {
    font-size: 0.8rem;
    color: #888;
    margin-left: auto;
  }
  
  .records-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    overflow-x: hidden;
  }
  
  .records-list::-webkit-scrollbar {
    width: 6px;
  }
  
  .records-list::-webkit-scrollbar-thumb {
    background-color: #444;
    border-radius: 3px;
  }
  
  .no-results {
    text-align: center;
    padding: 40px 20px;
    color: #666;
  }
  
  .no-results p {
    margin: 0 0 8px 0;
    font-size: 1rem;
  }
  
  .no-results small {
    font-size: 0.8rem;
    color: #555;
  }
  
  .area-group {
    margin-bottom: 15px;
  }
  
  .area-title {
    font-size: 0.75rem;
    font-weight: bold;
    color: #888;
    text-transform: uppercase;
    margin-bottom: 8px;
    padding-left: 5px;
    border-left: 2px solid #4a90e2;
  }
  
  .area-items {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }
  
  .record-item {
    background-color: #2a2a2a;
    border: 1px solid #333;
    border-radius: 8px;
    padding: 10px;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    color: white;
  }
  
  .record-item:hover {
    background-color: #333;
    border-color: #444;
    transform: translateX(3px);
  }
  
  .record-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
  }
  
  .record-period {
    font-size: 0.7rem;
    color: #4a90e2;
    font-weight: bold;
  }
  
  .record-intensity {
    font-size: 0.7rem;
    color: #888;
  }
  
  .record-teacher {
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
  }
  
  .record-highlight {
    font-size: 0.7rem;
    color: #4CAF50;
    font-weight: bold;
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .filters-row {
      flex-direction: column;
    }
    
    .filter-select {
      min-width: 100%;
    }
    
    .controls-row {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
    
    .sort-control {
      flex: none;
    }
    
    .record-count {
      margin-left: 0;
      text-align: center;
    }
    
    .pdf-btn {
      order: -1;
      width: 100%;
      margin-bottom: 8px;
      justify-self: center;
    }
  }
</style>