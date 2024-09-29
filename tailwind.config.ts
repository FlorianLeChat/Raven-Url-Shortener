import type { Config } from "tailwindcss";

/**
 * @type {import("tailwindcss").Config}
 */
export default {
	darkMode: "class",
	content: [ "./app/**/*.{ts,tsx}" ],
	theme: {},
	plugins: []
} satisfies Config;