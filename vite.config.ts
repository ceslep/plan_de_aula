import { defineConfig } from "vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import tailwindcss from "@tailwindcss/vite";

// https://vite.dev/config/
export default defineConfig({
   base: '/plan_de_aula/', // ← ¡Importante!
  plugins: [svelte(), tailwindcss()],
});
