//
// Déclaration des fonctionnalités du site Internet.
//
import { Code, Eye, Lock, Palette, Smile, Zap } from "lucide-react";

export const features = [
	{
		id: 1,
		title: "Sécurisé de bout en bout",
		description:
			"Toutes les données transmises entre votre navigateur et nos serveurs sont chiffrées pour garantir une sécurité maximale.",
		icon: <Lock />
	},
	{
		id: 2,
		title: "Interface ergonomique",
		description:
			"L'interface est conçue pour être simple et ergonomique pour tous les utilisateurs, même pour vos grands-parents.",
		icon: <Smile />
	},
	{
		id: 3,
		title: "Respect de la vie privée",
		description:
			"Vos données sont stockées sur des serveurs basés en Europe conformément au RGPD pour garantir une intégrité et une confidentialité totale.",
		icon: <Eye />
	},
	{
		id: 4,
		title: "Haute performance",
		description:
			"Nos serveurs sont prévus pour garantir une performance optimale et une disponibilité maximale pour tous les utilisateurs.",
		icon: <Zap />
	},
	{
		id: 5,
		title: "Personnalisation multiple",
		description:
			"Notre service propose une grande variétés d'options de personnalisation lors de la création des raccourcis vers vos liens Internet.",
		icon: <Palette />
	},
	{
		id: 6,
		title: "Ouvert aux développeurs",
		description:
			"Notre API est ouverte à tous les développeurs qui souhaitent intégrer notre service dans leurs applications.",
		icon: <Code />
	}
] as const;