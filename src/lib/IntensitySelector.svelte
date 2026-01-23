<script lang="ts">
  let { value = $bindable("") } = $props();

  const intensities = ["1", "2", "3", "4", "5", "6"];
</script>

<select bind:value class="intensity-select">
  <option value="" disabled selected>Int...</option>
  {#each intensities as intensity}
    <option value={intensity}>{intensity}</option>
  {/each}
</select>

<style>
  .intensity-select {
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

  .intensity-select:focus {
    outline: none;
    border-bottom-color: #888;
    background-color: #2a2a2a;
  }

  option {
    background-color: #242424;
    color: white;
  }
</style>
