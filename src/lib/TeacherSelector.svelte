<script lang="ts">
  let { value = $bindable("") } = $props();

  const teachers = [
    "ANA SOFIA CARDENAS PETUMA",
    "BLANCA ILDORY JARAMILLO JARAMILLO",
    "BLANCA NELLY MARIN GIRALDO",
    "CARLOS ALBERTO AGUIRRE GONZALEZ",
    "CARLOS ARMANDO RIOS ALVAREZ",
    "CARLOS ARTURO CUARTAS",
    "CARMEN ROCIO ALVAREZ SOLARTE",
    "CESAR LEANDRO PATIÑO VELEZ",
    "DANIEL QUICENO RAMIREZ",
    "DELCID DE JESUS BUENO ARANDIA",
    "DERLY JOHANNA PUERTAS ALVAREZ",
    "DIANA MARCELA ROMERO GAMBOA",
    "ESTEBAN FERNANDO JARAMILLO MUÑOZ",
    "ISABEL CRISTINA GOMEZ MONTOYA",
    "JAVIER DE JESUS AGUDELO CARVAJAL",
    "JHON JAIRO VELEZ TEJADA",
    "JOHN EDWIN ARBOLEDA ACEVEDO",
    "JON JAMES VASCO RODRIGUEZ",
    "JOSE FERNANDO CARDONA HENAO",
    "LEON FERNEY CARO ARDILA",
    "PABLO ESTEBAN REINOSO HENAO",
  ];
</script>

<select bind:value class="teacher-select">
  <option value="" disabled selected>Seleccione un docente...</option>
  {#each teachers as teacher}
    <option value={teacher}>{teacher}</option>
  {/each}
</select>

<style>
  .teacher-select {
    background-color: #242424;
    border: none;
    border-bottom: 1px solid #555;
    color: white;
    padding: 2px 5px;
    width: 100%;
    cursor: pointer;
    font-family: inherit;
    font-size: 0.9rem;
    height: 28px;
    appearance: none;
  }

  .teacher-select:focus {
    outline: none;
    border-bottom-color: #888;
    background-color: #2a2a2a;
  }

  /* Style options for better visibility in some browsers */
  option {
    background-color: #242424;
    color: white;
  }
</style>
