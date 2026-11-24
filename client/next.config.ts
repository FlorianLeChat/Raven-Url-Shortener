import type { NextConfig } from "next";
import createNextIntlPlugin from "next-intl/plugin";

const withNextIntl = createNextIntlPlugin( "./utilities/i18n.ts" );
const nextConfig: NextConfig = withNextIntl( {
	basePath: process.env.NEXT_PUBLIC_BASE_PATH,
	poweredByHeader: false
} );

export default nextConfig;