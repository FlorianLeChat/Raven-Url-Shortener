import { heroui } from "@heroui/react";
import type { Config } from "tailwindcss";

export default {
	theme: {
		extend: {
			keyframes: {
				github: {
					"0%, 100%": { transform: "rotate(0)" },
					"20%, 60%": { transform: "rotate(-25deg)" },
					"40%, 80%": { transform: "rotate(10deg)" }
				}
			},
			animation: {
				github: "github 560ms ease-in-out"
			}
		}
	},
	content: [
		"./app/**/*.{ts,tsx}",
		"./node_modules/@heroui/theme/dist/**/*.{js,jsx,ts,tsx,mdx}"
	],
	plugins: [ heroui() ],
	darkMode: "class"
} satisfies Config;