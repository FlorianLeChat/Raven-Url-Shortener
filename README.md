# 🔗 Raven Url Shortener

![logo](.gitlab/images/logo.png)

![HTML](.gitlab/badges/html.svg)
![CSS](.gitlab/badges/css.svg)
![TypeScript](.gitlab/badges/typescript.svg)
![PHP](.gitlab/badges/php.svg)

![Next.js](.gitlab/badges/nextjs.svg)
![Symfony](.gitlab/badges/symfony.svg)
![Doctrine](.gitlab/badges/doctrine.svg)
![TailwindCSS](.gitlab/badges/tailwindcss.svg)
![Lucide](.gitlab/badges/lucide.svg)
![Docker](.gitlab/badges/docker.svg)
![Redis](.gitlab/badges/redis.svg)
![i18n](.gitlab/badges/i18n.svg)
![Prettier](.gitlab/badges/prettier.svg)
![ESLint](.gitlab/badges/eslint.svg)
![OpenAPI](.gitlab/badges/openapi.svg)
![Swagger](.gitlab/badges/swagger.svg)

## In French

> [!IMPORTANT]
> Depuis avril 2026, le code du projet est désormais hébergé sur mon instance GitLab personnalisée, accessible à [cette adresse](https://git.florian-dev.fr/floriantrayon/Raven-Url-Shortener). Le dépôt GitHub est un miroir du dépôt GitLab, **mis à jour automatiquement**.
>
> **Les contributions publiques restent sur GitHub et sont les bienvenues** ; les pull requests validées y seront ensuite transférées manuellement sur GitLab pour être intégrées. 🙂

Voici l'un de mes projets les plus aboutis à ce jour : **un service de raccourcissement de liens Internet (URL)**, inspiré du célèbre [Cparlà](https://cpar.la/) réalisé par... [mon entreprise](https://ciblemut.net/) !, mais avec une approche personnelle plus **moderne**, **personnalisable**, centrée sur la **sécurité** et la **confidentialité**.

Le projet a été conçu avec une séparation **claire** entre le *front-end* et le *back-end*, permettant à chaque partie d'évoluer indépendamment. Le *front-end* repose actuellement sur [Next.js](https://nextjs.org/) 🤕, ma technologie de prédilection, mais une migration vers [SvelteKit](https://svelte.dev/docs/kit/introduction) 💘 est prévue afin de se débarrasser de ce qu'est devenu l'écosystème React, que je considère aujourd'hui comme une **horreur**.

Le *back-end*, quant à lui, est construit sur le solide *framework* [Symfony](https://symfony.com/) 💪, et expose une API REST qui alimente le *front-end*, le rendant ainsi **totalement agnostique** vis-à-vis des évolutions futures. Ce *back-end* a également été pensé pour être utilisé par d'**autres services**, ce qui permet d'intégrer ce raccourcisseur à d'autres applications. Une documentation [Swagger](https://swagger.io/) (**en anglais uniquement**) a bien sûr été mise en place pour faciliter l'utilisation de l'API et accessible [ici](https://docs.url.florian-dev.fr/).

À terme, toutes les nouvelles fonctionnalités seront d'abord implémentées côté API avant d'être intégrées dans le front-end, **garantissant ainsi une cohérence et une évolutivité optimales du projet**.

> [!TIP]
> Voir le fichier [SETUP.md](SETUP.md) pour consulter les instructions d'installation.

> [!NOTE]
> Tout ou partie du code peut contenir des commentaires dans ma langue natale (le français) afin de faciliter le développement. 🌐

## In English

> [!IMPORTANT]
> Since April 2026, the project's code has been hosted on my custom GitLab instance, accessible at [this address](https://git.florian-dev.fr/floriantrayon/Raven-Url-Shortener). The GitHub repository is a mirror of the GitLab repository, **automatically kept up to date**.
>
> **Public contributions remain on GitHub and are welcome**; validated pull requests will then be manually transferred to GitLab to be integrated. 🙂

Here's one of my most successful projects to date: **an URL Shortener service**, inspired by the famous [Cparlà](https://cpar.la/) created by... [my company](https://ciblemut.net/)!, but with a more **modern**, **personalizable**, **security** and **confidentiality** approach of my own.

The project was designed with a **clear** separation between the front-end and the back-end, allowing each part to evolve independently. The front-end is currently based on [Next.js](https://nextjs.org/) 🤕, my technology of choice, but a migration to [SvelteKit](https://svelte.dev/docs/kit/introduction) 💘 is planned to get rid of what React's ecosystem has become, which I now consider a **nightmare**.

The back-end, on the other hand, is built on the solid [Symfony](https://symfony.com/) 💪 framework and exposes a REST API that powers the front-end, making it **totally agnostic** to future evolutions. This back-end was also designed to be used by **other services**, allowing this URL Shortener to be integrated into other applications. A [Swagger](https://swagger.io/) documentation has of course been set up to make the API easier to use and is accessible [here](https://url.florian-dev.fr/api/docs).

Ultimately, all new functionalities will first be implemented on API before being integrated into the front-end, **ensuring optimal project consistency and scalability**.

> [!TIP]
> See the [SETUP.md](SETUP.md) file for setup instructions.

> [!NOTE]
> All or part of the code may contain comments in my native language (French) to ease development. 🌐

![image](.gitlab/images/raven_url_shortener.png)
