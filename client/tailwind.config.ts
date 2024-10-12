import { nextui } from "@nextui-org/react";
import type { Config } from "tailwindcss";

export default {
	theme: {},
	content: [
		"./app/**/*.{ts,tsx}",
		"./node_modules/@nextui-org/theme/dist/**/*.{js,jsx,ts,tsx,mdx}"
	],
	plugins: [ nextui() ],
	darkMode: "class"
} satisfies Config;