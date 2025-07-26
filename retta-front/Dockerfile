# Etapa de build
FROM node:18-alpine3.19 as build-stage
WORKDIR /app
COPY . .
RUN npm install
RUN npm run build

# Etapa de produção
FROM nginx:alpine
COPY --from=build-stage /app/dist /usr/share/nginx/html
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf
