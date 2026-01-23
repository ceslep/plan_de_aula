<script lang="ts">
  let { value = $bindable("") } = $props();

  const grades = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11"];
</script>

<select bind:value class="grade-select">
  <option value="" disabled selected>Grado...</option>
  {#each grades as grade}
    <option value={grade}>{grade}</option>
  {/each}
</select>

<style>
  .grade-select {
    background: transparent;
    border: none;
    border-bottom: 1px solid #555;
    color: white;
    padding: 2px 5px;
    width: 80px;
    cursor: pointer;
    font-family: inherit;
    font-size: 0.9rem;
    height: 28px;
    appearance: none;
  }

  .grade-select:focus {
    outline: none;
    border-bottom-color: #888;
    background-color: #2a2a2a;
  }

  option {
    background-color: #242424;
    color: white;
  }
</style>
