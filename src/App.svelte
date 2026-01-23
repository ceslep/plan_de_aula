<script lang="ts">
  import PlanAulaForm from "./lib/PlanAulaForm.svelte";
  import { sheetsService } from "./lib/services/google_sheets_service.svelte";
  import { onMount } from "svelte";

  onMount(async () => {
    await sheetsService.init();
    console.log(
      "Google Sheets Service iniciado para:",
      sheetsService.worksheetTitle,
    );
  });
</script>

<main>
  <PlanAulaForm />
</main>

<style>
  main {
    padding: 1rem;
    width: 100%;
    display: flex;
    justify-content: center;
  }
</style>
