FROM node:18-alpine3.19 as build-stage
WORKDIR /app

# Copia os arquivos de dependências do yarn
COPY package.json yarn.lock ./

# Instala as dependências via yarn
RUN yarn install --frozen-lockfile

# Copia o resto dos arquivos
COPY . .

# Builda o projeto
RUN yarn build

# Etapa final - Nginx para servir os arquivos estáticos
FROM nginx:alpine

# Copia o arquivo de configuração do nginx (ajuste o caminho conforme necessário)
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

# Copia os arquivos buildados do frontend
COPY --from=build-stage /app/dist /usr/share/nginx/html
