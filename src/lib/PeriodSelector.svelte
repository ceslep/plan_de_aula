<script lang="ts">
  let { value = $bindable("") } = $props();

  const periods = ["I", "II", "III", "IV"];
</script>

<select bind:value class="period-select">
  <option value="" disabled selected>Pér...</option>
  {#each periods as period}
    <option value={period}>{period}</option>
  {/each}
</select>

<style>
  .period-select {
    background: transparent;
    border: none;
    border-bottom: 1px solid #555;
    color: white;
    padding: 2px 5px;
    width: 60px;
    cursor: pointer;
    font-family: inherit;
    font-size: 0.9rem;
    height: 28px;
    appearance: none;
  }

  .period-select:focus {
    outline: none;
    border-bottom-color: #888;
    background-color: #2a2a2a;
  }

  option {
    background-color: #242424;
    color: white;
  }
</style>
