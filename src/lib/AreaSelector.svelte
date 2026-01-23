<script lang="ts">
  let { value = $bindable("") } = $props();

  const areas = [
    "MATEMATICAS",
    "ESTADISTICA",
    "GEOMETRIA",
    "CIENCIAS NATURALES Y EDUCACION AMBIENTAL",
    "QUIMICA",
    "FISICA",
    "EDUCACION ARTISTICA",
    "EMPRENDIMIENTO",
    "PROYECTO Y EMPRENDIMIENTO",
    "EDUCACION RELIGIOSA, ETICA Y V. HUMANOS",
    "EDUCACION ETICA Y VALORES",
    "EDUCACION FISICA, RECREACION Y DEPORTES",
    "TECNOLOGIA E INFORMATICA",
    "CATEDRA DE LA PAZ",
    "LENGUA CASTELLANA",
    "INGLES",
    "CIENCIAS SOCIALES",
    "FILOSOFIA",
  ];
</script>

<select bind:value class="area-select">
  <option value="" disabled selected>Seleccione un área...</option>
  {#each areas as area}
    <option value={area}>{area}</option>
  {/each}
</select>

<style>
  .area-select {
    background: transparent;
    border: none;
    border-bottom: 1px solid #555;
    color: white;
    padding: 2px 5px;
    width: 150px;
    cursor: pointer;
    font-family: inherit;
    font-size: 0.9rem;
    height: 28px;
    appearance: none;
  }

  .area-select:focus {
    outline: none;
    border-bottom-color: #888;
    background-color: #2a2a2a;
  }

  option {
    background-color: #242424;
    color: white;
  }
</style>
