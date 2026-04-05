# syntax=docker/dockerfile:1

FROM node:20-slim AS base

WORKDIR /usr/src/app

COPY package*.json ./

RUN apt update && \
    apt install -y tzdata wget && \
    apt clean && rm -rf /var/lib/apt/lists/* && \
    npm ci

COPY . .

RUN npm run build && npm prune --omit=dev

FROM base AS production

WORKDIR /usr/src/app

COPY --from=base --chown=node:node /usr/src/app ./
