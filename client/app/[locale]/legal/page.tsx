import { setRequestLocale } from "next-intl/server";

export default async function Page( {
	params
}: Readonly<{
	params: Promise<{ locale: string }>;
}> )
{
	const { locale } = await params;

	setRequestLocale( locale );

	if ( locale === "fr" )
	{
		return (
			<>
				<em>Applicable à partir du 1er janvier 2025.</em>

				<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
					Crédits
				</h3>

				<p className="mb-3">
					Le site de création de liens raccourcis accessible à
					l&lsquo;adresse <code>https://url.florian-dev.fr/</code> a
					été créée et est maintenue par Florian Trayon (personne
					physique). Le site Internet est hébergé par OVH – 2 rue
					Kellermann – 59100 Roubaix – France.
				</p>

				<p>
					Le responsable du traitement, de la publication et des
					données personnelles (DPO) est Florian Trayon. Pour toute
					question relative à la gestion de vos données personnelles
					par ce site, veuillez nous contacter à l&lsquo;adresse
					suivante : <code>contact@florian-dev.fr</code>.
				</p>

				<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
					Respect de la vie privée
				</h3>

				<p className="mb-3">
					Le service proposé par Raven Url Shortener se veut
					exemplaire en matière de respect de la vie privée.
					C&lsquo;est pourquoi la collecte de données personnelles est
					limitée au strict nécessaire.
				</p>

				<p className="mb-3">
					<strong>Premièrement</strong>, afin de produire des
					statistiques de fréquentation anonymes et d’améliorer le
					fonctionnement du site, celui-ci peut utiliser la solution
					de mesure d’audience open source Umami lorsqu’elle est
					activée. Cet outil est configuré de manière à ne pas
					utiliser de cookies ni à collecter de données personnelles
					permettant de vous identifier directement. Les statistiques
					produites sont anonymes et utilisées uniquement afin
					d’améliorer le fonctionnement du site.
				</p>

				<p className="mb-3">
					<strong>Deuxièmement</strong>, les mots de passe, données
					sensibles, liens raccourcis sont hachés, chiffrés et salés
					conformément aux normes de sécurité les plus récentes et aux
					bonnes pratiques avant d&lsquo;être enregistrés dans la base
					de données. Nous mettons tout en œuvre pour garantir
					l&lsquo;intégrité des données transmises et pour empêcher
					tout accès non autorisé par des tiers. Si vous avez des
					interrogations quant à la manière dont nous utilisons ou
					traitons vos données, sachez que le code source complet du
					site est accessible sur GitHub.
				</p>

				<p className="mb-3">
					<strong>Troisièmement</strong>, afin de prévenir les abus,
					les tentatives automatisées et toute utilisation
					malveillante du service (notamment par des robots), le site
					peut utiliser la solution ALTCHA lorsqu’elle est activée. Ce
					mécanisme de protection fonctionne sans recourir à des
					services tiers intrusifs et sans dépôt de cookies
					publicitaires. Les vérifications effectuées visent
					uniquement à assurer la sécurité et le bon fonctionnement du
					service, sans collecte de données personnelles à des fins de
					suivi ou de profilage.
				</p>

				<p>
					<strong>Enfin</strong>, en ce qui concerne la
					journalisation, nous limitons la collecte des données au
					strict nécessaire. Ces dernières sont utilisées uniquement à
					des fins de débogage et de surveillance technique.
				</p>

				<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
					Conditions d&lsquo;utilisation
				</h3>

				<p className="mb-3">
					Toute personne utilisant les services de Raven Url Shortener
					s&lsquo;engage à respecter les présentes conditions
					générales d&lsquo;utilisation. L&lsquo;utilisation de Raven
					Url Shortener est libre et gratuite, quel que soit le cadre,
					le code source du projet étant disponible sur GitHub.
					Néanmoins, toute activité malveillante ou illicite est
					strictement interdite.
				</p>

				<p className="mb-3">
					Nous nous réservons le droit de supprimer, sans préavis, le
					service ou l&lsquo;accès aux comptes utilisateurs ou des
					liens raccourcis, en cas d&lsquo;utilisation abusive. En cas
					de perte de l&lsquo;accès à votre compte utilisateur, nous
					ne pourrons pas vous restituer un mot de passe oublié, car
					nous n&lsquo;en connaissons pas la valeur.
				</p>

				<p>
					Conformément au Règlement Général sur la Protection des
					Données, vous disposez d&lsquo;un droit d&lsquo;accès, de
					rectification, de modification et de suppression des données
					personnelles que vous nous avez communiquées. Vous pouvez
					exercer ce droit à tout moment en écrivant à :{" "}
					<a
						href="mailto:contact@florian-dev.fr"
						className="underline decoration-dotted underline-offset-4"
					>
						contact@florian-dev.fr
					</a>{" "}
					.
				</p>

				<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
					Responsabilité
				</h3>

				<p>
					Le service est fourni tel quel. L&lsquo;utilisation du
					service relève de la seule responsabilité de
					l&lsquo;utilisateur, et nous ne saurions être tenus
					responsables de tout préjudice résultant de son utilisation.
					Nous ne pourrons pas être tenus responsables de toute
					indisponibilité du service, nous nous efforçons de le
					maintenir en ligne 24h/24 et 7j/7 jusqu&lsquo;à sa mise hors
					service.
				</p>

				<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
					Droit applicable
				</h3>

				<p>
					Les présentes conditions générales d&lsquo;utilisation ainsi
					que l&lsquo;ensemble du contenu du site sont soumis à la
					législation française. Tout litige relatif à leur
					interprétation relèvera de la compétence exclusive du
					tribunal de commerce de Nice.
				</p>
			</>
		);
	}

	return (
		<>
			<em>Effective from January 1, 2025.</em>

			<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
				Credits
			</h3>

			<p className="mb-3">
				The URL shortening website accessible at
				<code>https://url.florian-dev.fr/</code> was created and is
				maintained by Florian Trayon (natural person). The website is
				hosted by OVH – 2 rue Kellermann – 59100 Roubaix – France.
			</p>

			<p>
				The data controller, publisher, and Data Protection Officer
				(DPO) is Florian Trayon. For any questions regarding the
				management of your personal data by this site, please contact us
				at the following address: <code>contact@florian-dev.fr</code>.
			</p>

			<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
				Privacy Policy
			</h3>

			<p className="mb-3">
				The service offered by Raven Url Shortener strives to be
				exemplary in terms of privacy. That is why the collection of
				personal data is limited to what is strictly necessary.
			</p>

			<p className="mb-3">
				<strong>Firstly</strong>, in order to produce anonymous audience
				statistics and improve the operation of the website, it may use
				the open-source analytics solution Umami when enabled. This tool
				is configured not to use cookies and not to collect personal
				data that could directly identify you. The statistics produced
				are anonymous and used solely to improve the operation of the
				website.
			</p>

			<p className="mb-3">
				<strong>Secondly</strong>, passwords, sensitive data, and
				shortened links are hashed, encrypted, and salted in accordance
				with the latest security standards and best practices before
				being stored in the database. We do everything possible to
				ensure the integrity of the transmitted data and to prevent any
				unauthorized access by third parties. If you have any questions
				about how we use or process your data, please note that the
				complete source code of the site is available on GitHub.
			</p>

			<p className="mb-3">
				<strong>Thirdly</strong>, in order to prevent abuse, automated
				requests, and any malicious use of the service (notably by
				bots), the website may use the ALTCHA solution when enabled.
				This protection mechanism operates without relying on intrusive
				third-party services and without placing advertising cookies.
				The checks performed are intended solely to ensure the security
				and proper functioning of the service, without collecting
				personal data for tracking or profiling purposes.
			</p>

			<p>
				<strong>Finally</strong>, regarding logging, we limit data
				collection to what is strictly necessary. This data is used
				solely for debugging and technical monitoring purposes.
			</p>

			<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
				Terms of Use
			</h3>

			<p className="mb-3">
				Any person using the services of Raven Url Shortener agrees to
				abide by these general terms of use. The use of Raven Url
				Shortner is free and unrestricted, regardless of the context,
				with the project&lsquo;s source code being available on GitHub.
				However, any malicious or illegal activity is strictly
				prohibited.
			</p>

			<p className="mb-3">
				We reserve the right to delete, without notice, the service or
				access to user accounts or shortened links in the event of
				abusive use. In the event of losing access to your user account,
				we will not be able to recover a forgotten password, as we do
				not know its value.
			</p>

			<p className="mb-3">
				In accordance with the General Data Protection Regulation
				(GDPR), you have the right to access, rectify, modify, and
				delete the personal data you have provided to us. You can
				exercise this right at any time by writing to:{" "}
				<a
					href="mailto:contact@florian-dev.fr"
					className="underline decoration-dotted underline-offset-4"
				>
					contact@florian-dev.fr
				</a>{" "}
				.
			</p>

			<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
				Liability
			</h3>

			<p>
				The service is provided as is. The website may interact with or
				communicate with third-party servers and external services. The
				use of the service is solely the responsibility of the user, and
				we cannot be held liable for any damage resulting from its use.
				We cannot be held responsible for any unavailability of the
				service; we strive to keep it online 24/7 until it is
				decommissioned.
			</p>

			<h3 className="py-6 text-2xl font-bold tracking-tight underline underline-offset-4">
				Applicable Law
			</h3>

			<p>
				These terms of use and all content on the site are subject to
				French law. Any dispute regarding their interpretation will fall
				under the exclusive jurisdiction of the Commercial Court of
				Nice.
			</p>
		</>
	);
}