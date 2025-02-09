//
// Déclaration des fonctionnalités du site Internet.
//
import { Code, Eye, Lock, Palette, Smile, Zap } from "lucide-react";

export const features = [
	{
		id: 1,
		icon: <Lock />,
		title: "index.features.title_1",
		description: "index.features.description_1"
	},
	{
		id: 2,
		icon: <Smile />,
		title: "index.features.title_2",
		description: "index.features.description_2"
	},
	{
		id: 3,
		icon: <Eye />,
		title: "index.features.title_3",
		description: "index.features.description_3"
	},
	{
		id: 4,
		icon: <Zap />,
		title: "index.features.title_4",
		description: "index.features.description_4"
	},
	{
		id: 5,
		icon: <Palette />,
		title: "index.features.title_5",
		description: "index.features.description_5"
	},
	{
		id: 6,
		icon: <Code />,
		title: "index.features.title_6",
		description: "index.features.description_6"
	}
] as const;