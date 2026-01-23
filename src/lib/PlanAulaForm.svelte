<script lang="ts">
  import escudo from "../assets/escudo.png";
  import { sheetsService } from "./services/google_sheets_service.svelte";
  import Swal from "sweetalert2";
  import { onMount } from "svelte";
  import TeacherSelector from "./TeacherSelector.svelte";
  import AreaSelector from "./AreaSelector.svelte";
  import PeriodSelector from "./PeriodSelector.svelte";
  import GradeSelector from "./GradeSelector.svelte";
  import IntensitySelector from "./IntensitySelector.svelte";

  // Svelte 5 State (Runes)
  let formData = $state({
    area: "",
    docente: "",
    grado: "",
    intensidad: "",
    periodo: "I",
    contenidos: "",
    indicadores: "",
    dba: "",
    criterios: "",
    actividades: "",
  });

  let saving = $state(false);
  let savedRows = $state<any[]>([]);
  let loadingHistory = $state(false);

  // Grouped records for the sidebar
  let groupedRecords = $derived(() => {
    const groups: Record<string, any[]> = {};
    savedRows.forEach((row) => {
      // Area is column 0, Grade is column 2
      const key = `${row[0]} - ${row[2]}`;
      if (!groups[key]) {
        groups[key] = [];
      }
      groups[key].push(row);
    });
    return groups;
  });

  onMount(async () => {
    await fetchHistory();
  });

  async function fetchHistory() {
    loadingHistory = true;
    try {
      const result = await sheetsService.getRows();
      if (result.success && result.values) {
        // Skip header if it exists
        savedRows = result.values.slice(1);
      }
    } catch (error) {
      console.error("Error al cargar historia:", error);
    } finally {
      loadingHistory = false;
    }
  }

  function selectRecord(record: any[]) {
    formData = {
      area: record[0] || "",
      docente: record[1] || "",
      grado: record[2] || "",
      intensidad: record[3] || "",
      periodo: record[4] || "I",
      contenidos: record[5] || "",
      indicadores: record[6] || "",
      dba: record[7] || "",
      criterios: record[8] || "",
      actividades: record[9] || "",
    };
    Swal.fire({
      toast: true,
      position: "top-end",
      icon: "info",
      title: "Registro cargado",
      showConfirmButton: false,
      timer: 2000,
    });
  }

  function resetForm() {
    formData = {
      area: "",
      docente: "",
      grado: "",
      intensidad: "",
      periodo: "I",
      contenidos: "",
      indicadores: "",
      dba: "",
      criterios: "",
      actividades: "",
    };
    Swal.fire({
      toast: true,
      position: "top-end",
      icon: "success",
      title: "Formulario limpiado",
      showConfirmButton: false,
      timer: 2000,
    });
  }

  async function handleSave() {
    // Validation
    const requiredFields = [
      { key: "area", label: "ÁREA" },
      { key: "docente", label: "DOCENTE" },
      { key: "grado", label: "GRADO" },
      { key: "intensidad", label: "INTENSIDAD HORARIA" },
      { key: "periodo", label: "PERIODO" },
      { key: "contenidos", label: "CONTENIDOS" },
      { key: "indicadores", label: "INDICADORES" },
      { key: "dba", label: "DBA" },
      { key: "criterios", label: "CRITERIOS" },
      { key: "actividades", label: "ACTIVIDADES" },
    ];

    const missingFields = requiredFields
      .filter((field) => !formData[field.key as keyof typeof formData])
      .map((field) => field.label);

    if (missingFields.length > 0) {
      Swal.fire({
        icon: "warning",
        title: "Información Incompleta",
        html: `Por favor complete los siguientes campos:<br><br><b>${missingFields.join(", ")}</b>`,
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    saving = true;
    try {
      // Map data to columns A-J as per the spreadsheet image
      const row = [
        formData.area,
        formData.docente,
        formData.grado,
        formData.intensidad,
        formData.periodo,
        formData.contenidos,
        formData.indicadores,
        formData.dba,
        formData.criterios,
        formData.actividades,
      ];

      await sheetsService.appendRow(row);
      Swal.fire("Éxito", "Plan de Aula guardado correctamente.", "success");
      await fetchHistory();
    } catch (error) {
      console.error("Error al guardar:", error);
      Swal.fire("Error", "Error al guardar el plan de aula.", "error");
    } finally {
      saving = false;
    }
  }

  function handlePrint() {
    window.print();
  }
</script>

<div class="app-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h3 class="text-lg font-bold">Registros Guardados</h3>
      <button
        onclick={fetchHistory}
        class="text-xs bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded"
        title="Actualizar"
      >
        <span class={loadingHistory ? "animate-spin inline-block" : ""}>↻</span>
      </button>
    </div>

    <div class="sidebar-content">
      {#if loadingHistory}
        <div class="p-4 text-center text-gray-400">Cargando...</div>
      {:else if Object.keys(groupedRecords()).length === 0}
        <div class="p-4 text-center text-gray-500">No hay registros</div>
      {:else}
        {#each Object.entries(groupedRecords()) as [key, rows]}
          <div class="area-group">
            <div class="area-title">{key}</div>
            <div class="area-items">
              {#each rows as row}
                <button class="record-item" onclick={() => selectRecord(row)}>
                  <div class="record-period">Pér: {row[4]}</div>
                  <div class="record-teacher">{row[1]}</div>
                </button>
              {/each}
            </div>
          </div>
        {/each}
      {/if}
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="form-container">
      <div class="flex justify-between items-center mb-4 no-print">
        <h2 class="text-xl font-bold">Registro de Plan de Aula</h2>
        <div class="flex gap-4">
          <button
            onclick={resetForm}
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition-all transform hover:scale-105"
          >
            NUEVO REGISTRO
          </button>
          <button
            onclick={handlePrint}
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition-all transform hover:scale-105"
          >
            IMPRIMIR PDF
          </button>
          <button
            onclick={handleSave}
            disabled={saving}
            class="bg-green-600 hover:bg-green-700 disabled:bg-gray-600 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition-all transform hover:scale-105"
          >
            {saving ? "Guardando..." : "GUARDAR EN DRIVE"}
          </button>
        </div>
      </div>

      <!-- Header Table -->
      <table class="header-table">
        <tbody>
          <tr>
            <td rowspan="2" class="logo-cell">
              <img src={escudo} alt="Escudo" class="logo" />
            </td>
            <td class="institution-cell">
              <h1>INSTITUCION EDUCATIVA INSTITUTO GUATICA</h1>
              <p>DANE</p>
            </td>
            <td rowspan="2" class="academic-cell">
              <h2>GESTIÓN ACADÉMICA</h2>
            </td>
            <td class="code-label">CÓDIGO</td>
          </tr>
          <tr>
            <td class="empty-cell"></td>
            <td class="plan-cell">
              <div class="plan-title">PLAN DE AULA</div>
              <div class="plan-year">2026</div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Info Section -->
      <div class="info-section">
        <div class="info-row">
          <div class="info-group">
            <label for="area">ÁREA:</label>
            <AreaSelector bind:value={formData.area} />
          </div>
          <div class="info-group teacher">
            <label for="docente">DOCENTE:</label>
            <TeacherSelector bind:value={formData.docente} />
          </div>
        </div>
        <div class="info-row">
          <div class="info-group">
            <label for="grado">GRADO:</label>
            <GradeSelector bind:value={formData.grado} />
          </div>
          <div class="info-group intensity">
            <label for="intensidad">INTENSIDAD HORARIA SEMANAL:</label>
            <IntensitySelector bind:value={formData.intensidad} />
          </div>
        </div>
        <div class="info-row">
          <div class="info-group">
            <label for="periodo">PERIODO:</label>
            <PeriodSelector bind:value={formData.periodo} />
          </div>
        </div>
      </div>

      <!-- Main Table -->
      <table class="main-table">
        <thead>
          <tr>
            <th class="competencia-col">
              Estándares básicos de competencia<br />CONTENIDOS
            </th>
            <th class="desempeno-col">INDICADORES DE DESEMPEÑO</th>
            <th class="dba-col">DERECHOS BASICOS DE APRENDIZAJE<br />D B A</th>
            <th class="evaluacion-col">CRITERIOS DE EVALUACIÓN</th>
            <th class="recursos-col">ACTIVIDADES Y RECURSOS</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><textarea bind:value={formData.contenidos}></textarea></td>
            <td><textarea bind:value={formData.indicadores}></textarea></td>
            <td><textarea bind:value={formData.dba}></textarea></td>
            <td><textarea bind:value={formData.criterios}></textarea></td>
            <td><textarea bind:value={formData.actividades}></textarea></td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>
</div>

<style>
  :global(body) {
    background-color: #1a1a1a;
    color: #ffffff;
    padding: 20px;
  }

  .app-layout {
    display: flex;
    gap: 20px;
    height: calc(100vh - 40px);
    overflow: hidden;
  }

  .sidebar {
    width: 300px;
    background-color: #242424;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    border: 1px solid #333;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
  }

  .sidebar-header {
    padding: 15px;
    border-bottom: 1px solid #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #2a2a2a;
    border-radius: 12px 12px 0 0;
  }

  .sidebar-content {
    flex-grow: 1;
    overflow-y: auto;
    padding: 10px;
  }

  .sidebar-content::-webkit-scrollbar {
    width: 6px;
  }

  .sidebar-content::-webkit-scrollbar-thumb {
    background-color: #444;
    border-radius: 3px;
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

  .record-period {
    font-size: 0.7rem;
    color: #4a90e2;
    font-weight: bold;
    margin-bottom: 2px;
  }

  .record-teacher {
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .main-content {
    flex-grow: 1;
    overflow-y: auto;
    padding-right: 10px;
  }

  .main-content::-webkit-scrollbar {
    width: 8px;
  }

  .main-content::-webkit-scrollbar-thumb {
    background-color: #444;
    border-radius: 4px;
  }

  .form-container {
    max-width: 1200px;
    font-family: Arial, sans-serif;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #444;
  }

  th,
  td {
    border: 1px solid #444;
    padding: 8px;
    text-align: center;
  }

  /* Header Table Styles */
  .header-table {
    margin-bottom: 20px;
    background-color: #242424;
  }

  .logo-cell {
    width: 100px;
    padding: 10px;
  }

  .logo {
    max-width: 80px;
    height: auto;
  }

  .institution-cell h1 {
    font-size: 1.2rem;
    margin: 0;
    font-weight: bold;
  }

  .institution-cell p {
    margin: 4px 0 0;
    font-size: 0.9rem;
  }

  .academic-cell h2 {
    font-size: 1rem;
    margin: 0;
    font-weight: bold;
  }

  .code-label {
    width: 150px;
    font-size: 0.8rem;
    font-weight: bold;
    height: 20px;
  }

  .plan-cell {
    background-color: #242424;
  }

  .plan-title {
    font-weight: bold;
    font-size: 0.9rem;
  }

  .plan-year {
    font-size: 0.8rem;
  }

  /* Info Section Styles */
  .info-section {
    margin-bottom: 20px;
    text-align: left;
  }

  .info-row {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
    flex-wrap: wrap;
  }

  .info-group {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .info-group label {
    font-weight: bold;
    white-space: nowrap;
  }

  .info-group.teacher {
    flex-grow: 1;
  }

  /* Main Table Styles */
  .main-table {
    background-color: #242424;
  }

  .main-table thead th {
    font-size: 0.85rem;
    font-weight: bold;
    background-color: #333;
    vertical-align: middle;
    height: 60px;
  }

  .main-table tbody td {
    padding: 0;
    vertical-align: top;
  }

  .main-table textarea {
    width: 100%;
    min-height: 200px;
    background-color: transparent;
    border: none;
    color: white;
    padding: 10px;
    resize: none;
    box-sizing: border-box;
    font-family: inherit;
    overflow: hidden;
    field-sizing: content;
  }

  .main-table textarea:focus {
    outline: none;
    background-color: #2a2a2a;
  }

  /* Column widths matching image proportions approximately */
  .competencia-col {
    width: 20%;
  }
  .desempeno-col {
    width: 22%;
  }
  .dba-col {
    width: 20%;
  }
  .evaluacion-col {
    width: 18%;
  }
  .recursos-col {
    width: 20%;
  }

  /* PRINT STYLES */
  @media print {
    :global(body) {
      background-color: white !important;
      color: black !important;
      padding: 0 !important;
    }

    .sidebar,
    .no-print {
      display: none !important;
    }

    .app-layout {
      display: block !important;
      height: auto !important;
      overflow: visible !important;
    }

    .main-content {
      padding: 0 !important;
      overflow: visible !important;
    }

    .form-container {
      max-width: 100% !important;
    }

    table {
      border: 1px solid black !important;
      background-color: white !important;
    }

    th,
    td {
      border: 1px solid black !important;
      color: black !important;
    }

    .header-table,
    .main-table {
      background-color: white !important;
    }

    .main-table thead th {
      background-color: #eee !important;
      color: black !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .main-table textarea {
      color: black !important;
      background-color: white !important;
    }

    :global(.teacher-select),
    :global(.area-select) {
      border-bottom: 1px solid black !important;
      color: black !important;
      background: transparent !important;
    }

    .plan-cell {
      background-color: #eee !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .logo {
      filter: grayscale(1);
    }
  }
</style>
